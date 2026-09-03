<?php

namespace App\Services\TransferenciaPrevista;

use App\Models\AprovacaoExtraConfig;
use App\Models\CentroCusto;
use App\Models\GestorAprovacaoConfig;
use App\Models\TransferenciaPrevista;
use App\Models\User;
use App\Services\CentroCusto\CentroCustoGestorResolverService;
use DomainException;
use MasterTag\DataHora;

class TransferenciaPrevistaFluxoAprovacaoService
{
    public const ETAPA_GESTOR_ORIGEM = 'gestor_origem';
    public const ETAPA_GESTOR_DESTINO = 'gestor_destino';
    public const ETAPA_GESTOR_UNICO = 'gestor_unico';
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

        if ($this->modoAprovacaoEmpresa($empresaId) === TransferenciaPrevista::MODO_APROVACAO_GESTOR_UNICO) {
            return;
        }

        if (!$this->gestorResolver->getGestorPrincipal($destino->id)) {
            throw new DomainException(self::MSG_GESTOR_DESTINO_AUSENTE);
        }
    }

    /**
     * Config de "gestor aprovação" designado pela empresa para o processo de
     * transferência (GestorAprovacaoConfig) — independente da AprovacaoExtraConfig.
     */
    private function configGestorUnico(int $empresaId): ?GestorAprovacaoConfig
    {
        return GestorAprovacaoConfig::getConfigAtiva($empresaId, 'transferencia');
    }

    public function modoAprovacaoEmpresa(int $empresaId): string
    {
        $config = $this->configGestorUnico($empresaId);

        return ($config && $config->gestor_aprovacao_id)
            ? TransferenciaPrevista::MODO_APROVACAO_GESTOR_UNICO
            : TransferenciaPrevista::MODO_APROVACAO_PADRAO;
    }

    public function gestorAprovacaoUnicoId(int $empresaId): ?int
    {
        return $this->configGestorUnico($empresaId)?->gestor_aprovacao_id;
    }

    /**
     * Centro de custo origem sem gestor não bloqueia a solicitação. Quando o único
     * gestor disponível é o próprio solicitante (sem substituto/superior), a
     * solicitação não é bloqueada: o gestor (o próprio solicitante) continua
     * registrado na etapa, mas só o RH pode aprová-la (ver podeAprovarGestorOrigem).
     */
    public function resolverGestorOrigem(?int $origemId, int $solicitanteId): ?User
    {
        if ($origemId === null) {
            return null;
        }

        $principal = $this->gestorResolver->getGestorPrincipal($origemId);
        if (!$principal) {
            return null;
        }

        try {
            return $this->gestorResolver->resolverAprovador($origemId, $solicitanteId);
        } catch (DomainException) {
            return $principal;
        }
    }

    /**
     * Quando o centro de custo destino não tem gestor algum, bloqueia (obrigatório
     * ter um responsável). Quando o único gestor disponível é o próprio solicitante
     * (sem substituto/superior), não bloqueia: mantém o gestor (o próprio
     * solicitante) registrado, mas só o RH pode aprovar essa etapa.
     *
     * @throws DomainException quando o centro de custo destino não tem gestor algum
     */
    public function resolverGestorDestino(int $destinoId, int $solicitanteId): User
    {
        $principal = $this->gestorResolver->getGestorPrincipal($destinoId);
        if (!$principal) {
            throw new DomainException(self::MSG_GESTOR_DESTINO_AUSENTE);
        }

        try {
            return $this->gestorResolver->resolverAprovador($destinoId, $solicitanteId);
        } catch (DomainException) {
            return $principal;
        }
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

        if ($this->modoAprovacaoEmpresa($empresaId) === TransferenciaPrevista::MODO_APROVACAO_GESTOR_UNICO) {
            return [
                'gestor_id' => null,
                'gestor_destino_id' => null,
                'exige_aprovacao_gestor_destino' => false,
                'fluxo_gestores_automatico' => true,
                'modo_aprovacao' => TransferenciaPrevista::MODO_APROVACAO_GESTOR_UNICO,
                'gestor_aprovacao_id' => $this->gestorAprovacaoUnicoId($empresaId),
                'status_aprovacao' => null,
                'user_aprovacao_id' => null,
                'data_aprovacao' => null,
                'obs_aprovacao' => null,
                'status_aprovacao_gestor_destino' => null,
                'user_aprovacao_gestor_destino_id' => null,
                'data_aprovacao_gestor_destino' => null,
                'obs_aprovacao_gestor_destino' => null,
                'status_aprovacao_gestor_unico' => null,
                'user_aprovacao_gestor_unico_id' => null,
                'data_aprovacao_gestor_unico' => null,
                'obs_aprovacao_gestor_unico' => null,
            ];
        }

        $gestorOrigem = $this->resolverGestorOrigem($origemId, $solicitanteId);
        $gestorDestino = $this->resolverGestorDestino($destinoId, $solicitanteId);
        $exigeDestino = $this->deveExigirAprovacaoGestorDestino($gestorOrigem, $gestorDestino);

        // Solicitante é o próprio gestor (de origem e/ou destino), sem substituto/
        // superior disponível: a etapa já entra aprovada automaticamente, sem
        // notificar pedindo autoaprovação — segue direto para a etapa seguinte.
        $origemAutoaprovada = $gestorOrigem && (int) $gestorOrigem->id === $solicitanteId;
        $destinoAutoaprovada = $exigeDestino && (int) $gestorDestino->id === $solicitanteId;
        $dataHoraAtual = (new DataHora())->dataHoraInsert();

        return [
            'gestor_id' => $gestorOrigem?->id,
            'gestor_destino_id' => $exigeDestino ? $gestorDestino->id : null,
            'exige_aprovacao_gestor_destino' => $exigeDestino,
            'fluxo_gestores_automatico' => true,
            'modo_aprovacao' => TransferenciaPrevista::MODO_APROVACAO_PADRAO,
            'gestor_aprovacao_id' => null,
            'status_aprovacao' => $origemAutoaprovada ? 'aprovado' : null,
            'user_aprovacao_id' => $origemAutoaprovada ? $solicitanteId : null,
            'data_aprovacao' => $origemAutoaprovada ? $dataHoraAtual : null,
            'obs_aprovacao' => $origemAutoaprovada
                ? 'Aprovação automática: solicitante é o próprio gestor responsável pelo centro de custo de origem, sem substituto disponível.'
                : null,
            'status_aprovacao_gestor_destino' => $destinoAutoaprovada ? 'aprovado' : null,
            'user_aprovacao_gestor_destino_id' => $destinoAutoaprovada ? $solicitanteId : null,
            'data_aprovacao_gestor_destino' => $destinoAutoaprovada ? $dataHoraAtual : null,
            'obs_aprovacao_gestor_destino' => $destinoAutoaprovada
                ? 'Aprovação automática: solicitante é o próprio gestor responsável pelo centro de custo de destino, sem substituto disponível.'
                : null,
            'status_aprovacao_gestor_unico' => null,
            'user_aprovacao_gestor_unico_id' => null,
            'data_aprovacao_gestor_unico' => null,
            'obs_aprovacao_gestor_unico' => null,
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
                $dados['obs_aprovacao_gestor_destino'],
                $dados['status_aprovacao_gestor_unico'],
                $dados['user_aprovacao_gestor_unico_id'],
                $dados['data_aprovacao_gestor_unico'],
                $dados['obs_aprovacao_gestor_unico']
            );
        }

        $transferencia->fill($dados);
    }

    public function etapaAtual(TransferenciaPrevista $transferencia): string
    {
        if ($transferencia->status_aprovacao === 'reprovado'
            || $transferencia->status_aprovacao_gestor_destino === 'reprovado'
            || $transferencia->status_aprovacao_gestor_unico === 'reprovado'
            || $transferencia->status_aprovacao_extra === 'reprovado'
            || $transferencia->resposta_rh === 'reprovado') {
            return self::ETAPA_REPROVADO;
        }

        if ($transferencia->resposta_rh === 'aprovado') {
            return self::ETAPA_CONCLUIDO;
        }

        if ($transferencia->modo_aprovacao === TransferenciaPrevista::MODO_APROVACAO_GESTOR_UNICO) {
            if (!$transferencia->status_aprovacao_gestor_unico && !$this->gestorUnicoEhOSolicitante($transferencia)) {
                return self::ETAPA_GESTOR_UNICO;
            }
        } else {
            if (!$transferencia->status_aprovacao && $this->exigeAprovacaoGestorOrigem($transferencia)) {
                return self::ETAPA_GESTOR_ORIGEM;
            }

            if ($transferencia->exige_aprovacao_gestor_destino && !$transferencia->status_aprovacao_gestor_destino) {
                return self::ETAPA_GESTOR_DESTINO;
            }
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

        if ($this->gestorEhOSolicitante($transferencia, $transferencia->gestor_id)) {
            return false;
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

        if ($this->gestorEhOSolicitante($transferencia, $transferencia->gestor_destino_id)) {
            return false;
        }

        return (int) $transferencia->gestor_destino_id === (int) $user->id;
    }

    /**
     * Verdadeiro quando o gestor designado para a etapa é o próprio solicitante —
     * caso de autoaprovação sem substituto/superior disponível, resolvido em
     * resolverGestorOrigem/resolverGestorDestino. Nesse caso só o RH pode aprovar
     * (ver usuarioTemPrivilegioRh acima, que é checado antes desta chamada).
     */
    private function gestorEhOSolicitante(TransferenciaPrevista $transferencia, ?int $gestorId): bool
    {
        if ((int) ($gestorId ?? 0) <= 0) {
            return false;
        }

        return (int) $gestorId === (int) $transferencia->user_id;
    }

    /**
     * Quando o próprio solicitante é o gestor de aprovação designado pela empresa,
     * a etapa é dispensada (evita autoaprovação): não bloqueia, não notifica, segue o fluxo.
     */
    public function gestorUnicoEhOSolicitante(TransferenciaPrevista $transferencia): bool
    {
        if ((int) ($transferencia->gestor_aprovacao_id ?? 0) <= 0) {
            return false;
        }

        return (int) $transferencia->gestor_aprovacao_id === (int) $transferencia->user_id;
    }

    public function podeAprovarGestorUnico(TransferenciaPrevista $transferencia, User $user): bool
    {
        if ($this->etapaAtual($transferencia) !== self::ETAPA_GESTOR_UNICO) {
            return false;
        }

        if (!$transferencia->gestor_aprovacao_id) {
            return false;
        }

        if ($this->usuarioTemPrivilegioRh($user)) {
            return true;
        }

        return (int) $transferencia->gestor_aprovacao_id === (int) $user->id;
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

        $gestorId = match ($etapa) {
            self::ETAPA_GESTOR_DESTINO => $transferencia->gestor_destino_id,
            self::ETAPA_GESTOR_UNICO => $transferencia->gestor_aprovacao_id,
            default => $transferencia->gestor_id,
        };

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
        if ($transferencia->modo_aprovacao === TransferenciaPrevista::MODO_APROVACAO_GESTOR_UNICO) {
            if ($this->gestorUnicoEhOSolicitante($transferencia)) {
                return true;
            }

            return $transferencia->status_aprovacao_gestor_unico === 'aprovado';
        }

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
