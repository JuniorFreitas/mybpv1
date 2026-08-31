<?php

namespace App\Services\TransferenciaPrevista;

use App\Models\AprovacaoExtraConfig;
use App\Models\CentroCusto;
use App\Models\TransferenciaPrevista;
use App\Models\User;
use App\Services\CentroCusto\CentroCustoGestorResolverService;
use DomainException;

class TransferenciaPrevistaFluxoAprovacaoService
{
    public const ETAPA_GESTOR_ORIGEM = 'gestor_origem';
    public const ETAPA_GESTOR_DESTINO = 'gestor_destino';
    public const ETAPA_EXTRA = 'extra';
    public const ETAPA_RH = 'rh';
    public const ETAPA_CONCLUIDO = 'concluido';
    public const ETAPA_REPROVADO = 'reprovado';

    public const MSG_GESTOR_DESTINO_AUSENTE = 'O centro de custo de destino não possui gestor responsável cadastrado. Não é possível salvar a solicitação. Entre em contato com o Administrador para cadastrar o gestor de destino.';

    public function __construct(
        private readonly CentroCustoGestorResolverService $gestorResolver
    ) {
    }

    /**
     * @throws DomainException
     */
    public function validarCentrosCusto(?int $origemId, int $destinoId, int $empresaId): void
    {
        if ($origemId !== null) {
            $this->buscarCentroCusto($origemId, $empresaId, 'origem');
        }

        $destino = $this->buscarCentroCusto($destinoId, $empresaId, 'destino');
        if (!$this->gestorResolver->getGestorPrincipal($destino->id)) {
            throw new DomainException(self::MSG_GESTOR_DESTINO_AUSENTE);
        }
    }

    /**
     * Centro de custo origem sem gestor não bloqueia a solicitação.
     *
     * @throws DomainException quando existe gestor mas não há aprovador válido (autoaprovação)
     */
    public function resolverGestorOrigem(?int $origemId, int $solicitanteId): ?User
    {
        if ($origemId === null) {
            return null;
        }

        if (!$this->gestorResolver->getGestorPrincipal($origemId)) {
            return null;
        }

        return $this->gestorResolver->resolverAprovador($origemId, $solicitanteId);
    }

    /**
     * @throws DomainException
     */
    public function resolverGestorDestino(int $destinoId, int $solicitanteId): User
    {
        if (!$this->gestorResolver->getGestorPrincipal($destinoId)) {
            throw new DomainException(self::MSG_GESTOR_DESTINO_AUSENTE);
        }

        return $this->gestorResolver->resolverAprovador($destinoId, $solicitanteId);
    }

    public function deveExigirAprovacaoGestorDestino(?User $gestorOrigem, User $gestorDestino): bool
    {
        if ($gestorOrigem === null) {
            return true;
        }

        return (int) $gestorOrigem->id !== (int) $gestorDestino->id;
    }

    /**
     * @return array<string, mixed>
     * @throws DomainException
     */
    public function montarDadosFluxoGestores(?int $origemId, int $destinoId, int $solicitanteId, int $empresaId): array
    {
        $this->validarCentrosCusto($origemId, $destinoId, $empresaId);

        $gestorOrigem = $this->resolverGestorOrigem($origemId, $solicitanteId);
        $gestorDestino = $this->resolverGestorDestino($destinoId, $solicitanteId);
        $exigeDestino = $this->deveExigirAprovacaoGestorDestino($gestorOrigem, $gestorDestino);

        return [
            'gestor_id' => $gestorOrigem?->id,
            'gestor_destino_id' => $exigeDestino ? $gestorDestino->id : null,
            'exige_aprovacao_gestor_destino' => $exigeDestino,
            'fluxo_gestores_automatico' => true,
            'status_aprovacao' => null,
            'user_aprovacao_id' => null,
            'data_aprovacao' => null,
            'obs_aprovacao' => null,
            'status_aprovacao_gestor_destino' => null,
            'user_aprovacao_gestor_destino_id' => null,
            'data_aprovacao_gestor_destino' => null,
            'obs_aprovacao_gestor_destino' => null,
        ];
    }

    public function aplicarFluxoGestores(TransferenciaPrevista $transferencia, int $solicitanteId, bool $resetarEtapas = true): void
    {
        $dados = $this->montarDadosFluxoGestores(
            $transferencia->centro_custo_origem_id,
            (int) $transferencia->centro_custo_destino_id,
            $solicitanteId,
            (int) $transferencia->empresa_id
        );

        if (!$resetarEtapas) {
            unset(
                $dados['status_aprovacao'],
                $dados['user_aprovacao_id'],
                $dados['data_aprovacao'],
                $dados['obs_aprovacao'],
                $dados['status_aprovacao_gestor_destino'],
                $dados['user_aprovacao_gestor_destino_id'],
                $dados['data_aprovacao_gestor_destino'],
                $dados['obs_aprovacao_gestor_destino']
            );
        }

        $transferencia->fill($dados);
    }

