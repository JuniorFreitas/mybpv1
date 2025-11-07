<?php

namespace App\Services;

use App\Mail\Admissao\Historico\AvaliacaoNoventaVencimento\AvaliacaoNoventaVencimentoMail;
use App\Models\Admissao;
use App\Models\AvaliacaoNoventaVencimento;
use App\Models\TipoRecebeEmail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AvaliacaoNoventaService
{
    public const DIAS_ANTECEDENCIA = 30;
    public const MAX_AVALIACOES_PERMITIDAS = 2;

    public function buscarAvaliacoesVencendoOuVencidas(int $empresaId, ?int $diasAntecedencia = null, bool $incluirCompletas = false): Collection
    {
        $dias = $diasAntecedencia ?? self::DIAS_ANTECEDENCIA;
        $dataLimite = Carbon::today()->addDays($dias);

        return AvaliacaoNoventaVencimento::query()
            ->with([
                'FeedbackCurriculo:id,curriculo_id,empresa_id',
                'FeedbackCurriculo.Curriculo:id,nome',
                'FeedbackCurriculo.Admissao:id,feedback_id,status,centro_custo_id,cargo,funcao',
                'FeedbackCurriculo.Admissao.CentroCusto:id,gestor_id,label',
                'FeedbackCurriculo.Admissao.CentroCusto.Gestor:id,nome,login'
            ])
            ->whereHas('FeedbackCurriculo', function ($query) use ($empresaId) {
                $query->where('empresa_id', $empresaId);
            })
            ->whereHas('FeedbackCurriculo.Admissao', function ($query) {
                $query->where('status', Admissao::STATUS_ADMISSAO_ADMITIDO);
            })
            ->withCount('qntFeedback')
            ->having('qnt_feedback_count', $incluirCompletas ? '<=' : '<', self::MAX_AVALIACOES_PERMITIDAS)
            ->where(function ($query) use ($dataLimite) {
                $query->where('prazo_dia_inicial', '<=', $dataLimite)
                    ->orWhere('prazo_dia_final', '<=', $dataLimite);
            })
            ->orderBy('prazo_dia_inicial')
            ->orderBy('prazo_dia_final')
            ->orderBy('qnt_feedback_count')
            ->get();
    }

    /**
     * Agrupa vencimentos por Gestor do Centro de Custo do colaborador.
     * Retorna uma coleção onde cada item possui: ['gestor' => User(min), 'vencimentos' => array]
     */
    public function montarVencimentosPorGestor(Collection $avaliacoes, string $dataAtual): Collection
    {
        $grupos = [];

        foreach ($avaliacoes as $avaliacao) {
            $resultado = $this->verificarVencimento($avaliacao, $dataAtual);
            if (!$resultado) {
                continue;
            }

            $admissao = $avaliacao->FeedbackCurriculo->Admissao ?? null;
            $centro = $admissao ? $admissao->CentroCusto : null;
            $gestor = $centro ? $centro->Gestor : null; // User com id,nome,login
            if (!$gestor) {
                // Sem gestor definido, não enviaremos por este modo
                continue;
            }

            $qntAvaliacoes = (int)($avaliacao->qnt_feedback_count ?? 0);
            $avaliacaoStatusLabel = $qntAvaliacoes === 0 ? 'Nenhuma avaliação realizada' : '1ª avaliação realizada';

            $centroLabel = $centro ? $centro->label : null;

            $item = [
                'colaborador' => $avaliacao->FeedbackCurriculo->Curriculo->nome ?? 'Nome não disponível',
                'cargo' => $avaliacao->FeedbackCurriculo->Admissao->cargo ?? null,
                'funcao' => $avaliacao->FeedbackCurriculo->Admissao->funcao ?? null,
                'centro_custo' => $centroLabel,
                'prazo_vencido' => $resultado['prazo'],
                'status' => $resultado['status'],
                'dias_atraso' => $resultado['dias_atraso'],
                'dias_para_vencer' => $resultado['dias_para_vencer'],
                'observacao' => $resultado['observacao'],
                'qnt_avaliacoes' => $qntAvaliacoes,
                'avaliacao_status' => $avaliacaoStatusLabel,
            ];

            $key = $gestor->id ?? $gestor->login;
            if (!isset($grupos[$key])) {
                $grupos[$key] = [
                    'gestor' => $gestor,
                    'vencimentos' => [],
                ];
            }
            $grupos[$key]['vencimentos'][] = $item;
        }

        return collect(array_values($grupos));
    }

    public function buscarUsuariosParaNotificacao(int $empresaId): Collection
    {
        // Usuários que devem receber as notificações completas:
        // somente quem possui a habilidade "privilegio_gestao_rh"
        return User::query()
            ->where('empresa_id', $empresaId)
            ->select(['id', 'nome', 'login'])
            ->usuariosPrivilegioRh()
            ->orderBy('nome')
            ->get();
    }

    public function buscarGestoresParaNotificacao(int $empresaId): Collection
    {
        return User::query()
            ->where('empresa_id', $empresaId)
            ->select(['id', 'nome', 'login'])
            ->where(function ($q) {
                $q->where('tipo', User::GESTOR)
                    ->orWhere('gestor', true);
            })
            ->whereHas('UserRecebeEmail', function ($query) {
                $query->where('nome', TipoRecebeEmail::AVALIACAO_90_DIAS)
                    ->where('ativo', true);
            })
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();
    }

    public function montarVencimentos(Collection $avaliacoes, string $dataAtual): Collection
    {
        return $avaliacoes
            ->map(function ($avaliacao) use ($dataAtual) {
                $resultado = $this->verificarVencimento($avaliacao, $dataAtual);

                if (!$resultado) {
                    return null;
                }

                $qntAvaliacoes = (int)($avaliacao->qnt_feedback_count ?? 0);
                $avaliacaoStatusLabel = $qntAvaliacoes === 0
                    ? 'Nenhuma avaliação realizada'
                    : '1ª avaliação realizada';

                $centro = $avaliacao->FeedbackCurriculo->Admissao->CentroCusto ?? null;
                $gestor = $centro ? $centro->Gestor : null;

                // Gera ou recupera token para avaliação pública, apenas se ainda não estiver completa
                $tokenData = [];
                if ($qntAvaliacoes < self::MAX_AVALIACOES_PERMITIDAS) {
                    $tokenData = $this->gerarOuRecuperarToken($avaliacao);
                }

                // Se já estiver completa (2 avaliações), força status 'COMPLETA'
                $status = $qntAvaliacoes >= self::MAX_AVALIACOES_PERMITIDAS
                    ? 'COMPLETA'
                    : $resultado['status'];
                $diasAtraso = $qntAvaliacoes >= self::MAX_AVALIACOES_PERMITIDAS ? 0 : $resultado['dias_atraso'];
                $diasParaVencer = $qntAvaliacoes >= self::MAX_AVALIACOES_PERMITIDAS ? 0 : $resultado['dias_para_vencer'];
                $observacao = $qntAvaliacoes >= self::MAX_AVALIACOES_PERMITIDAS ? 'Avaliação completa' : $resultado['observacao'];

                return [
                    'colaborador' => $avaliacao->FeedbackCurriculo->Curriculo->nome ?? 'Nome não disponível',
                    'cargo' => $avaliacao->FeedbackCurriculo->Admissao->cargo ?? null,
                    'funcao' => $avaliacao->FeedbackCurriculo->Admissao->funcao ?? null,
                    'centro_custo' => $avaliacao->FeedbackCurriculo->Admissao->CentroCusto ? $avaliacao->FeedbackCurriculo->Admissao->CentroCusto->label : null,
                    'gestor_id' => $centro->gestor_id ?? ($gestor->id ?? null),
                    'gestor_nome' => $gestor->nome ?? null,
                    'gestor_login' => $gestor->login ?? null,
                    'prazo_vencido' => $resultado['prazo'],
                    'status' => $status,
                    'dias_atraso' => $diasAtraso,
                    'dias_para_vencer' => $diasParaVencer,
                    'observacao' => $observacao,
                    'qnt_avaliacoes' => $qntAvaliacoes,
                    'avaliacao_status' => $avaliacaoStatusLabel,
                    'token' => $tokenData['token'] ?? null,
                    'link_avaliacao' => $tokenData['url'] ?? null,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Gera novo token ou recupera existente ainda válido
     */
    private function gerarOuRecuperarToken(AvaliacaoNoventaVencimento $avaliacao): array
    {
        Log::debug('gerarOuRecuperarToken chamado', [
            'feedback_id' => $avaliacao->feedback_id,
            'tem_token' => !empty($avaliacao->token_avaliacao),
            'token_expiracao' => $avaliacao->token_expiracao
        ]);

        // Se já tem token válido e não expirado, reutiliza
        if ($avaliacao->token_avaliacao &&
            $avaliacao->token_expiracao &&
            Carbon::parse($avaliacao->token_expiracao)->isFuture() &&
            !$avaliacao->avaliacao_realizada) {

            Log::debug('Reutilizando token existente', ['feedback_id' => $avaliacao->feedback_id]);

            return [
                'token' => $avaliacao->token_avaliacao,
                'url' => url("/avaliacao-90-dias/{$avaliacao->token_avaliacao}"),
                'expiracao' => Carbon::parse($avaliacao->token_expiracao)
            ];
        }

        // Gera novo token
        Log::debug('Gerando novo token', ['feedback_id' => $avaliacao->feedback_id]);
        return $this->gerarTokenAvaliacao($avaliacao->feedback_id, 60);
    }

    public function enviarEmailVencimentos(User $usuario, array $vencimentos, int $empresaId, ?array $arquivoS3 = null): bool
    {
        try {
            Mail::send(new AvaliacaoNoventaVencimentoMail([
                'usuario' => $usuario,
                'vencimentos' => $vencimentos,
                'empresa_id' => $empresaId,
                'arquivo_s3' => $arquivoS3,
            ]));

            Log::info('E-mail de notificação de avaliação 90 dias enviado', [
                'usuario_id' => $usuario->id,
                'usuario_nome' => $usuario->nome,
                'usuario_email' => $usuario->login,
                'empresa_id' => $empresaId,
                'total_vencimentos' => count($vencimentos)
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Erro ao enviar e-mail de avaliação 90 dias', [
                'usuario_id' => $usuario->id,
                'usuario_nome' => $usuario->nome,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'empresa_id' => $empresaId
            ]);
            return false;
        }
    }

    public function gerarExcelS3(array $vencimentos, int $empresaId): ?array
    {
        $memoryInicial = memory_get_usage(true);

        try {
            if (class_exists('\\PhpOffice\\PhpSpreadsheet\\Settings')) {
                Settings::setLibXmlLoaderOptions(LIBXML_COMPACT);
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Avaliações 90 Dias');

            $spreadsheet->getProperties()
                ->setCreator('Sistema MyBP')
                ->setTitle('Avaliações de 90 Dias - Vencimentos')
                ->setSubject('Avaliações')
                ->setDescription('Relatório de avaliações de 90 dias vencidas ou vencendo')
                ->setCategory('Relatórios');

            $cabecalhos = ['Colaborador', 'Cargo', 'Função', 'Centro de Custo', 'Gestor', 'Data de Vencimento', 'Status', 'Dias em Atraso', 'Observação', 'Avaliações Realizadas', 'Link Avaliação'];
            $coluna = 'A';
            foreach ($cabecalhos as $cabecalho) {
                $sheet->setCellValue($coluna . '1', $cabecalho);
                $coluna++;
            }

            $sheet->getStyle('A1:K1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);

            $linha = 2;
            $batchSize = 100;
            $contador = 0;

            foreach ($vencimentos as $venc) {
                $sheet->setCellValue('A' . $linha, $venc['colaborador']);
                $sheet->setCellValue('B' . $linha, $venc['cargo'] ?? '-');
                $sheet->setCellValue('C' . $linha, $venc['funcao'] ?? '-');
                $sheet->setCellValue('D' . $linha, $venc['centro_custo'] ?? '-');
                $sheet->setCellValue('E' . $linha, trim(($venc['gestor_nome'] ?? '-') . (!empty($venc['gestor_login']) ? ' ('.$venc['gestor_login'].')' : '')));
                $sheet->setCellValue('F' . $linha, $venc['prazo_vencido']);
                $sheet->setCellValue('G' . $linha, $venc['status']);
                $sheet->setCellValue('H' . $linha, $venc['dias_atraso'] > 0 ? $venc['dias_atraso'] : '-');
                $sheet->setCellValue('I' . $linha, $venc['observacao'] ?? '');
                $sheet->setCellValue('J' . $linha, $venc['avaliacao_status'] ?? '');
                $sheet->setCellValue('K' . $linha, $venc['link_avaliacao'] ?? '-');

                $linha++;
                $contador++;

                if ($contador % $batchSize === 0) {
                    gc_collect_cycles();
                }
            }

            $sheet->getColumnDimension('A')->setWidth(40); // Colaborador
            $sheet->getColumnDimension('B')->setWidth(28); // Cargo
            $sheet->getColumnDimension('C')->setWidth(28); // Função
            $sheet->getColumnDimension('D')->setWidth(36); // Centro de Custo
            $sheet->getColumnDimension('E')->setWidth(36); // Gestor
            $sheet->getColumnDimension('F')->setWidth(20); // Data de Vencimento
            $sheet->getColumnDimension('G')->setWidth(15); // Status
            $sheet->getColumnDimension('H')->setWidth(18); // Dias em Atraso
            $sheet->getColumnDimension('I')->setWidth(50); // Observação
            $sheet->getColumnDimension('J')->setWidth(28); // Avaliações Realizadas
            $sheet->getColumnDimension('K')->setWidth(60); // Link Avaliação

            $nomeArquivo = 'avaliacoes_90_dias_' . $empresaId . '_' . date('YmdHis') . '.xlsx';
            $caminhoTempLocal = sys_get_temp_dir() . '/' . $nomeArquivo;

            $writer = new Xlsx($spreadsheet);
            $writer->save($caminhoTempLocal);

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $writer);
            gc_collect_cycles();

            if (!file_exists($caminhoTempLocal)) {
                throw new \Exception('Arquivo Excel não foi criado');
            }

            $tamanhoArquivo = filesize($caminhoTempLocal);

            $caminhoS3 = 'relatorios/avaliacoes-90-dias/' . $nomeArquivo;
            $conteudoArquivo = file_get_contents($caminhoTempLocal);

            $uploadSuccess = Storage::disk('s3')->put($caminhoS3, $conteudoArquivo, [
                'visibility' => 'private',
                'ContentType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'ContentDisposition' => 'attachment; filename="' . $nomeArquivo . '"',
                'Metadata' => [
                    'empresa_id' => (string)$empresaId,
                    'data_geracao' => date('Y-m-d H:i:s'),
                    'tipo' => 'avaliacoes_90_dias'
                ]
            ]);

            if (!$uploadSuccess) {
                throw new \Exception('Falha ao fazer upload para S3');
            }

            $urlTemporaria = Storage::disk('s3')->temporaryUrl($caminhoS3, now()->addDays(7));

            if (file_exists($caminhoTempLocal)) {
                unlink($caminhoTempLocal);
            }

            return [
                'url' => $urlTemporaria,
                'nome_arquivo' => $nomeArquivo,
                'caminho_s3' => $caminhoS3,
                'tamanho' => $tamanhoArquivo,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao gerar Excel S3', ['error' => $e->getMessage()]);

            if (isset($caminhoTempLocal) && file_exists($caminhoTempLocal)) {
                @unlink($caminhoTempLocal);
            }

            return null;
        }
    }

    private function verificarVencimento(AvaliacaoNoventaVencimento $avaliacao, string $dataAtual): ?array
    {
        $dataAtualCarbon = Carbon::createFromFormat('d/m/Y', $dataAtual);
        $dataLimite30Dias = $dataAtualCarbon->copy()->addDays(self::DIAS_ANTECEDENCIA);

        if ($avaliacao->prazo_dia_inicial) {
            $prazoInicial = Carbon::createFromFormat('d/m/Y', $avaliacao->prazo_dia_inicial);
            if ($prazoInicial->lte($dataLimite30Dias)) {
                $diasAtraso = 0;
                $diasParaVencer = 0;
                $status = '';

                if ($prazoInicial->lt($dataAtualCarbon)) {
                    $diasAtraso = $dataAtualCarbon->diffInDays($prazoInicial);
                    $status = 'VENCIDO';
                } elseif ($prazoInicial->equalTo($dataAtualCarbon)) {
                    $status = 'VENCE HOJE';
                } else {
                    $diasParaVencer = $prazoInicial->diffInDays($dataAtualCarbon);
                    $status = 'A VENCER';
                }

                $observacao = $this->gerarObservacaoCompleta($diasAtraso, $diasParaVencer, 'primeira');

                return [
                    'prazo' => $avaliacao->prazo_dia_inicial,
                    'status' => $status,
                    'dias_atraso' => $diasAtraso,
                    'dias_para_vencer' => $diasParaVencer,
                    'observacao' => $observacao,
                ];
            }
        }

        if ($avaliacao->prazo_dia_final) {
            $prazoFinal = Carbon::createFromFormat('d/m/Y', $avaliacao->prazo_dia_final);
            if ($prazoFinal->lte($dataLimite30Dias)) {
                $diasAtraso = 0;
                $diasParaVencer = 0;
                $status = '';

                if ($prazoFinal->lt($dataAtualCarbon)) {
                    $diasAtraso = $dataAtualCarbon->diffInDays($prazoFinal);
                    $status = 'VENCIDO';
                } elseif ($prazoFinal->equalTo($dataAtualCarbon)) {
                    $status = 'VENCE HOJE';
                } else {
                    $diasParaVencer = $prazoFinal->diffInDays($dataAtualCarbon);
                    $status = 'A VENCER';
                }

                $observacao = $this->gerarObservacaoCompleta($diasAtraso, $diasParaVencer, 'segunda');

                return [
                    'prazo' => $avaliacao->prazo_dia_final,
                    'status' => $status,
                    'dias_atraso' => $diasAtraso,
                    'dias_para_vencer' => $diasParaVencer,
                    'observacao' => $observacao,
                ];
            }
        }

        return null;
    }

    private function gerarObservacaoCompleta(int $diasAtraso, int $diasParaVencer, string $tipoAvaliacao): string
    {
        $avaliacao = $tipoAvaliacao === 'primeira' ? '1ª Avaliação' : '2ª Avaliação';

        if ($diasAtraso > 0) {
            if ($diasAtraso <= 7) {
                return "$avaliacao - Atenção: Poucos dias de atraso";
            }
            if ($diasAtraso <= 15) {
                return "$avaliacao - Urgente: Atraso moderado";
            }
            return "$avaliacao - CRÍTICO: Atraso significativo";
        }

        if ($diasParaVencer > 0) {
            if ($diasParaVencer <= 7) {
                return "$avaliacao - Urgente: Vence em menos de 1 semana";
            }
            if ($diasParaVencer <= 15) {
                return "$avaliacao - Atenção: Vence em {$diasParaVencer} dias";
            }
            return "$avaliacao - Vence em {$diasParaVencer} dias";
        }

        return "$avaliacao - Vence hoje";
    }

    public static function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Gera token único para acesso público à avaliação
     *
     * @param int $feedbackId
     * @param int $diasValidade (padrão: 60 dias)
     * @return array ['token' => string, 'expiracao' => Carbon]
     */
    public function gerarTokenAvaliacao(int $feedbackId, int $diasValidade = 60): array
    {
        $token = bin2hex(random_bytes(32)); // 64 caracteres hexadecimais
        $expiracao = Carbon::now()->addDays($diasValidade);

        $vencimento = AvaliacaoNoventaVencimento::where('feedback_id', $feedbackId)->first();

        if ($vencimento) {
            $vencimento->update([
                'token_avaliacao' => $token,
                'token_expiracao' => $expiracao,
                'avaliacao_realizada' => false
            ]);

            Log::info('Token de avaliação gerado', [
                'feedback_id' => $feedbackId,
                'token' => substr($token, 0, 10) . '...',
                'expiracao' => $expiracao->format('Y-m-d H:i:s')
            ]);
        }

        return [
            'token' => $token,
            'expiracao' => $expiracao,
            'url' => url("/avaliacao-90-dias/{$token}")
        ];
    }

    /**
     * Valida token de avaliação e retorna dados se válido
     *
     * @param string $token
     * @return array|null ['valid' => bool, 'vencimento' => AvaliacaoNoventaVencimento|null, 'mensagem' => string]
     */
    public function validarTokenAvaliacao(string $token, $empresaId = null): ?array
    {
        $vencimento = AvaliacaoNoventaVencimento::where('token_avaliacao', $token)
            ->with([
                'FeedbackCurriculo:id,curriculo_id,empresa_id',
                'FeedbackCurriculo.Curriculo:id,nome',
                'FeedbackCurriculo.Admissao:id,feedback_id,status,centro_custo_id,cargo,funcao',
                'FeedbackCurriculo.Admissao.CentroCusto:id,gestor_id,label',
                'FeedbackCurriculo.Admissao.CentroCusto.Gestor:id,nome,login'
            ])
            ->when($empresaId, function ($query) use ($empresaId) {
                $query->whereHas('FeedbackCurriculo', function ($q) use ($empresaId) {
                    $q->where('empresa_id', $empresaId);
                });
            })
            ->first();

        if (!$vencimento) {
            return [
                'valid' => false,
                'vencimento' => null,
                'mensagem' => 'Token inválido ou não encontrado'
            ];
        }

        // Verifica se token expirou
        if ($vencimento->token_expiracao && Carbon::parse($vencimento->token_expiracao)->isPast()) {
            return [
                'valid' => false,
                'vencimento' => $vencimento,
                'mensagem' => 'Token expirado. Solicite um novo link ao RH.'
            ];
        }

        // Verifica se avaliação já foi realizada
        if ($vencimento->avaliacao_realizada) {
            return [
                'valid' => false,
                'vencimento' => $vencimento,
                'mensagem' => 'Esta avaliação já foi realizada anteriormente.'
            ];
        }

        // Verifica se já atingiu limite de avaliações (2)
        $qntAvaliacoes = $vencimento->qntFeedback()->count();
        if ($qntAvaliacoes >= self::MAX_AVALIACOES_PERMITIDAS) {
            return [
                'valid' => false,
                'vencimento' => $vencimento,
                'mensagem' => 'Limite de avaliações atingido para este colaborador.'
            ];
        }

        return [
            'valid' => true,
            'vencimento' => $vencimento,
            'mensagem' => 'Token válido'
        ];
    }

    /**
     * Marca avaliação como realizada via token
     *
     * @param string $token
     * @return bool
     */
    public function marcarAvaliacaoRealizada(string $token): bool
    {
        try {
            $vencimento = AvaliacaoNoventaVencimento::where('token_avaliacao', $token)->first();

            if ($vencimento) {
                $vencimento->update(['avaliacao_realizada' => true]);

                Log::info('Avaliação marcada como realizada via token', [
                    'feedback_id' => $vencimento->feedback_id,
                    'token' => substr($token, 0, 10) . '...'
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Erro ao marcar avaliação como realizada', [
                'token' => substr($token, 0, 10) . '...',
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Regenera token expirado
     *
     * @param int $feedbackId
     * @param int $diasValidade
     * @return array|null
     */
    public function regenerarToken(int $feedbackId, int $diasValidade = 60): ?array
    {
        $vencimento = AvaliacaoNoventaVencimento::where('feedback_id', $feedbackId)->first();

        if (!$vencimento) {
            return null;
        }

        // Reseta o flag de avaliação realizada ao regenerar
        $vencimento->update(['avaliacao_realizada' => false]);

        return $this->gerarTokenAvaliacao($feedbackId, $diasValidade);
    }
}
