<?php

namespace App\Jobs\AssinaturaDigital;

use App\Models\Admissao;
use App\Models\CartaOferta;
use App\Models\CartaOfertaTemplate;
use App\Models\Cliente;
use App\Models\DemissaoPrevista;
use App\Models\DocumentoContratos;
use App\Models\EmpresaTemporaria;
use App\Models\FeedbackCurriculo;
use App\Models\MedidaAdministrativa;
use App\Models\Sistema;
use App\Models\User;
use App\Services\AssinaturaDigital\AssinaturaDigitalService;
use App\Services\CartaOferta\CartaOfertaTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MasterTag\DataHora;
use PDF;

class JobProcessarEnvioAssinatura implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const TIPO_CONTRATO = 'contrato_legal';
    public const TIPO_DEMISSAO = 'documento_demissao';
    public const TIPO_CARTA_OFERTA = 'carta_oferta';
    public const TIPO_MEDIDA = 'medida_administrativa';
    public const TIPO_DOSSIE = 'dossie';

    public $tries = 3;

    protected string $tipo;
    protected int $empresaId;
    protected int $solicitanteId;
    protected array $payload;
    protected array $signatarios;

    public function __construct(string $tipo, int $empresaId, int $solicitanteId, array $payload = [], array $signatarios = [])
    {
        $this->tipo = $tipo;
        $this->empresaId = $empresaId;
        $this->solicitanteId = $solicitanteId;
        $this->payload = $payload;
        $this->signatarios = $signatarios;
    }

    public function handle(AssinaturaDigitalService $service): void
    {
        try {
            Auth::onceUsingId($this->solicitanteId);

            switch ($this->tipo) {
                case self::TIPO_CONTRATO:
                    $this->processarContrato($service);
                    break;
                case self::TIPO_DEMISSAO:
                    $this->processarDemissao($service);
                    break;
                case self::TIPO_CARTA_OFERTA:
                    $this->processarCartaOferta($service);
                    break;
                case self::TIPO_MEDIDA:
                    $this->processarMedida($service);
                    break;
                case self::TIPO_DOSSIE:
                    $this->processarDossie($service);
                    break;
                default:
                    Log::warning('JobProcessarEnvioAssinatura: tipo inválido', ['tipo' => $this->tipo]);
            }
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Cota mensal de assinatura digital')) {
                Log::warning('JobProcessarEnvioAssinatura: bloqueado por cota', [
                    'tipo' => $this->tipo,
                    'empresa_id' => $this->empresaId,
                    'payload' => $this->payload,
                    'message' => $e->getMessage(),
                ]);
                return;
            }
            throw $e;
        } catch (\Throwable $e) {
            Log::error('JobProcessarEnvioAssinatura: erro', [
                'tipo' => $this->tipo,
                'empresa_id' => $this->empresaId,
                'payload' => $this->payload,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    protected function processarContrato(AssinaturaDigitalService $service): void
    {
        $contratoId = (int) ($this->payload['contrato_id'] ?? 0);
        if (!$contratoId) {
            return;
        }

        $contrato = DocumentoContratos::where('empresa_id', $this->empresaId)->find($contratoId);
        if (!$contrato) {
            Log::warning('JobProcessarEnvioAssinatura[contrato]: contrato não encontrado', ['contrato_id' => $contratoId]);
            return;
        }

        $dados = $contrato;
        $pdf = PDF::loadView('pdf/administracao/documentoslegais/contrato/contratopdf', compact('dados'));
        $pdf->setPaper('A4', 'portrait');
        $pdfContent = $pdf->output();

        $nome = $contrato->dados_cadastrais && (isset($contrato->dados_cadastrais->tipo) || isset($contrato->dados_cadastrais->nome))
            ? (($contrato->dados_cadastrais->tipo ?? '') == 'Pessoa Jurídica' ? ($contrato->dados_cadastrais->razao_social ?? 'contrato') : ($contrato->dados_cadastrais->nome ?? 'contrato'))
            : 'contrato';
        $nomeArquivo = 'contrato_' . Str::slug($nome) . '.pdf';

        $service->criarEnvio(
            $this->empresaId,
            'contrato_legal',
            DocumentoContratos::class,
            $contrato->id,
            $this->solicitanteId,
            $this->signatarios,
            'sequencial',
            $pdfContent,
            $nomeArquivo,
            null
        );
    }

    protected function processarDemissao(AssinaturaDigitalService $service): void
    {
        $demissaoId = (int) ($this->payload['demissao_prevista_id'] ?? 0);
        if (!$demissaoId) {
            return;
        }

        $demissaoPrevista = DemissaoPrevista::whereId($demissaoId)
            ->where('empresa_id', $this->empresaId)
            ->with('Colaborador')
            ->first();
        if (!$demissaoPrevista || !$demissaoPrevista->Colaborador) {
            Log::warning('JobProcessarEnvioAssinatura[demissao]: registro não encontrado', ['demissao_prevista_id' => $demissaoId]);
            return;
        }

        $pdf = PDF::loadView('pdf.planejamento.movimentacao.demissao.avisoprevio', compact('demissaoPrevista'));
        $pdf->setPaper('A4', 'portrait');
        $pdfContent = $pdf->output();
        $nomeArquivo = 'aviso_previo_' . Str::slug($demissaoPrevista->Colaborador->nome) . '_' . (new DataHora())->nomeUnico() . '.pdf';

        $service->criarEnvio(
            $this->empresaId,
            'documento_demissao',
            DemissaoPrevista::class,
            $demissaoPrevista->id,
            $this->solicitanteId,
            $this->signatarios,
            'sequencial',
            $pdfContent,
            $nomeArquivo,
            null
        );
    }

    protected function processarCartaOferta(AssinaturaDigitalService $service): void
    {
        $cartaOfertaId = (int) ($this->payload['carta_oferta_id'] ?? 0);
        if (!$cartaOfertaId) {
            return;
        }

        $cartaOferta = CartaOferta::where('id', $cartaOfertaId)
            ->where('empresa_id', $this->empresaId)
            ->with([
                'Curriculo:id,nome,email,cpf',
                'empresa:id,razao_social,nome_fantasia,cnpj',
                'vagaAberta:id,vaga_id',
                'vagaAberta.Vaga:id,nome',
                'vagaProjeto:id,vaga_aberta_id',
                'vagaProjeto.VagaAberta:id,vaga_id',
                'vagaProjeto.VagaAberta.Vaga:id,nome',
            ])
            ->first();

        if (!$cartaOferta || !$cartaOferta->Curriculo || $cartaOferta->status !== CartaOferta::STATUS_PENDENTE_ANEXO) {
            Log::warning('JobProcessarEnvioAssinatura[carta_oferta]: inválida para envio', ['carta_oferta_id' => $cartaOfertaId]);
            return;
        }

        $nomeCargo = 'Cargo';
        if ($cartaOferta->vagaAberta && $cartaOferta->vagaAberta->Vaga) {
            $nomeCargo = $cartaOferta->vagaAberta->Vaga->nome;
        } elseif ($cartaOferta->vagaProjeto && $cartaOferta->vagaProjeto->VagaAberta && $cartaOferta->vagaProjeto->VagaAberta->Vaga) {
            $nomeCargo = $cartaOferta->vagaProjeto->VagaAberta->Vaga->nome;
        }

        $template = CartaOfertaTemplate::query()
            ->where('empresa_id', $this->empresaId)
            ->publicado()
            ->orderBy('versao', 'desc')
            ->first();

        $empresaNome = '';
        $empresaRazao = '';
        $empresaCnpj = '';
        if ($cartaOferta->empresa) {
            $empresaNome = $cartaOferta->empresa->nome_fantasia ?? $cartaOferta->empresa->razao_social ?? '';
            $empresaRazao = $cartaOferta->empresa->razao_social ?? '';
            $empresaCnpj = $cartaOferta->empresa->cnpj ?? '';
        }

        $html = null;
        if ($template) {
            $dados = [
                'colaborador' => [
                    'nome' => $cartaOferta->Curriculo->nome ?? '',
                    'cpf' => $cartaOferta->Curriculo->cpf ?? '',
                    'email' => $cartaOferta->Curriculo->email ?? '',
                ],
                'cargo' => $nomeCargo,
                'setor' => '',
                'salario' => '',
                'data_inicio' => '',
                'empresa' => [
                    'nome_fantasia' => $empresaNome,
                    'razao_social' => $empresaRazao,
                    'cnpj' => $empresaCnpj,
                ],
                'data_emissao' => (new DataHora())->dataCompletaExt(),
            ];

            $html = app(CartaOfertaTemplateRenderer::class)->render($template->conteudo_html, $dados);
        }

        if ($html) {
            $pdf = PDF::loadView('pdf.admissao.carta-oferta-template', ['html' => $html]);
        } else {
            $pdf = PDF::loadView('pdf.admissao.carta-oferta', compact('cartaOferta', 'nomeCargo'));
        }

        $pdf->setPaper('A4', 'portrait');
        $pdfContent = $pdf->output();

        $signatarios = [[
            'nome' => $cartaOferta->Curriculo->nome,
            'email' => $cartaOferta->Curriculo->email ?? '',
            'cpf' => $cartaOferta->Curriculo->cpf ?? null,
            'user_id' => null,
        ]];
        if (empty($signatarios[0]['email'])) {
            Log::warning('JobProcessarEnvioAssinatura[carta_oferta]: candidato sem e-mail', ['carta_oferta_id' => $cartaOfertaId]);
            return;
        }

        $nomeArquivo = 'carta_oferta_' . Str::slug($cartaOferta->Curriculo->nome) . '_' . (new DataHora())->nomeUnico() . '.pdf';

        $service->criarEnvio(
            $this->empresaId,
            'carta_oferta',
            CartaOferta::class,
            $cartaOferta->id,
            $this->solicitanteId,
            $signatarios,
            'sequencial',
            $pdfContent,
            $nomeArquivo,
            null
        );
    }

    protected function processarMedida(AssinaturaDigitalService $service): void
    {
        $medidaId = (int) ($this->payload['medida_id'] ?? 0);
        if (!$medidaId) {
            return;
        }

        $medida = MedidaAdministrativa::withoutGlobalScopes()
            ->whereId($medidaId)
            ->whereHas('Feedback', function ($query) {
                $query->where('empresa_id', $this->empresaId);
            })
            ->with(
                'Anexos',
                'Feedback:id,curriculo_id,empresa_id',
                'Feedback.Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento',
                'Feedback.Empresa:id,cnpj,razao_social,nome_fantasia,cep,logradouro,numero,complemento,bairro,municipio,uf,contato',
                'Feedback.Empresa.Logo:id,nome,layout,disco,imagem,file,thumb',
                'Feedback.Admissao:id,feedback_id,data_admissao'
            )
            ->first();

        if (!$medida || !$medida->Feedback) {
            Log::warning('JobProcessarEnvioAssinatura[medida]: não encontrada', ['medida_id' => $medidaId]);
            return;
        }

        $pdf = PDF::loadView('pdf.admissao.historico.medidasadministrativas.carta-advertencia', compact('medida'));
        $pdf->setPaper('A4', 'portrait');
        $pdfContent = $pdf->output();
        $nomeArquivo = 'carta_' . Str::slug($medida->tipo) . '_' . (new DataHora())->nomeUnico() . '.pdf';

        $service->criarEnvio(
            $this->empresaId,
            'medida_administrativa',
            MedidaAdministrativa::class,
            $medida->id,
            $this->solicitanteId,
            $this->signatarios,
            'sequencial',
            $pdfContent,
            $nomeArquivo,
            null
        );
    }

    protected function processarDossie(AssinaturaDigitalService $service): void
    {
        $tipoModelo = (string) ($this->payload['tipo_modelo'] ?? '');
        $curriculoId = (int) ($this->payload['curriculo_id'] ?? 0);
        $feedbackId = (int) ($this->payload['feedback_id'] ?? 0);
        if (!$tipoModelo || !$curriculoId || !$feedbackId) {
            return;
        }

        $colaborador = FeedbackCurriculo::with(['Curriculo', 'Admissao'])
            ->whereCurriculoId($curriculoId)
            ->whereId($feedbackId)
            ->first();
        if (!$colaborador || $colaborador->empresa_id != $this->empresaId || !$colaborador->Admissao || !$colaborador->Curriculo) {
            Log::warning('JobProcessarEnvioAssinatura[dossie]: inválido para envio', ['feedback_id' => $feedbackId, 'curriculo_id' => $curriculoId]);
            return;
        }

        $cliente = Cliente::withoutGlobalScopes()->find($this->empresaId);
        if (!$cliente) {
            return;
        }

        $tipoAdmissao = Str::slug($colaborador->Admissao->tipo_admissao);
        $usuarioSolicitante = User::select('nome')->find($this->solicitanteId);
        $dados = [
            'dados_empresa' => Sistema::getEmpresaFilialMatriz($colaborador->Admissao->centro_custo_filial_id, $colaborador->empresa_id),
            'dados_colaborador' => $colaborador,
            'solicitante' => $usuarioSolicitante ? $usuarioSolicitante->nome : 'Sistema',
        ];

        if ($tipoModelo === 'contratotrabalhoassinado') {
            if (in_array($colaborador->Admissao->tipo_admissao, [Admissao::TIPO_ADMISSAO_TEMPORARIO, Admissao::TIPO_ADMISSAO_INTERMITENTE, Admissao::TIPO_ADMISSAO_DETERMINADO])) {
                $temporaria = EmpresaTemporaria::whereEmpresaId($colaborador->empresa_id)->first();
                $pdf = PDF::loadView('pdf.historico.dossie.contratos.' . $tipoAdmissao, compact('dados', 'cliente', 'temporaria'));
            } else {
                $view = "pdf.historico.dossie.customizado.{$cliente->apelido}.contratos.{$tipoModelo}";
                if (view()->exists($view)) {
                    $pdf = PDF::loadView($view, compact('dados', 'cliente'));
                } else {
                    $pdf = PDF::loadView('pdf.historico.dossie.default.contratos.' . $tipoModelo, compact('dados', 'cliente'));
                }
            }
        } else {
            $pdf = PDF::loadView('pdf.historico.dossie.' . $tipoModelo, compact('dados', 'cliente'));
        }

        $pdf->setPaper('A4', 'portrait');
        $pdfContent = $pdf->output();
        $nomeArquivo = $tipoModelo . '_' . Str::slug($colaborador->Curriculo->nome ?? 'documento') . '.pdf';

        $service->criarEnvio(
            $this->empresaId,
            $this->tipoModeloParaTipoDocumento($tipoModelo),
            FeedbackCurriculo::class,
            $colaborador->id,
            $this->solicitanteId,
            $this->signatarios,
            'sequencial',
            $pdfContent,
            $nomeArquivo,
            null
        );
    }

    protected function tipoModeloParaTipoDocumento(string $tipoModelo): string
    {
        $map = [
            'contratotrabalhoassinado' => 'contrato_trabalho',
            'termoconfiabilidade' => 'termo_confidencialidade',
            'valetransporte' => 'opcao_vale_transporte',
            'acordocompensacaohoras' => 'acordo_compensacao_horas',
            'termosalariofamilia' => 'termo_salario_familia',
            'declaracaodependentesimposto' => 'declaracao_dependentes_ir',
        ];
        return $map[$tipoModelo] ?? $tipoModelo;
    }
}
