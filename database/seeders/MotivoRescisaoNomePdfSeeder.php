<?php

namespace Database\Seeders;

use App\Models\MotivoRescisao;
use Illuminate\Database\Seeder;
use DB;

class MotivoRescisaoNomePdfSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $dados[0] = ['nome_pdf' => 'termino_contrato'];
        $dados[1] = ['nome_pdf' => 'pedido_colaborador_trabalhado'];
        $dados[2] = ['nome_pdf' => 'pedido_colaborador_imediato'];
        $dados[3] = ['nome_pdf' => 'demissao_sem_justa_causa'];
        $dados[4] = ['nome_pdf' => 'demissao_comum_acordo'];
        $dados[5] = ['nome_pdf' => 'demissao_com_justa_causa'];
        $dados[6] = ['nome_pdf' => 'outros_motivos'];
        $dados[7] = ['nome_pdf' => 'contrato_temporário'];
        $dados[8] = ['nome_pdf' => 'quebra_contrato_colaborador'];
        $dados[9] = ['nome_pdf' => 'quebra_contrato_empresa'];

        try {
            DB::beginTransaction();

            foreach ($dados as $index => $dado) {
                $index = $index + 1;
                $motivo = MotivoRescisao::find($index);
                $motivo->update($dado);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage(), $e->getCode(), $e->getLine(), $e->getTraceAsString());
        }
    }
}
