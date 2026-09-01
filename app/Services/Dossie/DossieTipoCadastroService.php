<?php

namespace App\Services\Dossie;

use App\Models\Arquivo;
use App\Models\DossieTipo;
use App\Models\Sistema;
use DomainException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DossieTipoCadastroService
{
    public const ESCOPO_GLOBAL = 'global';
    public const ESCOPO_EMPRESA = 'empresa';

    /**
     * Catálogo efetivo da empresa (global + override), incluindo inativos.
     *
     * @param  array{campoBusca?: mixed, campoStatus?: mixed, campoEscopo?: mixed}  $filtros
     */
    public function listar(?int $empresaId, array $filtros, int $porPagina, int $page): LengthAwarePaginator
    {
        $porPagina = $porPagina > 0 ? $porPagina : 20;
        $page = $page > 0 ? $page : 1;

        $items = $this->catalogoEfetivo($empresaId);
        $items = $this->aplicarFiltros($items, $filtros);

        $total = $items->count();
        $slice = $items->forPage($page, $porPagina)->values();

        return new LengthAwarePaginator(
            $slice,
            $total,
            $porPagina,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    public function buscarParaEdicao(int $id, ?int $empresaId): DossieTipo
    {
        $tipo = $this->localizarAcessivel($id, $empresaId);
        $tipo->load('modeloArquivo');
        $tipo->setAttribute('modelo', $tipo->modeloArquivo ? [$tipo->modeloArquivo] : []);
        $tipo->setAttribute('modeloDel', []);
        $tipo->setAttribute('tem_modelo_sistema', $this->temModeloSistema($tipo));

        return $tipo;
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function criar(array $dados, int $empresaId): DossieTipo
    {
        return DB::transaction(function () use ($dados, $empresaId) {
            $payload = $this->normalizarPayload($dados);
            $this->preencherIdentificadoresDoLabel($payload);

            $this->assertIdentificadoresUnicos($payload['tipo'], $payload['chave'], $empresaId);
            $this->aplicarRestricaoAssinaturaDigital($payload, $empresaId, criar: true);
            $this->preencherIdentificadoresDeModelo($payload);

            $tipo = DossieTipo::create(array_merge($payload, [
                'empresa_id' => $empresaId,
            ]));

            $this->persistirModelo($tipo, $dados);
            $this->assertAssinaturaTemModelo($tipo->fresh());

            return $tipo->fresh();
        });
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function atualizar(int $id, array $dados, ?int $empresaId): DossieTipo
    {
        return DB::transaction(function () use ($id, $dados, $empresaId) {
            $atual = $this->localizarAcessivel($id, $empresaId);
            $payload = $this->normalizarPayload($dados);
            unset($payload['tipo'], $payload['chave']);

            $alvo = $this->resolverRegistroParaEscrita($atual, $empresaId);
            $payload['chave'] = $alvo->chave;
            $payload['tipo_modelo'] = $payload['tipo_modelo'] ?: $alvo->tipo_modelo;
            $payload['tipo_documento'] = $payload['tipo_documento'] ?: $alvo->tipo_documento;
            $this->preencherIdentificadoresDeModelo($payload);
            unset($payload['chave']);
            $this->aplicarRestricaoAssinaturaDigital($payload, $empresaId, criar: false);

            $alvo->fill($payload);
            $alvo->save();

            $this->persistirModelo($alvo, $dados);
            $this->assertAssinaturaTemModelo($alvo->fresh());

            return $alvo->fresh();
        });
    }

    public function alternarAtivo(int $id, ?int $empresaId): DossieTipo
    {
        $atual = $this->localizarAcessivel($id, $empresaId);
        $alvo = $this->resolverRegistroParaEscrita($atual, $empresaId);
        $alvo->ativo = !$alvo->ativo;
        $alvo->save();

        return $alvo->fresh();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function catalogoEfetivo(?int $empresaId): Collection
    {
        $rows = DossieTipo::query()
            ->where(function ($query) use ($empresaId) {
                $query->whereNull('empresa_id');
                if ($empresaId) {
                    $query->orWhere('empresa_id', $empresaId);
                }
            })
            ->orderBy('ordem')
            ->orderBy('id')
            ->get();

        $merged = [];
        foreach ($rows->whereNull('empresa_id') as $row) {
            $merged[$row->tipo] = $this->mapearItem($row);
        }

        if ($empresaId) {
            foreach ($rows->where('empresa_id', $empresaId) as $row) {
                $merged[$row->tipo] = $this->mapearItem($row);
            }
        }

        return collect($merged)
            ->sortBy([
                ['ordem', 'asc'],
                ['id', 'asc'],
            ])
            ->values();
    }

    public function localizarAcessivel(int $id, ?int $empresaId): DossieTipo
    {
        $tipo = DossieTipo::query()->find($id);
        if (!$tipo) {
            throw new DomainException('Tipo de dossiê não encontrado.');
        }

        if ($tipo->empresa_id === null) {
            return $tipo;
        }

        if ($empresaId && (int) $tipo->empresa_id === $empresaId) {
            return $tipo;
        }

        throw new DomainException('Tipo de dossiê não encontrado.');
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @param  array<string, mixed>  $filtros
     * @return Collection<int, array<string, mixed>>
     */
    protected function aplicarFiltros(Collection $items, array $filtros): Collection
    {
        if (!empty($filtros['campoBusca'])) {
            $termo = mb_strtolower(trim((string) $filtros['campoBusca']));
            $items = $items->filter(function (array $item) use ($termo) {
                if (is_numeric($termo) && (int) $item['id'] === (int) $termo) {
                    return true;
                }

                $haystack = mb_strtolower(implode(' ', [
                    $item['label'] ?? '',
                    $item['tipo'] ?? '',
                    $item['chave'] ?? '',
                ]));

                return str_contains($haystack, $termo);
            });
        }

        if (isset($filtros['campoStatus']) && $filtros['campoStatus'] !== '' && $filtros['campoStatus'] !== null) {
            $status = filter_var($filtros['campoStatus'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($status !== null) {
                $items = $items->filter(fn (array $item) => (bool) $item['ativo'] === $status);
            }
        }

        if (!empty($filtros['campoEscopo'])) {
            $escopo = (string) $filtros['campoEscopo'];
            if ($escopo === self::ESCOPO_GLOBAL || $escopo === self::ESCOPO_EMPRESA) {
                $items = $items->filter(fn (array $item) => ($item['escopo'] ?? '') === $escopo);
            }
        }

        return $items->values();
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapearItem(DossieTipo $tipo): array
    {
        $escopo = $tipo->empresa_id ? self::ESCOPO_EMPRESA : self::ESCOPO_GLOBAL;
        $temArquivo = (bool) $tipo->modelo_arquivo_id;
        $temSistema = $this->temModeloSistema($tipo);

        return [
            'id' => $tipo->id,
            'empresa_id' => $tipo->empresa_id,
            'tipo' => $tipo->tipo,
            'chave' => $tipo->chave,
            'label' => $tipo->label,
            'tipo_modelo' => $tipo->tipo_modelo,
            'tipo_documento' => $tipo->tipo_documento,
            'tem_modelo' => $temArquivo || $temSistema,
            'tem_arquivo_modelo' => $temArquivo,
            'tem_modelo_sistema' => $temSistema,
            'permite_assinatura' => (bool) $tipo->permite_assinatura,
            'ordem' => (int) $tipo->ordem,
            'ativo' => (bool) $tipo->ativo,
            'escopo' => $escopo,
            'escopo_label' => $escopo === self::ESCOPO_EMPRESA ? 'Da empresa' : 'Padrão do sistema',
            'modelo_origem_label' => $temArquivo ? 'Arquivo cadastrado' : ($temSistema ? 'Modelo do sistema' : 'Não informado'),
        ];
    }

    protected function resolverRegistroParaEscrita(DossieTipo $atual, ?int $empresaId): DossieTipo
    {
        if ($empresaId === null) {
            return $atual;
        }

        if ((int) $atual->empresa_id === $empresaId) {
            return $atual;
        }

        if ($atual->empresa_id !== null) {
            throw new DomainException('Tipo de dossiê não encontrado.');
        }

        $existente = DossieTipo::query()
            ->where('empresa_id', $empresaId)
            ->where('tipo', $atual->tipo)
            ->first();

        if ($existente) {
            return $existente;
        }

        $copia = $atual->replicate();
        $copia->empresa_id = $empresaId;
        $copia->save();

        return $copia;
    }

    /**
     * @param  array<string, mixed>  $dados
     * @return array<string, mixed>
     */
    public function normalizarPayload(array $dados): array
    {
        $payload = [
            'label' => trim((string) ($dados['label'] ?? '')),
            'tipo_modelo' => $this->nuloSeVazio($dados['tipo_modelo'] ?? null),
            'tipo_documento' => $this->nuloSeVazio($dados['tipo_documento'] ?? null),
            'tem_modelo' => $this->normalizarBoolean($dados['tem_modelo'] ?? false),
            'permite_assinatura' => $this->normalizarBoolean($dados['permite_assinatura'] ?? false),
            'ordem' => (int) ($dados['ordem'] ?? 0),
            'ativo' => $this->normalizarBoolean($dados['ativo'] ?? true),
        ];

        return $payload;
    }

    /**
     * tipo (StudlyCase) e chave (snake_case) nascem do nome. O cliente não informa.
     *
     * @param  array<string, mixed>  $payload
     */
    public function preencherIdentificadoresDoLabel(array &$payload): void
    {
        $chave = $this->gerarChaveDoLabel((string) ($payload['label'] ?? ''));
        $tipo = $this->gerarTipoDaChave($chave);

        if ($chave === '' || $tipo === '') {
            throw new DomainException('Informe um nome válido para o tipo de dossiê.');
        }

        if (strlen($chave) > 100 || strlen($tipo) > 100) {
            throw new DomainException('O nome é muito longo para gerar o identificador. Use um nome mais curto.');
        }

        $payload['chave'] = $chave;
        $payload['tipo'] = $tipo;
    }

    public function gerarChaveDoLabel(string $label): string
    {
        $ascii = Str::ascii(trim($label));
        $chave = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '_', $ascii));

        return trim($chave, '_');
    }

    public function gerarTipoDaChave(string $chave): string
    {
        return Str::studly($chave);
    }

    /**
     * Sem assinatura digital na empresa: no cadastro grava false; na edição não altera o campo.
     *
     * @param  array<string, mixed>  $payload
     */
    public function aplicarRestricaoAssinaturaDigital(array &$payload, ?int $empresaId, bool $criar): void
    {
        if (Sistema::assinaturaDigitalHabilitada($empresaId)) {
            return;
        }

        if ($criar) {
            $payload['permite_assinatura'] = false;

            return;
        }

        unset($payload['permite_assinatura']);
    }

    public function assertIdentificadoresUnicos(string $tipo, string $chave, int $empresaId, ?int $ignorarId = null): void
    {
        $jaTemTipo = DossieTipo::query()
            ->where('tipo', $tipo)
            ->where(function ($query) use ($empresaId) {
                $query->whereNull('empresa_id')->orWhere('empresa_id', $empresaId);
            })
            ->when($ignorarId, fn ($query) => $query->where('id', '!=', $ignorarId))
            ->exists();

        if ($jaTemTipo) {
            throw new DomainException('Já existe um tipo de dossiê com este nome.');
        }

        $jaTemChave = DossieTipo::query()
            ->where('chave', $chave)
            ->where(function ($query) use ($empresaId) {
                $query->whereNull('empresa_id')->orWhere('empresa_id', $empresaId);
            })
            ->when($ignorarId, fn ($query) => $query->where('id', '!=', $ignorarId))
            ->exists();

        if ($jaTemChave) {
            throw new DomainException('Já existe um tipo de dossiê com este nome.');
        }
    }

    public function normalizarBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function persistirModelo(DossieTipo $tipo, array $dados): void
    {
        $removidos = $dados['modeloDel'] ?? [];
        if (!is_array($removidos)) {
            $removidos = [];
        }

        foreach ($removidos as $idRemovido) {
            $idRemovido = (int) $idRemovido;
            if ($idRemovido && (int) $tipo->modelo_arquivo_id === $idRemovido) {
                $tipo->modelo_arquivo_id = null;
                $arquivo = Arquivo::find($idRemovido);
                $arquivo?->excluir();
            }
        }

        $anexos = $dados['modelo'] ?? [];
        if (is_array($anexos) && $anexos !== []) {
            $anexo = $anexos[0];
            $arquivoId = (int) ($anexo['id'] ?? 0);
            if ($arquivoId) {
                $arquivo = Arquivo::query()->where('id', $arquivoId)->first();
                if ($arquivo) {
                    $arquivo->temporario = false;
                    $arquivo->chave = '';
                    if (!empty($anexo['nome'])) {
                        $arquivo->nome = $anexo['nome'];
                    }
                    $arquivo->save();
                    $tipo->modelo_arquivo_id = $arquivo->id;
                }
            }
        }

        $tipo->tem_modelo = (bool) $tipo->modelo_arquivo_id || $this->temModeloSistema($tipo);
        $tipo->save();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function preencherIdentificadoresDeModelo(array &$payload): void
    {
        $chave = (string) ($payload['chave'] ?? '');
        if ($chave === '') {
            return;
        }

        if (empty($payload['tipo_modelo'])) {
            $payload['tipo_modelo'] = str_replace('_', '', $chave);
        }

        if (!empty($payload['permite_assinatura']) && empty($payload['tipo_documento'])) {
            $payload['tipo_documento'] = $chave;
        }
    }

    public function temModeloSistema(DossieTipo $tipo): bool
    {
        if (!$tipo->tipo_modelo) {
            return false;
        }

        if ((bool) $tipo->modelo_arquivo_id) {
            return false;
        }

        return view()->exists('pdf.historico.dossie.' . $tipo->tipo_modelo)
            || view()->exists('pdf.historico.dossie.default.contratos.' . $tipo->tipo_modelo);
    }

    public function assertAssinaturaTemModelo(DossieTipo $tipo): void
    {
        if (!$tipo->permite_assinatura) {
            return;
        }

        if ($tipo->modelo_arquivo_id || $this->temModeloSistema($tipo)) {
            return;
        }

        throw new DomainException('Para enviar à assinatura digital, cadastre um PDF de modelo.');
    }

    protected function nuloSeVazio(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $texto = trim((string) $valor);

        return $texto === '' ? null : $texto;
    }
}
