<?php

namespace App\Domain\Whatsapp\Services;

use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Models\Cliente;
use App\Models\ClienteConfig;
use App\Models\EmpresaWhatsappConfig;
use App\Models\EmpresaWhatsappTemplate;
use App\Models\UsuarioWhatsappPreferencia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class WhatsappConfigService
{
    private const CACHE_PREFIX = 'whatsapp_config:';

    public function cacheTtlSeconds(): int
    {
        return (int) config('whatsapp_templates.cache_ttl_minutes', 15) * 60;
    }

    public function invalidateCache(int $empresaId): void
    {
        Cache::forget(self::CACHE_PREFIX . $empresaId);
    }

    /** @return array{config: ?EmpresaWhatsappConfig, templates: array<string, EmpresaWhatsappTemplate>} */
    public function getCachedBundle(int $empresaId): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . $empresaId,
            $this->cacheTtlSeconds(),
            fn () => [
                'config' => EmpresaWhatsappConfig::query()
                    ->select(['id', 'empresa_id', 'nome_exibicao', 'telefone_contato', 'endereco_completo', 'texto_assinatura', 'modulos_habilitados'])
                    ->where('empresa_id', $empresaId)
                    ->first(),
                'templates' => EmpresaWhatsappTemplate::query()
                    ->select(['id', 'empresa_id', 'tipo_mensagem', 'corpo', 'ativo'])
                    ->where('empresa_id', $empresaId)
                    ->where('ativo', true)
                    ->get()
                    ->keyBy('tipo_mensagem')
                    ->all(),
            ]
        );
    }

    public function getConfig(int $empresaId): ?EmpresaWhatsappConfig
    {
        return $this->getCachedBundle($empresaId)['config'];
    }

    public function getTemplateCorpo(int $empresaId, TipoMensagemWhatsapp $tipo): string
    {
        $bundle = $this->getCachedBundle($empresaId);
        $custom = $bundle['templates'][$tipo->value] ?? null;

        if ($custom instanceof EmpresaWhatsappTemplate && trim($custom->corpo) !== '') {
            return $custom->corpo;
        }

        return config("whatsapp_templates.templates.{$tipo->value}", '');
    }

    public function resolveContactData(int $empresaId): array
    {
        $config = $this->getConfig($empresaId);

        $nomeConfig = trim((string) ($config?->nome_exibicao ?? ''));
        $telefoneConfig = trim((string) ($config?->telefone_contato ?? ''));
        $enderecoConfig = trim((string) ($config?->endereco_completo ?? ''));

        $cliente = null;
        if ($nomeConfig === '' || $telefoneConfig === '' || $enderecoConfig === '') {
            $cliente = Cliente::withoutGlobalScopes()
                ->select([
                    'id',
                    'razao_social',
                    'tel_principal',
                    'logradouro',
                    'bairro',
                    'cep',
                    'numero',
                    'complemento',
                    'municipio',
                    'uf',
                ])
                ->find($empresaId);
        }

        return [
            'nome_exibicao' => $nomeConfig !== '' ? $nomeConfig : ($cliente?->razao_social ?: self::FALLBACK),
            'telefone_contato' => $telefoneConfig !== '' ? $telefoneConfig : ($cliente?->tel_principal ?: self::FALLBACK),
            'endereco_completo' => $enderecoConfig !== '' ? $enderecoConfig : ($cliente ? $this->montarEnderecoCliente($cliente) : self::FALLBACK),
            'texto_assinatura' => $config?->texto_assinatura,
        ];
    }

    public function saveConfig(int $empresaId, array $dados): EmpresaWhatsappConfig
    {
        $config = EmpresaWhatsappConfig::query()->updateOrCreate(
            ['empresa_id' => $empresaId],
            [
                'nome_exibicao' => $dados['nome_exibicao'] ?? null,
                'telefone_contato' => $dados['telefone_contato'] ?? null,
                'endereco_completo' => $dados['endereco_completo'] ?? null,
                'texto_assinatura' => $dados['texto_assinatura'] ?? null,
                'modulos_habilitados' => array_key_exists('modulos_habilitados', $dados)
                    ? $this->normalizarModulosHabilitados($dados['modulos_habilitados'])
                    : $this->getModulosHabilitados($empresaId),
            ]
        );

        $this->invalidateCache($empresaId);

        return $config;
    }

    public function saveTemplate(int $empresaId, TipoMensagemWhatsapp $tipo, string $corpo, bool $ativo = true): EmpresaWhatsappTemplate
    {
        $template = EmpresaWhatsappTemplate::query()->updateOrCreate(
            ['empresa_id' => $empresaId, 'tipo_mensagem' => $tipo->value],
            ['corpo' => $corpo, 'ativo' => $ativo]
        );

        $this->invalidateCache($empresaId);

        return $template;
    }

    public function restoreTemplatePadrao(int $empresaId, TipoMensagemWhatsapp $tipo): void
    {
        EmpresaWhatsappTemplate::query()
            ->where('empresa_id', $empresaId)
            ->where('tipo_mensagem', $tipo->value)
            ->delete();

        $this->invalidateCache($empresaId);
    }

    /** @return array<string, mixed> */
    public function getConfigForApi(int $empresaId): array
    {
        $config = $this->getConfig($empresaId);
        $resolved = $this->resolveContactData($empresaId);

        return [
            'nome_exibicao' => $config?->nome_exibicao,
            'telefone_contato' => $config?->telefone_contato,
            'endereco_completo' => $config?->endereco_completo,
            'texto_assinatura' => $config?->texto_assinatura,
            'modulos_habilitados' => $this->listModulosHabilitadosForApi($empresaId),
            'resolved' => $resolved,
        ];
    }

    public function saveModulosHabilitados(int $empresaId, array $modulos): EmpresaWhatsappConfig
    {
        $config = EmpresaWhatsappConfig::query()->firstOrNew(['empresa_id' => $empresaId]);
        $config->modulos_habilitados = $this->normalizarModulosHabilitados($modulos);
        $config->save();

        $this->invalidateCache($empresaId);

        return $config;
    }

    /** @return array<string, bool> */
    public function getModulosHabilitados(int $empresaId): array
    {
        $config = $this->getConfig($empresaId);
        $salvos = $config?->modulos_habilitados;

        if (!is_array($salvos) || $salvos === []) {
            return $this->modulosPadraoHabilitados();
        }

        return $this->normalizarModulosHabilitados($salvos);
    }

    public function isModuloHabilitado(int $empresaId, string $modulo): bool
    {
        $habilitados = $this->resolveModulosHabilitadosParaEnvio($empresaId);

        return (bool) ($habilitados[$modulo] ?? false);
    }

    /**
     * Status leve para o frontend (sem templates).
     *
     * @return array{
     *     whatsapp_liberado: bool,
     *     modulos: array<string, bool>,
     *     tipos: array<string, bool>
     * }
     */
    public function getStatusForApi(int $empresaId): array
    {
        $liberado = (bool) ClienteConfig::query()
            ->where('cliente_id', $empresaId)
            ->value('envia_whatsapp');

        $modulos = [];
        foreach ($this->listModulosHabilitadosForApi($empresaId) as $item) {
            $modulos[$item['modulo']] = $liberado && (bool) $item['habilitado'];
        }

        $gate = app(WhatsappNotificationGateService::class);
        $tipos = [];
        foreach (TipoMensagemWhatsapp::cases() as $tipo) {
            $tipos[$tipo->value] = $liberado && $gate->podeEnviar($tipo, $empresaId);
        }

        return [
            'whatsapp_liberado' => $liberado,
            'modulos' => $modulos,
            'tipos' => $tipos,
        ];
    }

    /** Leitura direta do banco para o gate de envio (evita cache desatualizado em workers). */
    /** @return array<string, bool> */
    public function resolveModulosHabilitadosParaEnvio(int $empresaId): array
    {
        if (!$this->empresaPossuiConfiguracaoWhatsapp($empresaId)) {
            return $this->getModulosHabilitados($empresaId);
        }

        $salvos = EmpresaWhatsappConfig::query()
            ->where('empresa_id', $empresaId)
            ->value('modulos_habilitados');

        if (!is_array($salvos) || $salvos === []) {
            return $this->modulosPadraoHabilitados();
        }

        return $this->normalizarModulosHabilitados($salvos);
    }

    private function empresaPossuiConfiguracaoWhatsapp(int $empresaId): bool
    {
        if (!Schema::hasTable('empresa_whatsapp_configs')) {
            return false;
        }

        return EmpresaWhatsappConfig::query()
            ->where('empresa_id', $empresaId)
            ->exists();
    }

    /** @return array<int, array{modulo: string, habilitado: bool, tipos: string[]}> */
    public function listModulosHabilitadosForApi(int $empresaId): array
    {
        $habilitados = $this->getModulosHabilitados($empresaId);
        $agrupados = [];

        foreach (TipoMensagemWhatsapp::cases() as $tipo) {
            $modulo = $tipo->modulo();

            if (!isset($agrupados[$modulo])) {
                $agrupados[$modulo] = [
                    'modulo' => $modulo,
                    'habilitado' => (bool) ($habilitados[$modulo] ?? false),
                    'tipos' => [],
                ];
            }

            $agrupados[$modulo]['tipos'][] = $tipo->value;
        }

        return array_values($agrupados);
    }

    /** @return array<int, array{modulo: string, receber: bool, tipos: string[]}> */
    public function listPreferenciasUsuarioForApi(int $userId, int $empresaId): array
    {
        $preferencias = UsuarioWhatsappPreferencia::query()
            ->select(['modulo', 'receber'])
            ->where('user_id', $userId)
            ->get()
            ->keyBy('modulo');

        $lista = [];

        foreach ($this->listModulosHabilitadosForApi($empresaId) as $item) {
            $modulo = $item['modulo'];
            $registro = $preferencias->get($modulo);

            $lista[] = [
                'modulo' => $modulo,
                'receber' => $registro !== null ? (bool) $registro->receber : true,
                'habilitado_empresa' => (bool) $item['habilitado'],
                'tipos' => $item['tipos'],
            ];
        }

        return $lista;
    }

    /** @param array<int, array{modulo: string, receber: bool}> $preferencias */
    public function savePreferenciasUsuario(int $userId, array $preferencias): void
    {
        foreach ($preferencias as $item) {
            $modulo = (string) ($item['modulo'] ?? '');
            if ($modulo === '' || !in_array($modulo, TipoMensagemWhatsapp::modulosLista(), true)) {
                continue;
            }

            UsuarioWhatsappPreferencia::query()->updateOrCreate(
                ['user_id' => $userId, 'modulo' => $modulo],
                ['receber' => filter_var($item['receber'] ?? true, FILTER_VALIDATE_BOOLEAN)]
            );
        }
    }

    /** @return array<string, bool> */
    private function modulosPadraoHabilitados(): array
    {
        $modulos = [];

        foreach (TipoMensagemWhatsapp::modulosLista() as $modulo) {
            $modulos[$modulo] = true;
        }

        return $modulos;
    }

    /** @return array<string, bool> */
    private function normalizarModulosHabilitados(array $modulos): array
    {
        $normalizado = $this->modulosPadraoHabilitados();

        foreach ($modulos as $chave => $valor) {
            if (is_array($valor) && array_key_exists('modulo', $valor)) {
                $modulo = (string) $valor['modulo'];
                $normalizado[$modulo] = filter_var($valor['habilitado'] ?? $valor['receber'] ?? true, FILTER_VALIDATE_BOOLEAN);
                continue;
            }

            if (is_string($chave) && in_array($chave, TipoMensagemWhatsapp::modulosLista(), true)) {
                $normalizado[$chave] = filter_var($valor, FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $normalizado;
    }

    /** @return array<int, array<string, mixed>> */
    public function listTemplatesForApi(int $empresaId): array
    {
        $custom = EmpresaWhatsappTemplate::query()
            ->where('empresa_id', $empresaId)
            ->get()
            ->keyBy('tipo_mensagem');

        $lista = [];

        foreach (TipoMensagemWhatsapp::cases() as $tipo) {
            $registro = $custom->get($tipo->value);
            $lista[] = [
                'tipo_mensagem' => $tipo->value,
                'label' => $tipo->label(),
                'modulo' => $tipo->modulo(),
                'customizado' => $registro !== null && trim($registro->corpo) !== '',
                'ativo' => $registro?->ativo ?? false,
                'corpo' => $registro?->corpo ?? config("whatsapp_templates.templates.{$tipo->value}", ''),
                'corpo_padrao' => config("whatsapp_templates.templates.{$tipo->value}", ''),
                'placeholders' => $tipo->placeholders(),
            ];
        }

        return $lista;
    }

    private function montarEnderecoCliente(object $cliente): string
    {
        $logradouro = trim((string) ($cliente->logradouro ?? ''));

        if ($logradouro === '') {
            return self::FALLBACK;
        }

        $numero = trim((string) ($cliente->numero ?? '')) ?: 'S/N';
        $complemento = trim((string) ($cliente->complemento ?? ''));
        $bairro = trim((string) ($cliente->bairro ?? ''));
        $cep = trim((string) ($cliente->cep ?? ''));
        $municipio = trim((string) ($cliente->municipio ?? ''));
        $uf = trim((string) ($cliente->uf ?? ''));

        if ($complemento !== '') {
            return "{$logradouro}, {$complemento}, {$numero}, {$bairro}, {$cep}, {$municipio}-{$uf}";
        }

        return "{$logradouro}, {$numero}, {$bairro}, {$cep}, {$municipio}-{$uf}";
    }

    private const FALLBACK = 'Não informado';
}
