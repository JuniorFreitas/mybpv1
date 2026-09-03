<?php

namespace App\Services\Preadmissao;

use App\Models\DocumentosCurriculosAdmissaoEmpresa;
use App\Models\DocumentosCurriculosCatAdmissaoEmpresa;
use App\Support\DocumentoPreadmissaoDescricaoSanitizer;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DocumentoPreadmissaoCadastroService
{
    public const TIPO_FOTO_3X4 = 'foto3x4';
    public const MSG_PADRAO_SISTEMA = 'A foto 3x4 é um documento padrão do sistema e não pode ser alterada.';
    public const MSG_PADRAO_SISTEMA_CADASTRO = 'A foto 3x4 é um documento padrão do sistema e não pode ser cadastrada manualmente.';

    /**
     * @param  array{campoBusca?: mixed, campoStatus?: mixed, campoCategoria?: mixed}  $filtros
     */
    public function listar(int $empresaId, array $filtros, int $porPagina, int $page): LengthAwarePaginator
    {
        $porPagina = $porPagina > 0 ? min($porPagina, 100) : 20;
        $page = $page > 0 ? $page : 1;

        $this->garantirPadraoSistema($empresaId);

        $query = DocumentosCurriculosAdmissaoEmpresa::query()
            ->with('categoria')
            ->where('empresa_id', $empresaId);

        $this->aplicarFiltros($query, $filtros);

        return $query
            ->orderBy('categoria_id')
            ->orderBy('ordem')
            ->orderBy('label')
            ->paginate($porPagina, ['*'], 'page', $page)
            ->through(function (DocumentosCurriculosAdmissaoEmpresa $doc) {
                $doc->setAttribute('categoria_label', $doc->categoria?->label);
                $doc->setAttribute('padrao_sistema', $this->ePadraoSistema($doc->tipo));

                return $doc;
            });
    }

    public function listarCategorias(int $empresaId): Collection
    {
        return DocumentosCurriculosCatAdmissaoEmpresa::query()
            ->where('empresa_id', $empresaId)
            ->orderBy('label')
            ->get(['id', 'label', 'ativo']);
    }

    public function buscarParaEdicao(int $id, int $empresaId): DocumentosCurriculosAdmissaoEmpresa
    {
        $doc = $this->localizarDaEmpresa($id, $empresaId);
        $doc->load('categoria');
        $doc->setAttribute('categoria_label', $doc->categoria?->label);
        $doc->setAttribute('categoria_nova', '');

        return $doc;
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function criar(array $dados, int $empresaId): DocumentosCurriculosAdmissaoEmpresa
    {
        try {
            return DB::transaction(function () use ($dados, $empresaId) {
                $payload = $this->normalizarPayload($dados);
                $this->preencherIdentificadoresDoLabel($payload);
                $this->assertNaoPadraoSistemaCadastro($payload['tipo']);
                $this->assertTipoUnico($payload['tipo'], $empresaId);
                $payload['categoria_id'] = $this->resolverCategoriaId($dados, $empresaId);
                unset($payload['categoria_nova']);

                $doc = DocumentosCurriculosAdmissaoEmpresa::create(array_merge($payload, [
                    'empresa_id' => $empresaId,
                ]));

                DocumentosCurriculosAdmissaoEmpresa::limparCache($empresaId);

                return $doc->fresh('categoria');
            });
        } catch (QueryException $e) {
            if ($this->eViolacaoTipoUnico($e)) {
                throw new DomainException('Já existe um documento com este nome.', previous: $e);
            }

            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function atualizar(int $id, array $dados, int $empresaId): DocumentosCurriculosAdmissaoEmpresa
    {
        return DB::transaction(function () use ($id, $dados, $empresaId) {
            $doc = $this->localizarDaEmpresa($id, $empresaId);
            $this->assertNaoPadraoSistema($doc);
            $payload = $this->normalizarPayload($dados);
            unset($payload['tipo'], $payload['metodo']);
            $payload['categoria_id'] = $this->resolverCategoriaId($dados, $empresaId);
            unset($payload['categoria_nova']);

            $doc->fill($payload);
            $doc->save();

            DocumentosCurriculosAdmissaoEmpresa::limparCache($empresaId);

            return $doc->fresh('categoria');
        });
    }

    public function alternarAtivo(int $id, int $empresaId): DocumentosCurriculosAdmissaoEmpresa
    {
        $doc = $this->localizarDaEmpresa($id, $empresaId);
        $this->assertNaoPadraoSistema($doc);
        $doc->ativo = !$doc->ativo;
        $doc->save();

        DocumentosCurriculosAdmissaoEmpresa::limparCache($empresaId);

        return $doc->fresh();
    }

    public function gerarChaveDoLabel(string $label): string
    {
        $ascii = Str::ascii(trim($label));
        $chave = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '_', $ascii));

        return trim($chave, '_');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function preencherIdentificadoresDoLabel(array &$payload): void
    {
        $tipo = $this->gerarChaveDoLabel((string) ($payload['label'] ?? ''));
        $metodo = Str::studly($tipo);

        if ($tipo === '' || $metodo === '') {
            throw new DomainException('Informe um nome válido para o documento.');
        }

        if (strlen($tipo) > 100 || strlen($metodo) > 100) {
            throw new DomainException('O nome é muito longo para gerar o identificador. Use um nome mais curto.');
        }

        $payload['tipo'] = $tipo;
        $payload['metodo'] = $metodo;
    }

    public function garantirPadraoSistema(int $empresaId): void
    {
        $existente = DocumentosCurriculosAdmissaoEmpresa::query()
            ->where('empresa_id', $empresaId)
            ->where('tipo', self::TIPO_FOTO_3X4)
            ->first();

        if ($existente) {
            if (!$existente->ativo) {
                $existente->ativo = true;
                $existente->save();
                DocumentosCurriculosAdmissaoEmpresa::limparCache($empresaId);
            }

            return;
        }

        $varianteLegada = DocumentosCurriculosAdmissaoEmpresa::query()
            ->where('empresa_id', $empresaId)
            ->get()
            ->first(fn (DocumentosCurriculosAdmissaoEmpresa $documento) => $this->ePadraoSistema($documento->tipo));

        if ($varianteLegada) {
            if (!$varianteLegada->ativo) {
                $varianteLegada->ativo = true;
                $varianteLegada->save();
                DocumentosCurriculosAdmissaoEmpresa::limparCache($empresaId);
            }

            return;
        }

        $categoriaId = $this->resolverCategoriaPadraoFoto($empresaId);

        try {
            DocumentosCurriculosAdmissaoEmpresa::create([
                'empresa_id' => $empresaId,
                'categoria_id' => $categoriaId,
                'label' => 'FOTO 3X4',
                'metodo' => 'FotoTres',
                'descricao' => 'Obs.: Somente imagens no formato JPG, JPEG, PNG',
                'tipo' => self::TIPO_FOTO_3X4,
                'ordem' => 1,
                'ativo' => true,
                'configuracoes' => [
                    'obrigatorio' => true,
                    'apenas_img' => true,
                    'apenas_pdf' => false,
                    'apenas_pdf_img' => false,
                    'multiple' => false,
                    'min' => 1,
                    'max' => 1,
                    'sogestao' => false,
                ],
            ]);
        } catch (QueryException $e) {
            if (!$this->eViolacaoTipoUnico($e)) {
                throw $e;
            }
        }

        DocumentosCurriculosAdmissaoEmpresa::limparCache($empresaId);
    }

    public function ePadraoSistema(?string $tipo): bool
    {
        $normalizado = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '', Str::ascii((string) $tipo)));

        return $normalizado === self::TIPO_FOTO_3X4;
    }

    private function resolverCategoriaPadraoFoto(int $empresaId): int
    {
        $pessoais = DocumentosCurriculosCatAdmissaoEmpresa::query()
            ->where('empresa_id', $empresaId)
            ->where('label', 'DOCUMENTOS PESSOAIS')
            ->first();

        if ($pessoais) {
            return (int) $pessoais->id;
        }

        $primeira = DocumentosCurriculosCatAdmissaoEmpresa::query()
            ->where('empresa_id', $empresaId)
            ->orderBy('id')
            ->first();

        if ($primeira) {
            return (int) $primeira->id;
        }

        DocumentosCurriculosCatAdmissaoEmpresa::query()->insertOrIgnore([
            'empresa_id' => $empresaId,
            'label' => 'DOCUMENTOS PESSOAIS',
            'ativo' => true,
        ]);

        $categoria = DocumentosCurriculosCatAdmissaoEmpresa::query()
            ->where('empresa_id', $empresaId)
            ->where('label', 'DOCUMENTOS PESSOAIS')
            ->first();

        if (!$categoria) {
            throw new DomainException('Não foi possível garantir a categoria padrão da foto 3x4.');
        }

        return (int) $categoria->id;
    }

    private function assertNaoPadraoSistema(DocumentosCurriculosAdmissaoEmpresa $doc): void
    {
        if ($this->ePadraoSistema($doc->tipo)) {
            throw new DomainException(self::MSG_PADRAO_SISTEMA);
        }
    }

    private function assertNaoPadraoSistemaCadastro(string $tipo): void
    {
        if ($this->ePadraoSistema($tipo)) {
            throw new DomainException(self::MSG_PADRAO_SISTEMA_CADASTRO);
        }
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    private function aplicarFiltros(Builder $query, array $filtros): void
    {
        if (filled($filtros['campoBusca'] ?? null)) {
            $termo = trim((string) $filtros['campoBusca']);
            $query->where(function (Builder $q) use ($termo) {
                $q->where('label', 'like', '%' . $termo . '%')
                    ->orWhere('tipo', 'like', '%' . $termo . '%');
                if (is_numeric($termo)) {
                    $q->orWhere('id', (int) $termo);
                }
            });
        }

        if (filled($filtros['campoStatus'] ?? null)) {
            $status = filter_var($filtros['campoStatus'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($status !== null) {
                $query->whereAtivo($status);
            }
        }

        if (filled($filtros['campoCategoria'] ?? null) && is_numeric($filtros['campoCategoria'])) {
            $query->where('categoria_id', (int) $filtros['campoCategoria']);
        }
    }

    /**
     * @param  array<string, mixed>  $dados
     * @return array<string, mixed>
     */
    private function normalizarPayload(array $dados): array
    {
        return [
            'label' => trim((string) ($dados['label'] ?? '')),
            'descricao' => $this->sanitizarDescricao($dados['descricao'] ?? null),
            'ordem' => (int) ($dados['ordem'] ?? 0),
            'ativo' => $this->normalizarBoolean($dados['ativo'] ?? true),
            'configuracoes' => $this->normalizarConfiguracoes($dados['configuracoes'] ?? []),
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function normalizarConfiguracoes($config): array
    {
        if (!is_array($config)) {
            $config = [];
        }

        $apenasImg = $this->normalizarBoolean($config['apenas_img'] ?? false);
        $apenasPdf = $this->normalizarBoolean($config['apenas_pdf'] ?? false);
        $apenasPdfImg = $this->normalizarBoolean($config['apenas_pdf_img'] ?? false);

        if (((int) $apenasImg + (int) $apenasPdf + (int) $apenasPdfImg) > 1) {
            throw new DomainException('Escolha apenas um tipo de arquivo aceito.');
        }

        $multiple = $this->normalizarBoolean($config['multiple'] ?? false);
        $min = max(1, (int) ($config['min'] ?? 1));
        $max = (int) ($config['max'] ?? ($multiple ? $min : 1));
        if (!$multiple) {
            $max = $min;
        }
        if ($max < $min) {
            throw new DomainException('A quantidade máxima não pode ser menor que a mínima.');
        }

        return [
            'obrigatorio' => $this->normalizarBoolean($config['obrigatorio'] ?? false),
            'apenas_img' => $apenasImg,
            'apenas_pdf' => $apenasPdf,
            'apenas_pdf_img' => $apenasPdfImg,
            'multiple' => $multiple,
            'min' => $min,
            'max' => $max,
            'sogestao' => $this->normalizarBoolean($config['sogestao'] ?? false),
        ];
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function resolverCategoriaId(array $dados, int $empresaId): int
    {
        $nova = trim((string) ($dados['categoria_nova'] ?? ''));
        if ($nova !== '') {
            $existente = DocumentosCurriculosCatAdmissaoEmpresa::query()
                ->where('empresa_id', $empresaId)
                ->whereRaw('LOWER(label) = ?', [mb_strtolower($nova)])
                ->first();

            if ($existente) {
                return (int) $existente->id;
            }

            DocumentosCurriculosCatAdmissaoEmpresa::query()->insertOrIgnore([
                'empresa_id' => $empresaId,
                'label' => $nova,
                'ativo' => true,
            ]);

            $categoria = DocumentosCurriculosCatAdmissaoEmpresa::query()
                ->where('empresa_id', $empresaId)
                ->whereRaw('LOWER(label) = ?', [mb_strtolower($nova)])
                ->first();

            if (!$categoria) {
                throw new DomainException('Não foi possível cadastrar a categoria.');
            }

            return (int) $categoria->id;
        }

        $id = (int) ($dados['categoria_id'] ?? 0);
        if ($id < 1) {
            throw new DomainException('Informe ou cadastre uma categoria.');
        }

        $categoria = DocumentosCurriculosCatAdmissaoEmpresa::query()
            ->where('empresa_id', $empresaId)
            ->whereKey($id)
            ->first();

        if (!$categoria) {
            throw new DomainException('Categoria não encontrada.');
        }

        return (int) $categoria->id;
    }

    private function assertTipoUnico(string $tipo, int $empresaId, ?int $ignorarId = null): void
    {
        $query = DocumentosCurriculosAdmissaoEmpresa::query()
            ->where('empresa_id', $empresaId)
            ->where('tipo', $tipo);

        if ($ignorarId) {
            $query->where('id', '<>', $ignorarId);
        }

        if ($query->exists()) {
            throw new DomainException('Já existe um documento com este nome.');
        }
    }

    private function localizarDaEmpresa(int $id, int $empresaId): DocumentosCurriculosAdmissaoEmpresa
    {
        $doc = DocumentosCurriculosAdmissaoEmpresa::query()
            ->where('empresa_id', $empresaId)
            ->whereKey($id)
            ->first();

        if (!$doc) {
            throw new DomainException('Documento não encontrado.');
        }

        return $doc;
    }

    private function sanitizarDescricao(mixed $html): ?string
    {
        return DocumentoPreadmissaoDescricaoSanitizer::sanitize($html);
    }

    private function eViolacaoTipoUnico(QueryException $e): bool
    {
        $mensagem = strtolower($e->getMessage());

        return str_contains($mensagem, 'doc_adm_empresa_tipo_unique')
            || str_contains($mensagem, 'documentos_curriculos_adm_empresa.empresa_id, documentos_curriculos_adm_empresa.tipo');
    }

    private function normalizarBoolean(mixed $valor): bool
    {
        return filter_var($valor, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $valor;
    }
}
