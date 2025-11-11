<?php

namespace App\Services\Historico;

use App\Models\Arquivo;
use App\Models\AuditoriaInterna;
use App\Models\LogHistorico;
use App\Models\MedidaAdministrativa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MedidaAdministrativaService
{
    /**
     * Valida os dados das medidas administrativas
     *
     * @param array $dados
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validarDados(array $dados)
    {
        return \Validator::make($dados, [
            'medidas_administrativas.*.solicitante' => 'required',
            'medidas_administrativas.*.tipo' => 'required',
            'medidas_administrativas.*.causa' => 'required',
            'medidas_administrativas.*.motivo' => 'required',
            'medidas_administrativas.*.data_solicitacao' => 'required',
        ]);
    }

    /**
     * Cria ou atualiza medidas administrativas
     *
     * @param array $dados
     * @param int $feedbackId
     * @return void
     * @throws \Exception
     */
    public function storeMedidas(array $dados, int $feedbackId)
    {
        DB::beginTransaction();
        try {
            foreach ($dados['medidas_administrativas'] as $medida) {
                $medida['feedback_id'] = $feedbackId;
                $medida['user_id'] = auth()->id();
                
                if (isset($medida['novo'])) {
                    $medidaAdm = MedidaAdministrativa::create($medida);
                    $this->processarAnexos($medidaAdm, $medida);
                    $this->logCriacao($feedbackId, $medidaAdm);
                } else {
                    $medidaExistente = MedidaAdministrativa::find($medida['id']);
                    
                    if (!$medidaExistente) {
                        throw new \Exception("Medida administrativa não encontrada: {$medida['id']}");
                    }
                    
                    $this->processarAnexos($medidaExistente, $medida);
                    $this->logAtualizacao($feedbackId, $medidaExistente);
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Atualiza medidas administrativas (método legado)
     *
     * @param array $dados
     * @param int $feedbackId
     * @return void
     * @throws \Exception
     */
    public function updateMedidas(array $dados, int $feedbackId)
    {
        DB::beginTransaction();
        try {
            foreach ($dados['medidas_administrativas'] as $medida) {
                $medida['feedback_id'] = $feedbackId;
                $medida['user_id'] = auth()->id();
                
                if (!isset($medida['id'])) {
                    $medidaSingle = MedidaAdministrativa::create($medida);
                    $this->processarAnexos($medidaSingle, $medida);
                    $this->logCriacao($feedbackId, $medidaSingle);
                } else {
                    $medidaSingle = MedidaAdministrativa::find($medida['id']);
                    
                    if (!$medidaSingle) {
                        throw new \Exception("Medida administrativa não encontrada: {$medida['id']}");
                    }
                    
                    $this->processarAnexos($medidaSingle, $medida);
                    $medidaSingle->update($medida);
                    $this->logAtualizacao($feedbackId, $medidaSingle);
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Remove uma medida administrativa (soft delete)
     *
     * @param int $medidaId
     * @param string|null $motivo
     * @return void
     * @throws \Exception
     */
    public function removerMedidaAdministrativa(int $medidaId, ?string $motivo = null)
    {
        DB::beginTransaction();
        try {
            $medidaAdministrativa = MedidaAdministrativa::find($medidaId);
            
            if (!$medidaAdministrativa) {
                throw new \Exception('Medida administrativa não encontrada!');
            }
            
            $feedbackId = $medidaAdministrativa->feedback_id;
            $feedback = \App\Models\FeedbackCurriculo::find($feedbackId);
            
            // Atualiza quem_deletou_id antes de deletar
            $medidaAdministrativa->update([
                'quem_deletou_id' => auth()->id()
            ]);
            
            // Deleta a medida administrativa (soft delete)
            $medidaAdministrativa->delete();
            
            // Cria auditoria
            $dadosAuditoria = [
                'empresa_id' => auth()->user()->empresa_id,
                'usuario_id' => auth()->id(),
                'feedback_id' => $feedbackId,
                'colaborador_id' => $feedback ? $feedback->curriculo_id : null,
                'tipo' => 'remocao_medida_administrativa',
                'descricao' => $motivo ?: ('Medida administrativa removida: ' . $medidaAdministrativa->tipo),
                'dados' => json_encode([
                    'tipo_medida' => $medidaAdministrativa->tipo,
                    'motivo_remocao' => $motivo
                ])
            ];
            AuditoriaInterna::create($dadosAuditoria);

            $this->logRemocao($feedbackId, $medidaAdministrativa);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Processa anexos de uma medida administrativa (deletar e anexar)
     *
     * @param MedidaAdministrativa $medidaAdm
     * @param array $medida
     * @return void
     */
    private function processarAnexos(MedidaAdministrativa $medidaAdm, array $medida)
    {
        // Remove anexos marcados para exclusão
        if (isset($medida['anexosDel']) && is_array($medida['anexosDel'])) {
            foreach ($medida['anexosDel'] as $id_anexo) {
                $arquivo = Arquivo::find($id_anexo);
                if ($arquivo) {
                    $arquivo->excluir();
                }
            }
        }

        // Anexa novos arquivos
        if (isset($medida['anexos']) && is_array($medida['anexos'])) {
            foreach ($medida['anexos'] as $anexo) {
                $arquivo = Arquivo::whereChave($anexo['chave'] ?? '')
                    ->whereId($anexo['id'] ?? 0)
                    ->first();
                    
                if ($arquivo) {
                    $arquivo->temporario = false;
                    $arquivo->chave = '';
                    $arquivo->save();
                    $medidaAdm->Anexos()->attach($arquivo->id);
                }
            }
        }
    }

    /**
     * Registra log de criação de medida administrativa
     *
     * @param int $feedbackId
     * @param MedidaAdministrativa $medidaAdm
     * @return void
     */
    private function logCriacao(int $feedbackId, MedidaAdministrativa $medidaAdm)
    {
        LogHistorico::createLog(
            $feedbackId,
            'O Usuário ' . auth()->user()->nome . ' cadastrou a medida administrativa - ' . $medidaAdm->tipo . ' - cod da medida: #' . $medidaAdm->id
        );
    }

    /**
     * Registra log de atualização de medida administrativa
     *
     * @param int $feedbackId
     * @param MedidaAdministrativa $medidaExistente
     * @return void
     */
    private function logAtualizacao(int $feedbackId, MedidaAdministrativa $medidaExistente)
    {
        LogHistorico::createLog(
            $feedbackId,
            'O Usuário ' . auth()->user()->nome . ' atualizou a medida administrativa - ' . $medidaExistente->tipo . ' - cod da medida: #' . $medidaExistente->id
        );
    }

    /**
     * Registra log de remoção de medida administrativa
     *
     * @param int $feedbackId
     * @param MedidaAdministrativa $medidaAdministrativa
     * @return void
     */
    private function logRemocao(int $feedbackId, MedidaAdministrativa $medidaAdministrativa)
    {
        LogHistorico::createLog(
            $feedbackId,
            'O Usuário ' . auth()->user()->nome . ' realizou a remoção da medida administrativa - ' . $medidaAdministrativa->tipo . ' - cod da medida: #' . $medidaAdministrativa->id
        );
    }

    /**
     * Busca medida administrativa com relacionamentos para PDF
     *
     * @param int $medidaId
     * @return MedidaAdministrativa|null
     */
    public function buscarParaPDF(int $medidaId)
    {
        $medida = MedidaAdministrativa::whereId($medidaId)->with('Feedback');

        if ($medida->count() == 0) {
            return null;
        }

        return $medida->with(
            'Anexos',
            'Feedback:id,curriculo_id,empresa_id',
            'Feedback.Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento',
            'Feedback.Empresa:id,cnpj,razao_social,nome_fantasia,cep,logradouro,numero,complemento,bairro,municipio,uf,contato',
            'Feedback.Empresa.Logo:id,nome,layout,disco,imagem,file,thumb',
            'Feedback.Admissao:id,feedback_id,data_admissao'
        )->first();
    }
}

