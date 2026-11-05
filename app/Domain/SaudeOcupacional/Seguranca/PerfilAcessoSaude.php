<?php

namespace App\Domain\SaudeOcupacional\Seguranca;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Perfis de acesso do módulo Saúde Ocupacional.
 *
 * - RH (saude_ocupacional): status, prazos, apto/inapto, datas.
 * - Configurador (saude_ocupacional_configuracao): telas de configuração.
 * - Saúde/SESMT (saude_ocupacional_dados_sensiveis): CID, laudos, atestados, resultados.
 *
 * Regra: o dado sensível é removido no backend. Esconder só no Vue não basta.
 */
class PerfilAcessoSaude
{
    public const HABILIDADE_ACESSO = 'saude_ocupacional';

    public const HABILIDADE_CONFIGURACAO = 'saude_ocupacional_configuracao';

    public const HABILIDADE_DADOS_SENSIVEIS = 'saude_ocupacional_dados_sensiveis';

    public function podeAcessar(?User $user): bool
    {
        return $this->permite($user, self::HABILIDADE_ACESSO);
    }

    public function podeConfigurar(?User $user): bool
    {
        return $this->permite($user, self::HABILIDADE_CONFIGURACAO);
    }

    public function podeVerDadosSensiveis(?User $user): bool
    {
        return $this->permite($user, self::HABILIDADE_DADOS_SENSIVEIS);
    }

    /**
     * Remove os campos sensíveis do array quando o usuário não tem o perfil Saúde.
     *
     * @param  array<string, mixed>  $dados
     * @param  list<string>  $camposSensiveis
     * @return array<string, mixed>
     */
    public function ocultarCamposSensiveis(array $dados, array $camposSensiveis, ?User $user): array
    {
        if ($this->podeVerDadosSensiveis($user)) {
            return $dados;
        }

        foreach ($camposSensiveis as $campo) {
            unset($dados[$campo]);
        }

        return $dados;
    }

    private function permite(?User $user, string $habilidade): bool
    {
        if (!$user) {
            return false;
        }

        return Gate::forUser($user)->allows($habilidade);
    }
}
