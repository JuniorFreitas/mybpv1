<?php

namespace App\Support\ApiExterna;

final class ApiExternaScopes
{
    public const CLOUD_READ = 'cloud:read';
    public const FORNECEDORES_READ = 'fornecedores:read';
    public const AVALIACOES_READ = 'avaliacoes:read';
    public const PROCESSOS_READ = 'processos:read';
    public const CURRICULOS_READ = 'curriculos:read';
    public const FLUXO_READ = 'fluxo:read';
    public const VAGAS_READ = 'vagas:read';
    public const CENTROS_CUSTO_READ = 'centros-custo:read';
    public const CIH_READ = 'cih:read';
    public const MEDIDAS_READ = 'medidas:read';
    public const HISTORICO_READ = 'historico:read';
    public const POS_ADMISSAO_READ = 'pos-admissao:read';
    public const WEEKLY_READ = 'weekly:read';
    public const RELATORIOS_READ = 'relatorios:read';

    public static function all(): array
    {
        return [
            self::CLOUD_READ,
            self::FORNECEDORES_READ,
            self::AVALIACOES_READ,
            self::PROCESSOS_READ,
            self::CURRICULOS_READ,
            self::FLUXO_READ,
            self::VAGAS_READ,
            self::CENTROS_CUSTO_READ,
            self::CIH_READ,
            self::MEDIDAS_READ,
            self::HISTORICO_READ,
            self::POS_ADMISSAO_READ,
            self::WEEKLY_READ,
            self::RELATORIOS_READ,
        ];
    }
}
