<?php

namespace App\Services\Admissao\Importacao;

use App\Models\Admissao;
use App\Models\AdmissaoAso;
use App\Models\Curriculo;
use App\Models\Sistema;
use App\Models\User;
use App\Models\VagasAbertas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Persiste uma linha de importação (payload curriculo + admissao) no banco.
 * Uma transação por linha. Nunca duplica: atualiza por CPF+empresa ou cria.
 *
 * @return array{sucesso: bool, erro?: string}
 */
class PersistidorAdmissaoImportada
{
    /**
     * Persiste um item (curriculo + admissao) para a empresa.
     *
     * @param array{curriculo: array, admissao: array} $item
     * @param int $empresaId
     * @param int|null $userId Usuário responsável (para Admissao.usuario_id e auth); se null, usa auth()->id() ou 1
     */
    public function persistir(array $item, int $empresaId, ?int $userId = null): array
    {
        if ($userId !== null) {
            Auth::loginUsingId($userId);
        }

        try {
            DB::transaction(function () use ($item, $empresaId) {
                $this->executarPersistencia($item, $empresaId);
            });
            return ['sucesso' => true];
        } catch (Throwable $e) {
            return [
                'sucesso' => false,
                'erro' => $e->getMessage() . ' (linha ' . $e->getLine() . ')',
            ];
        }
    }

    private function executarPersistencia(array $item, int $empresaId): void
    {
        $empresa_id = $empresaId;

        $usuario = User::withoutGlobalScopes()
            ->where('empresa_id', $empresa_id)
            ->whereHas('Curriculo', function ($q) use ($item) {
                $q->where('cpf', $item['curriculo']['cpf']);
            });

        $dadosUser = [
            'nome' => $item['curriculo']['nome'],
            'login' => $item['curriculo']['email'],
            'password' => Sistema::SenhaCpf($item['curriculo']['cpf']),
            'tipo' => User::FUNCIONARIO,
            'ativo' => true,
            'temp' => false,
            'termos' => false,
            'empresa_id' => $empresa_id,
        ];

        if ($usuario->count() === 0) {
            $usuario = User::withoutGlobalScopes()->create($dadosUser);
        } else {
            $usuario = $usuario->first();
            $usuario->update($dadosUser);
        }

        $dadosConta = [
            'banco' => $item['admissao']['banco']['nome'] ?? null,
            'agencia' => $item['admissao']['banco']['agencia'] ?? null,
            'conta' => $item['admissao']['banco']['conta'] ?? null,
            'pix' => $item['admissao']['banco']['pix'] ?? false,
            'tipochavepix' => $item['admissao']['banco']['pix_tipo_chave'] ?? null,
            'chavepix' => $item['admissao']['banco']['pix_chave'] ?? null,
        ];

        if ($usuario->BancoConta) {
            $usuario->BancoConta->update($dadosConta);
        } else {
            $usuario->BancoConta()->create($dadosConta);
        }

        $vagaAberta = VagasAbertas::withoutGlobalScopes()
            ->with('Municipio', 'Vaga')
            ->find($item['curriculo']['vaga_pretendida']);

        $ufVaga = null;
        $municipioId = null;
        if ($vagaAberta && $vagaAberta->Municipio) {
            $ufVaga = $vagaAberta->Municipio->uf ?? null;
            $municipioId = $vagaAberta->Municipio->id ?? null;
        }

        $nascimento = $this->normalizarDataParaBanco($item['curriculo']['nascimento'] ?? null);
        $cnhVencimento = $this->normalizarDataParaBanco($item['curriculo']['cnh_vencimento'] ?? null, true);

        $dadosCurriculo = [
            'id' => $usuario->id,
            'cpf' => $item['curriculo']['cpf'],
            'nome' => $item['curriculo']['nome'],
            'estado_civil' => $item['curriculo']['estado_civil'],
            'cnh' => $item['curriculo']['cnh'],
            'cnh_vencimento' => $cnhVencimento,
            'email' => $item['curriculo']['email'],
            'nascimento' => $nascimento,
            'naturalidade' => $item['curriculo']['naturalidade'],
            'logradouro' => $item['curriculo']['endereco']['logradouro'],
            'end_numero' => $item['curriculo']['endereco']['numero'],
            'complemento' => $item['curriculo']['endereco']['complemento'],
            'bairro' => $item['curriculo']['endereco']['bairro'],
            'municipio' => $item['curriculo']['endereco']['municipio'],
            'uf' => $item['curriculo']['endereco']['uf'],
            'cep' => $item['curriculo']['endereco']['cep'],
            'uf_vaga' => $ufVaga,
            'municipio_id' => $municipioId,
            'rg' => $item['curriculo']['rg'],
            'rg_data_emissao' => $item['curriculo']['rg_data_emissao'],
            'filiacao_pai' => $item['curriculo']['filiacao_pai'],
            'filiacao_mae' => $item['curriculo']['filiacao_mae'],
            'sexo' => $item['curriculo']['sexo'],
            'pcd' => $item['curriculo']['pcd'],
            'cid' => $item['curriculo']['cid'],
            'vaga_pretendida' => $item['curriculo']['vaga_pretendida'],
        ];

        $curriculo = Curriculo::withoutGlobalScopes()->find($usuario->id);
        if ($curriculo === null) {
            $curriculo = Curriculo::withoutGlobalScopes()->create($dadosCurriculo);
        } else {
            $curriculo->update($dadosCurriculo);
        }

        $dadosTel = [
            'curriculo_id' => $curriculo->id,
            'tipo' => $item['curriculo']['telefone']['whatsapp'],
            'pais' => '55',
            'numero' => $item['curriculo']['telefone']['numero'],
            'principal' => true,
        ];

        $telefone = $curriculo->Telefones()->updateOrCreate(
            ['curriculo_id' => $curriculo->id, 'principal' => true],
            $dadosTel
        );
        $telefone_id = $telefone->id;

        $vagaId = $vagaAberta !== null ? $vagaAberta->vaga_id : null;

        $curriculo->FeedBack()->updateOrCreate(
            ['curriculo_id' => $curriculo->id],
            [
                'selecionado' => 'sim',
                'vaga_id' => $vagaId,
                'cliente_id' => $empresa_id,
                'empresa_id' => $empresa_id,
                'interesse' => true,
                'contato_realizado' => true,
                'telefone_id' => $telefone_id,
                'vagas_abertas_id' => $item['curriculo']['vaga_pretendida'],
            ]
        );

        $curriculo->load('FeedBack');
        $feedback = $curriculo->FeedBack;

        $feedback->parecerRh()->firstOrCreate([], ['nota' => 9]);
        $feedback->parecerRota()->firstOrCreate([]);
        $feedback->parecerTecnica()->firstOrCreate([]);
        $feedback->parecerTeste()->firstOrCreate([]);
        $feedback->individualRh()->firstOrCreate([]);
        $feedback->gestorRh()->firstOrCreate([]);
        $feedback->entrevistaRh()->firstOrCreate([]);

        $feedback->ResultadoIntegrado()->updateOrCreate(
            ['feedback_id' => $feedback->id],
            [
                'responsavel_envio' => 'importacao',
                'documentos_entregue' => false,
                'encaminhado_exame' => (bool) ($item['admissao']['encaminhado_exame'] ?? false),
                'encaminhado_exame_data' => $item['admissao']['encaminhado_exame_data'] ?? null,
                'encaminhado_treinamento' => (bool) ($item['admissao']['encaminhado_treinamento'] ?? false),
                'encaminhado_treinamento_data' => $item['admissao']['encaminhado_treinamento_data'] ?? null,
            ]
        );

        $cargoNome = $vagaAberta && $vagaAberta->Vaga ? $vagaAberta->Vaga->nome : '';

        $feedback->Admissao()->updateOrCreate(
            ['feedback_id' => $feedback->id],
            [
                'centro_custo_id' => $item['admissao']['centro_custo_id'],
                'area_etiqueta_id' => $item['admissao']['area_etiqueta_id'],
                'data_entrega_area' => $item['admissao']['data_entrega_area'] ?: null,
                'data_admissao' => $item['admissao']['data_admissao'],
                'cargo' => $cargoNome,
                'funcao' => $cargoNome,
                'status' => Admissao::STATUS_ADMISSAO_ADMITIDO,
                'salario' => $item['admissao']['salario'] ?: null,
                'pis' => $item['admissao']['pis'] ?: null,
                'tipo_admissao' => $item['admissao']['tipo_admissao'],
                'prazo_experiencia' => $item['admissao']['prazo_experiencia'] ?: null,
                'data_encerramento' => $item['admissao']['admissao_encerramento'] ?: null,
                'usuario_id' => Auth::id() ?? 1,
            ]
        );

        $feedback->load('Admissao');

        Admissao::tipoAdmissaoAvalNoventaCriarAtualizar(
            $feedback->id,
            $item['admissao']['tipo_admissao'],
            $item['admissao']['prazo_experiencia'],
            $item['admissao']['data_admissao'],
            $item['admissao']['admissao_encerramento']
        );

        AdmissaoAso::criarAtualizar($feedback->Admissao->id, $empresa_id, $item['admissao']['data_aso']);

        $feedback->Admissao->DadosAdmissoes()->updateOrCreate(
            ['admissao_id' => $feedback->Admissao->id],
            [
                'ctps_numero' => $item['admissao']['ctps_numero'] ?? null,
                'ctps_serie' => $item['admissao']['ctps_serie'] ?? null,
                'ctps_data_emissao' => $item['admissao']['ctps_data_emissao'] ?? null,
                'titulo_eleitor_numero' => $item['admissao']['titulo_eleitor_numero'] ?? null,
                'titulo_eleitor_sessao' => $item['admissao']['titulo_eleitor_sessao'] ?? null,
                'titulo_eleitor_zona' => $item['admissao']['titulo_eleitor_zona'] ?? null,
            ]
        );
    }