    public function etapaAtual(TransferenciaPrevista $transferencia): string
    {
        if ($transferencia->status_aprovacao === 'reprovado'
            || $transferencia->status_aprovacao_gestor_destino === 'reprovado'
            || $transferencia->status_aprovacao_extra === 'reprovado'
            || $transferencia->resposta_rh === 'reprovado') {
            return self::ETAPA_REPROVADO;
        }

        if ($transferencia->resposta_rh === 'aprovado') {
            return self::ETAPA_CONCLUIDO;
        }

        if (!$transferencia->status_aprovacao && $this->exigeAprovacaoGestorOrigem($transferencia)) {
            return self::ETAPA_GESTOR_ORIGEM;
        }

        if ($transferencia->exige_aprovacao_gestor_destino && !$transferencia->status_aprovacao_gestor_destino) {
            return self::ETAPA_GESTOR_DESTINO;
        }

        $config = AprovacaoExtraConfig::getConfigAtiva((int) $transferencia->empresa_id, 'transferencia');
        if ($config && !$transferencia->status_aprovacao_extra) {
            return self::ETAPA_EXTRA;
        }

        if (!$transferencia->resposta_rh) {
            return self::ETAPA_RH;
        }

        return self::ETAPA_CONCLUIDO;
    }

    public function podeAprovarGestorOrigem(TransferenciaPrevista $transferencia, User $user): bool
    {
        if ($this->etapaAtual($transferencia) !== self::ETAPA_GESTOR_ORIGEM) {
            return false;
        }

        if (!$transferencia->gestor_id) {
            return false;
        }

        if ($this->usuarioTemPrivilegioRh($user)) {
            return true;
        }

        return (int) $transferencia->gestor_id === (int) $user->id;
    }

    public function podeAprovarGestorDestino(TransferenciaPrevista $transferencia, User $user): bool
    {
        if ($this->etapaAtual($transferencia) !== self::ETAPA_GESTOR_DESTINO) {
            return false;
        }

        if (!$transferencia->gestor_destino_id) {
            return false;
        }

        if ($this->usuarioTemPrivilegioRh($user)) {
            return true;
        }

        return (int) $transferencia->gestor_destino_id === (int) $user->id;
    }

    public function usuarioTemPrivilegioRh(User $user): bool
    {
        return $user->can('privilegio_gestao_rh')
            || $user->can('privilegio_aprovar_por_rh')
            || $user->can('privilegio_aprovar_rh');
    }

    public function aprovacaoGestorViaRh(TransferenciaPrevista $transferencia, User $aprovador, string $etapa): bool
    {
        if (!$this->usuarioTemPrivilegioRh($aprovador)) {
            return false;
        }

        $gestorId = $etapa === self::ETAPA_GESTOR_DESTINO
            ? $transferencia->gestor_destino_id
            : $transferencia->gestor_id;

        return (int) $aprovador->id !== (int) $gestorId;
    }

    public function anexarRegistroAprovadorRh(?string $observacao, User $aprovador): string
    {
        $registro = 'Registrado por RH: ' . trim((string) $aprovador->nome);
        $observacao = trim((string) $observacao);

        if ($observacao === '') {
            return $registro;
        }

        if (str_contains($observacao, $registro)) {
            return $observacao;
        }

        return $observacao . "\n" . $registro;
    }

    public function exigeAprovacaoGestorOrigem(TransferenciaPrevista $transferencia): bool
    {
        if ($this->isFluxoLegado($transferencia)) {
            return true;
        }

        return (int) ($transferencia->gestor_id ?? 0) > 0;
    }

    public function origemEtapaConcluida(TransferenciaPrevista $transferencia): bool
    {
        if (!$this->exigeAprovacaoGestorOrigem($transferencia)) {
            return true;
        }

        return $transferencia->status_aprovacao === 'aprovado';
    }

    public function gestoresEtapasConcluidas(TransferenciaPrevista $transferencia): bool
    {
        if (!$this->origemEtapaConcluida($transferencia)) {
            return false;
        }

        if ($transferencia->exige_aprovacao_gestor_destino) {
            return $transferencia->status_aprovacao_gestor_destino === 'aprovado';
        }

        return true;
    }

    public function isFluxoLegado(TransferenciaPrevista $transferencia): bool
    {
        return !(bool) $transferencia->fluxo_gestores_automatico;
    }

    public function exigirCentroCustoAtivo(string $tipo): bool
    {
        return $tipo !== 'origem';
    }

    /**
     * A tela de transferência envia `resposta_rh`. `status_aprovacao_rh` é legado/outros módulos.
     */
    public function decisaoAprovacaoRh(array $dados): ?string
    {
        $decisao = $dados['resposta_rh'] ?? $dados['status_aprovacao_rh'] ?? null;

        if (!is_string($decisao) || $decisao === '') {
            return null;
        }

        return $decisao;
    }

    public function mensagemHistoricoAprovacaoRh(TransferenciaPrevista $transferencia, ?string $decisaoRh): string
    {
        $origem = $transferencia->CentroCustoOrigem?->label ?: 'não informado';
        $destino = $transferencia->CentroCustoDestino?->label ?: 'não informado';

        return 'Solicitação foi ' . $decisaoRh
            . ' pelo RH na mudança de Centro de Custo de ' . $origem
            . ' para ' . $destino
            . ' na solicitação de transferência #' . $transferencia->id;
    }

    /**
     * @throws DomainException
     */
    private function buscarCentroCusto(int $centroCustoId, int $empresaId, string $tipo): CentroCusto
    {
        $centro = CentroCusto::query()
            ->where('id', $centroCustoId)
            ->where('empresa_id', $empresaId)
            ->first();

        if (!$centro) {
            throw new DomainException("Centro de custo de {$tipo} inválido ou não encontrado.");
        }

        if ($this->exigirCentroCustoAtivo($tipo) && !$centro->ativo) {
            throw new DomainException("O centro de custo de {$tipo} está inativo.");
        }

        return $centro;
    }
}
