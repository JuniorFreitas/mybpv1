<?php

namespace Database\Seeders;

use App\Models\AvaliacaoTipo;
use DB;
use Exception;
use Illuminate\Database\Seeder;

class AvaliacoesTiposSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $lista[] = [
                        'nome' => 'Avaliação Anual de Desempenho',
                        'descricao' => 'Serve para avaliar, anualmente, as competências e desempenho dos colaboradores',
                        'empresa_id' => 104,
                        'ativo' => true
                   ];

        try {
            DB::beginTransaction();

            foreach ($lista as $avaliacao_tipo) {
                if (AvaliacaoTipo::whereNome($avaliacao_tipo['nome'])->whereDescricao($avaliacao_tipo['descricao'])->whereEmpresaId($avaliacao_tipo['empresa_id'])->count() == 0) {
                    echo "\e[032mCriando tipo de avaliação: " . $avaliacao_tipo['nome'] . " - " . $avaliacao_tipo['descricao']. " - " . $avaliacao_tipo['empresa_id'] . "\n";
                    AvaliacaoTipo::create($avaliacao_tipo);
                } else {
                    echo "\e[34mTipo de avaliação já existe: " . $avaliacao_tipo['nome'] . " - " . $avaliacao_tipo['descricao']. " - " . $avaliacao_tipo['tipo'] . "\n";
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return $e->getTrace() . ' - ' . $e->getCode() . ' - ' . $e->getCode() . ' - ' . $e->getLine();
        }
    }
}
