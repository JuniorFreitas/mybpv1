<?php

namespace App\Services\Preadmissao;

use App\Models\Arquivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Quem pode enviar, ver, baixar e excluir anexos do disco de documentos de pré-admissão
 * (rotas `/documentos/uploadAnexos` e `/documentos/anexo*`, usadas pelo RH e pelo candidato).
 *
 * - Usuário logado no sistema (RH): só anexos de candidatos da própria empresa (multi-tenant).
 * - Candidato: só depois de autenticar com CPF + código (documentos ou carta oferta), e apenas
 *   para os próprios arquivos — os já vinculados ao seu currículo/carta ou os que enviou nesta sessão.
 * - Sem nenhum dos dois: negado.
 *
 * Ver, baixar e excluir exigem também URL assinada (`signed:relative` nas rotas).
 */
class AnexoPreAdmissaoAcessoService
{
    protected const SESSAO = 'acesso_publico_anexos';

    /**
     * Chamado quando o candidato conclui a autenticação por código.
     */
    public function liberarCandidato(Request $request, int $curriculoId): void
    {
        $dados = $this->dados($request);
        $dados['curriculos'] = array_values(array_unique([...$dados['curriculos'], $curriculoId]));
        $request->session()->put(self::SESSAO, $dados);
    }

    public function podeEnviar(Request $request): bool
    {
        return auth()->check() || $this->dados($request)['curriculos'] !== [];
    }

    /**
     * Guarda o arquivo recém-enviado para o candidato poder vê-lo/excluí-lo antes de salvar.
     */
    public function registrarEnvio(Request $request, int $arquivoId): void
    {
        if (auth()->check()) {
            return;
        }

        $dados = $this->dados($request);
        $dados['arquivos'][] = $arquivoId;
        // Limite só para a sessão não crescer sem fim.
        $dados['arquivos'] = array_slice(array_values(array_unique($dados['arquivos'])), -200);
        $request->session()->put(self::SESSAO, $dados);
    }

    /**
     * `$arquivo` é o nome físico (ou da miniatura) que vem na URL.
     */
    public function podeAcessar(Request $request, string $arquivo): bool
    {
        $registro = DB::table('arquivos')
            ->where('disco', Arquivo::DISCO_DOCUMENTOS_PRE_ADMISSAO)
            ->where(function ($q) use ($arquivo) {
                $q->where('file', $arquivo)->orWhere('thumb', $arquivo);
            })
            ->first(['id', 'quem_enviou']);

        if (!$registro) {
            return false;
        }

        if ($usuario = auth()->user()) {
            return $this->usuarioPodeAcessar($usuario, $registro);
        }

        $dados = $this->dados($request);
        if ($dados['curriculos'] === []) {
            return false;
        }

        return in_array((int) $registro->id, $dados['arquivos'], true)
            || $this->curriculosDoArquivo((int) $registro->id)->intersect($dados['curriculos'])->isNotEmpty();
    }

    /**
     * Multi-tenant: o usuário só acessa anexo de candidato ligado à própria empresa
     * (ou o arquivo que ele mesmo enviou e ainda não foi vinculado a ninguém).
     */
    protected function usuarioPodeAcessar($usuario, object $arquivo): bool
    {
        $empresaId = (int) $usuario->empresa_id;
        $curriculos = $this->curriculosDoArquivo((int) $arquivo->id);

        if ($curriculos->isEmpty()) {
            return (int) $arquivo->quem_enviou === (int) $usuario->id;
        }

        return DB::table('feedback_curriculos')
                ->whereIn('curriculo_id', $curriculos)
                ->where('empresa_id', $empresaId)
                ->whereNull('deleted_at')
                ->exists()
            || DB::table('curriculo_carta_oferta')
                ->whereIn('curriculo_id', $curriculos)
                ->where('empresa_id', $empresaId)
                ->exists()
            // O cadastro do candidato (Curriculo::Pessoa) é um usuário com o mesmo id.
            || DB::table('users')
                ->whereIn('id', $curriculos)
                ->where('empresa_id', $empresaId)
                ->whereNull('deleted_at')
                ->exists();
    }

    /**
     * Candidatos aos quais o arquivo está vinculado (documentos de pré-admissão ou carta oferta).
     */
    protected function curriculosDoArquivo(int $arquivoId): \Illuminate\Support\Collection
    {
        return DB::table('documentos_curriculos')
            ->where('arquivo_id', $arquivoId)
            ->pluck('curriculo_id')
            ->merge(
                DB::table('curriculo_carta_oferta')
                    ->where('arquivo_id', $arquivoId)
                    ->whereNotNull('curriculo_id')
                    ->pluck('curriculo_id')
            )
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    /**
     * @return array{curriculos: int[], arquivos: int[]}
     */
    protected function dados(Request $request): array
    {
        $dados = $request->hasSession() ? $request->session()->get(self::SESSAO) : null;

        return [
            'curriculos' => array_map('intval', (array) ($dados['curriculos'] ?? [])),
            'arquivos' => array_map('intval', (array) ($dados['arquivos'] ?? [])),
        ];
    }
}
