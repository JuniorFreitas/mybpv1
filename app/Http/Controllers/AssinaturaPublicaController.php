<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\DocumentoAssinaturaEvento;
use App\Models\DocumentoAssinaturaSignatario;
use App\Models\DocumentoParaAssinatura;
use App\Models\Sistema;
use App\Services\AssinaturaDigital\AssinaturaDigitalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssinaturaPublicaController extends Controller
{
    protected AssinaturaDigitalService $service;

    public function __construct(AssinaturaDigitalService $service)
    {
        $this->service = $service;
    }

    /**
     * Página pública para o signatário visualizar o documento e assinar (sem login).
     * Agora exige 2FA simples: CPF + código enviado por e-mail.
     */
    public function index(Request $request, string $apelido, string $token)
    {
        $signatario = $this->service->validarTokenParaEmpresa($token, $apelido);
        if (!$signatario) {
            abort(404, 'Link inválido ou expirado.');
        }
        ['empresaNome' => $empresaNome, 'nomeDocumento' => $nomeDocumento] = $this->dadosOrigemDocumento($signatario);

        if (!$this->isAcessoVerificado($request, $token, $signatario->id)) {
            $validarCpfUrl = route('documentospreadmissao.assinatura.publica.validar-cpf', ['apelido' => $apelido, 'token' => $token]);
            $codigoUrl = route('documentospreadmissao.assinatura.publica.codigo', ['apelido' => $apelido, 'token' => $token]);
            return view('assinatura.validar_cpf', compact('signatario', 'validarCpfUrl', 'codigoUrl', 'empresaNome', 'nomeDocumento'));
        }

        $doc = $signatario->documentoParaAssinatura;
        $pdfUrl = route('documentospreadmissao.assinatura.publica.pdf', ['apelido' => $apelido, 'token' => $token]);

        if ($doc->status === DocumentoParaAssinatura::STATUS_CONCLUIDO) {
            return view('assinatura.concluido', compact('signatario', 'doc', 'pdfUrl', 'empresaNome', 'nomeDocumento'));
        }
        if ($doc->status === DocumentoParaAssinatura::STATUS_CANCELADO) {
            abort(404, 'Este documento foi cancelado.');
        }
        if ($doc->data_expiracao && $doc->data_expiracao->isPast()) {
            return view('assinatura.expirado', compact('signatario', 'doc', 'empresaNome', 'nomeDocumento'));
        }
        if ($signatario->status !== DocumentoAssinaturaSignatario::STATUS_PENDENTE) {
            return view('assinatura.ja_assinado', compact('signatario', 'doc', 'pdfUrl', 'empresaNome', 'nomeDocumento'));
        }

        $assinarUrl = route('documentospreadmissao.assinatura.publica.assinar', ['apelido' => $apelido, 'token' => $token]);
        $recusarUrl = route('documentospreadmissao.assinatura.publica.recusar', ['apelido' => $apelido, 'token' => $token]);

        $this->service->registrarEvento($doc->id, DocumentoAssinaturaEvento::EVENTO_VISUALIZADO, [
            'signatario_id' => $signatario->id,
            'ip' => $request->ip(),
        ]);

        return view('assinatura.assinar', [
            'signatario' => $signatario,
            'doc' => $doc,
            'pdfUrl' => $pdfUrl,
            'assinarUrl' => $assinarUrl,
            'recusarUrl' => $recusarUrl,
            'empresaNome' => $empresaNome,
            'nomeDocumento' => $nomeDocumento,
        ]);
    }

    /**
     * POST: valida CPF e envia código de verificação por e-mail.
     */
    public function validarCpf(Request $request, string $apelido, string $token)
    {
        $signatario = $this->service->validarTokenParaEmpresa($token, $apelido);
        if (!$signatario) {
            return redirect()->back()->with('error', 'Link inválido ou expirado.');
        }

        $request->validate([
            'cpf' => 'required|string|min:11|max:14',
        ]);

        $cpf = (string) $request->input('cpf');
        if (!$this->service->validarCpfSignatario($signatario, $cpf)) {
            return redirect()->back()->withInput()->with('error', 'CPF inválido para este signatário.');
        }
        $cooldown = $this->service->podeReenviarCodigoVerificacao($signatario);
        if (!$cooldown['pode_enviar']) {
            return redirect()->back()->withInput()->with(
                'error',
                'Aguarde ' . $this->formatarDuracao((int) $cooldown['segundos_restantes']) . ' para reenviar o código.'
            );
        }

        try {
            $empresa = $signatario->documentoParaAssinatura ? $signatario->documentoParaAssinatura->empresa : null;
            $this->service->enviarCodigoVerificacao($signatario, $empresa);
            $this->marcarCodigoPendente($request, $token, $signatario->id);
            $this->limparAcessoVerificado($request, $token);

            return redirect()->route('documentospreadmissao.assinatura.publica.codigo', ['apelido' => $apelido, 'token' => $token])
                ->with('success', 'Código enviado para seu e-mail. Informe-o para continuar.');
        } catch (\Throwable $e) {
            \Log::error('AssinaturaPublicaController: falha ao enviar código de verificação', [
                'signatario_id' => $signatario->id,
                'erro' => $e->getMessage(),
            ]);
            return redirect()->back()->withInput()->with('error', 'Não foi possível enviar o código agora. Tente novamente.');
        }
    }

    /**
     * GET: tela para digitar o código enviado por e-mail.
     */
    public function codigo(Request $request, string $apelido, string $token)
    {
        $signatario = $this->service->validarTokenParaEmpresa($token, $apelido);
        if (!$signatario) {
            abort(404, 'Link inválido ou expirado.');
        }
        ['empresaNome' => $empresaNome, 'nomeDocumento' => $nomeDocumento] = $this->dadosOrigemDocumento($signatario);

        if ($this->isAcessoVerificado($request, $token, $signatario->id)) {
            return redirect()->route('documentospreadmissao.assinatura.publica.index', ['apelido' => $apelido, 'token' => $token]);
        }

        if (!$this->isCodigoPendente($request, $token, $signatario->id)) {
            return redirect()->route('documentospreadmissao.assinatura.publica.index', ['apelido' => $apelido, 'token' => $token])
                ->with('error', 'Primeiro valide seu CPF para receber o código.');
        }

        $validarCodigoUrl = route('documentospreadmissao.assinatura.publica.validar-codigo', ['apelido' => $apelido, 'token' => $token]);
        $voltarCpfUrl = route('documentospreadmissao.assinatura.publica.index', ['apelido' => $apelido, 'token' => $token]);

        return view('assinatura.validar_codigo', [
            'signatario' => $signatario,
            'validarCodigoUrl' => $validarCodigoUrl,
            'voltarCpfUrl' => $voltarCpfUrl,
            'empresaNome' => $empresaNome,
            'nomeDocumento' => $nomeDocumento,
        ]);
    }

    /**
     * POST: valida o código de verificação enviado por e-mail.
     */
    public function validarCodigo(Request $request, string $apelido, string $token)
    {
        $signatario = $this->service->validarTokenParaEmpresa($token, $apelido);
        if (!$signatario) {
            return redirect()->back()->with('error', 'Link inválido ou expirado.');
        }

        if (!$this->isCodigoPendente($request, $token, $signatario->id)) {
            return redirect()->route('documentospreadmissao.assinatura.publica.index', ['apelido' => $apelido, 'token' => $token])
                ->with('error', 'A validação de código expirou. Informe o CPF novamente.');
        }
        $statusTentativas = $this->service->statusTentativasCodigoVerificacao($signatario);
        if ($statusTentativas['bloqueado']) {
            return redirect()->back()->with('error', 'Muitas tentativas inválidas. Tente novamente em ' . $this->formatarDuracao((int) $statusTentativas['segundos_restantes']) . '.');
        }

        $request->validate([
            'codigo' => 'required|string|max:8',
        ]);

        $codigo = (string) $request->input('codigo');
        if (!$this->service->validarCodigoVerificacao($signatario, $codigo)) {
            $status = $this->service->registrarFalhaCodigoVerificacao($signatario);
            if ($status['bloqueado']) {
                return redirect()->back()->withInput()->with('error', 'Muitas tentativas inválidas. Tente novamente em ' . $this->formatarDuracao((int) $status['segundos_restantes']) . '.');
            }
            return redirect()->back()->withInput()->with('error', 'Código inválido ou expirado. Tentativas restantes: ' . (int) $status['tentativas_restantes'] . '.');
        }

        $this->service->invalidarCodigoVerificacao($signatario);
        $this->service->limparTentativasCodigoVerificacao($signatario);
        $this->limparCodigoPendente($request, $token);
        $this->marcarAcessoVerificado($request, $token, $signatario->id);

        return redirect()->route('documentospreadmissao.assinatura.publica.index', ['apelido' => $apelido, 'token' => $token])
            ->with('success', 'Verificação concluída com sucesso.');
    }

    /**
     * Stream do PDF do documento (acesso apenas com token válido).
     * Se o documento estiver concluído, exibe o PDF com marca d'água "ASSINADO DIGITALMENTE".
     */
    public function pdf(Request $request, string $apelido, string $token)
    {
        $signatario = $this->service->validarTokenParaEmpresa($token, $apelido);
        if (!$signatario) {
            abort(404);
        }
        if (!$this->isAcessoVerificado($request, $token, $signatario->id)) {
            abort(403, 'Validação de segurança pendente.');
        }

        $doc = $signatario->documentoParaAssinatura;
        $doc->load('arquivoAssinado', 'arquivo');

        $arquivo = ($doc->status === DocumentoParaAssinatura::STATUS_CONCLUIDO && $doc->arquivoAssinado)
            ? $doc->arquivoAssinado
            : $doc->arquivo;

        if (!$arquivo || $arquivo->disco !== Arquivo::DISCO_DOCUMENTO_ASSINATURA) {
            abort(404);
        }

        if (!Storage::disk($arquivo->disco)->exists($arquivo->file)) {
            abort(404);
        }

        return Storage::disk($arquivo->disco)->response($arquivo->file, $arquivo->nome, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * POST: Assinar o documento (evidências capturadas no service).
     */
    public function assinar(Request $request, string $apelido, string $token)
    {
        $signatario = $this->service->validarTokenParaEmpresa($token, $apelido);
        if (!$signatario) {
            return response()->json(['success' => false, 'message' => 'Link inválido ou expirado.'], 404);
        }
        if (!$this->isAcessoVerificado($request, $token, $signatario->id)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Validação de segurança pendente.'], 403);
            }
            return redirect()->route('documentospreadmissao.assinatura.publica.index', ['apelido' => $apelido, 'token' => $token])
                ->with('error', 'Valide CPF e código de segurança para continuar.');
        }

        $request->validate([
            'consentimento' => 'required|accepted',
            'cpf' => 'nullable|string|size:14',
        ]);

        $cpf = $request->input('cpf') ? preg_replace('/\D/', '', $request->input('cpf')) : null;
        if ($cpf !== null && $cpf !== '' && Sistema::validaCPF($cpf) !== true) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'CPF inválido.'], 422);
            }
            return redirect()->back()->withInput()->with('error', 'CPF inválido.');
        }
        if ($cpf !== null && strlen($cpf) === 11) {
            $cpf = substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
        }

        $result = $this->service->assinar($token, $request, $cpf);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }
        return redirect()->route('documentospreadmissao.assinatura.publica.index', ['apelido' => $apelido, 'token' => $token])
            ->with('success', $result['message']);
    }

    /**
     * POST: Recusar o documento.
     */
    public function recusar(Request $request, string $apelido, string $token)
    {
        $signatario = $this->service->validarTokenParaEmpresa($token, $apelido);
        if (!$signatario) {
            return response()->json(['success' => false, 'message' => 'Link inválido ou expirado.'], 404);
        }
        if (!$this->isAcessoVerificado($request, $token, $signatario->id)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Validação de segurança pendente.'], 403);
            }
            return redirect()->route('documentospreadmissao.assinatura.publica.index', ['apelido' => $apelido, 'token' => $token])
                ->with('error', 'Valide CPF e código de segurança para continuar.');
        }

        $motivo = $request->input('motivo');
        $result = $this->service->recusar($token, $request, $motivo);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }
        return redirect()->route('documentospreadmissao.assinatura.publica.index', ['apelido' => $apelido, 'token' => $token])
            ->with('success', $result['message']);
    }

    private function isAcessoVerificado(Request $request, string $token, int $signatarioId): bool
    {
        $dados = $request->session()->get($this->chaveSessaoVerificado($token));
        return is_array($dados) && (int) ($dados['signatario_id'] ?? 0) === $signatarioId;
    }

    private function marcarAcessoVerificado(Request $request, string $token, int $signatarioId): void
    {
        $request->session()->put($this->chaveSessaoVerificado($token), [
            'signatario_id' => $signatarioId,
            'verified_at' => now()->toDateTimeString(),
        ]);
    }

    private function limparAcessoVerificado(Request $request, string $token): void
    {
        $request->session()->forget($this->chaveSessaoVerificado($token));
    }

    private function isCodigoPendente(Request $request, string $token, int $signatarioId): bool
    {
        $dados = $request->session()->get($this->chaveSessaoCodigoPendente($token));
        return is_array($dados) && (int) ($dados['signatario_id'] ?? 0) === $signatarioId;
    }

    private function marcarCodigoPendente(Request $request, string $token, int $signatarioId): void
    {
        $request->session()->put($this->chaveSessaoCodigoPendente($token), [
            'signatario_id' => $signatarioId,
            'sent_at' => now()->toDateTimeString(),
        ]);
    }

    private function limparCodigoPendente(Request $request, string $token): void
    {
        $request->session()->forget($this->chaveSessaoCodigoPendente($token));
    }

    private function chaveSessaoVerificado(string $token): string
    {
        return 'assinatura_publica_verificado.' . $token;
    }

    private function chaveSessaoCodigoPendente(string $token): string
    {
        return 'assinatura_publica_codigo_pendente.' . $token;
    }

    private function formatarDuracao(int $segundos): string
    {
        if ($segundos < 60) {
            return $segundos . 's';
        }

        $minutos = (int) ceil($segundos / 60);
        return $minutos . ' min';
    }

    private function dadosOrigemDocumento(DocumentoAssinaturaSignatario $signatario): array
    {
        $doc = $signatario->documentoParaAssinatura;
        if (!$doc) {
            return [
                'empresaNome' => 'Empresa',
                'nomeDocumento' => 'Documento',
            ];
        }

        $empresa = $doc->empresa;
        $empresaNome = trim((string) ($empresa->razao_social ?? $empresa->nome_fantasia ?? $empresa->apelido ?? 'Empresa'));
        $nomeDocumento = DocumentoParaAssinatura::labelTipoDocumento((string) $doc->tipo_documento);

        return [
            'empresaNome' => $empresaNome !== '' ? $empresaNome : 'Empresa',
            'nomeDocumento' => $nomeDocumento !== '' ? $nomeDocumento : 'Documento',
        ];
    }
}
