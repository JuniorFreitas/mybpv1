<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DocumentoAssinaturaEvento;
use App\Models\DocumentoAssinaturaSignatario;
use App\Models\DocumentoParaAssinatura;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Página pública de verificação de assinatura digital (acessada pelo QR code no PDF).
 * GET /verificacao-assinatura?d={documento_id}&s={signatario_id}&h={hash_evidencia}
 */
class VerificacaoAssinaturaController extends Controller
{
    public function index(Request $request, string $apelido = null): View
    {
        $documentoId = (int) $request->query('d');
        $signatarioId = (int) $request->query('s');
        $hashInformado = $request->query('h', '');

        $valido = false;
        $documento = null;
        $signatario = null;
        $empresa = null;
        $historico = [];
        $mensagem = 'Link de verificacao invalido ou expirado.';

        if ($documentoId && $signatarioId) {
            $documento = DocumentoParaAssinatura::withoutGlobalScopes()
                ->with(['arquivo', 'eventos', 'signatarios', 'solicitante'])
                ->find($documentoId);

            if ($documento) {
                $signatario = DocumentoAssinaturaSignatario::where('documento_para_assinatura_id', $documento->id)
                    ->where('id', $signatarioId)
                    ->first();

                if ($signatario) {
                    $hashOk = $hashInformado === '' || hash_equals((string) $signatario->hash_evidencia, $hashInformado);
                    if ($hashOk) {
                        $empresa = Cliente::withoutGlobalScopes()->find($documento->empresa_id);
                        if ($apelido && $empresa && $empresa->apelido && $empresa->apelido !== $apelido) {
                            $mensagem = 'Empresa informada nao confere com o documento.';
                        } else {
                            $valido = true;
                            $mensagem = 'Assinatura verificada com sucesso. Este documento foi assinado digitalmente conforme a legislacao brasileira.';
                            $historico = $this->montarHistorico($documento);
                        }
                    } else {
                        $mensagem = 'O identificador da assinatura (hash) nao confere. A assinatura pode ter sido alterada.';
                    }
                } else {
                    $mensagem = 'Signatario nao encontrado para este documento.';
                }
            } else {
                $mensagem = 'Documento nao encontrado.';
            }
        }

        return view('assinatura.verificacao', [
            'valido' => $valido,
            'mensagem' => $mensagem,
            'documento' => $documento,
            'signatario' => $signatario,
            'empresa' => $empresa,
            'historico' => $historico,
        ]);
    }

    /**
     * Monta o histórico de eventos do documento (mesmo formato da página de assinaturas do PDF).
     *
     * @return array<int, array{data_formatada: string, hora: string, descricao: string}>
     */
    private function montarHistorico(DocumentoParaAssinatura $doc): array
    {
        $tz = 'America/Sao_Paulo';
        $lista = [];
        $signatariosPorId = $doc->signatarios->keyBy('id');

        foreach ($doc->eventos->sortBy('created_at') as $ev) {
            $data = $ev->created_at->setTimezone($tz);
            $payload = is_array($ev->payload) ? $ev->payload : [];
            $nome = $payload['nome'] ?? null;
            $email = $payload['email'] ?? '';
            $cpf = isset($payload['cpf']) && $payload['cpf'] ? $payload['cpf'] : '';
            $ip = $payload['ip'] ?? '';
            if (!$nome && !empty($payload['signatario_id'])) {
                $s = $signatariosPorId->get($payload['signatario_id']);
                if ($s) {
                    $nome = $s->nome ?: $s->email;
                    if (empty($email)) {
                        $email = $s->email ?? '';
                    }
                    if (empty($cpf) && $s->cpf) {
                        $cpf = $s->cpf;
                    }
                }
            }
            if ($cpf && strlen(preg_replace('/\D/', '', $cpf)) === 11) {
                $n = preg_replace('/\D/', '', $cpf);
                $cpf = substr($n, 0, 3) . '.' . substr($n, 3, 3) . '.' . substr($n, 6, 3) . '-' . substr($n, 9, 2);
            }
            $nome = $nome ?: 'Sistema';

            $descricao = '';
            switch ($ev->evento) {
                case DocumentoAssinaturaEvento::EVENTO_ENVIADO:
                    $solicitante = $doc->solicitante;
                    $nomeSol = $solicitante ? $solicitante->nome : 'Sistema';
                    $descricao = $nomeSol . ' enviou este documento para assinatura digital. (Email: ' . ($solicitante && $solicitante->email ? $solicitante->email : '—') . ')';
                    break;
                case DocumentoAssinaturaEvento::EVENTO_VISUALIZADO:
                    $descricao = $nome . ' (Email: ' . $email . ($cpf ? ', CPF: ' . $cpf : '') . ') visualizou este documento por meio do IP ' . ($ip ?: '—');
                    break;
                case DocumentoAssinaturaEvento::EVENTO_ASSINADO:
                    $descricao = $nome . ' (Email: ' . $email . ($cpf ? ', CPF: ' . $cpf : '') . ') assinou este documento por meio do IP ' . ($ip ?: '—');
                    break;
                case DocumentoAssinaturaEvento::EVENTO_RECUSADO:
                    $motivo = $payload['motivo'] ?? '';
                    $descricao = $nome . ' (Email: ' . $email . ') recusou este documento.' . ($motivo ? ' Motivo: ' . $motivo : '');
                    break;
                case DocumentoAssinaturaEvento::EVENTO_DOWNLOAD:
                    $descricao = $nome . ' realizou download do documento assinado.' . ($ip ? ' (IP: ' . $ip . ')' : '');
                    break;
                default:
                    $descricao = 'Evento: ' . $ev->evento;
            }

            $lista[] = [
                'data_formatada' => $data->format('d M Y'),
                'hora' => $data->format('H:i:s'),
                'descricao' => $descricao,
            ];
        }

        return $lista;
    }
}