    /**
     * Garante data em Y-m-d para o banco/DataHora. Corrige formato yyyy-d-m (ex.: 1994-29-7).
     *
     * @param string|mixed $valor
     */
    private function normalizarDataParaBanco($valor, bool $opcional = false): ?string
    {
        $v = $valor === null || $valor === '' ? '' : trim((string) $valor);
        if ($v === '') {
            return $opcional ? null : '';
        }
        if (preg_match('/^(\d{4})\D+(\d{1,2})\D+(\d{1,2})$/', $v, $m)) {
            $ano = (int) $m[1];
            $a = (int) $m[2];
            $b = (int) $m[3];
            if ($a >= 1 && $a <= 12 && $b >= 1 && $b <= 31 && checkdate($a, $b, $ano)) {
                return sprintf('%04d-%02d-%02d', $ano, $a, $b);
            }
            if ($b >= 1 && $b <= 12 && $a >= 1 && $a <= 31 && checkdate($b, $a, $ano)) {
                return sprintf('%04d-%02d-%02d', $ano, $b, $a);
            }
        }
        if (preg_match('/^(\d{1,2})\D+(\d{1,2})\D+(\d{4})$/', $v, $m)) {
            $dia = (int) $m[1];
            $mes = (int) $m[2];
            $ano = (int) $m[3];
            if ($mes >= 1 && $mes <= 12 && $dia >= 1 && $dia <= 31 && checkdate($mes, $dia, $ano)) {
                return sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
            }
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
            return $v;
        }
        return $opcional ? null : $v;
    }
}
