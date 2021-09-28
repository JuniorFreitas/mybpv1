<?php

use Illuminate\Database\Seeder;

class HabilidadesCloudsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $lista[] = ['nome' => 'Download'];
        $lista[] = ['nome' => 'Visualizar'];
        $lista[] = ['nome' => 'Detalhes'];
        $lista[] = ['nome' => 'Editar'];
        $lista[] = ['nome' => 'Mover'];
        $lista[] = ['nome' => 'Deletar'];
        $lista[] = ['nome' => 'Atualizar'];
        $lista[] = ['nome' => 'Revisar'];
        $lista[] = ['nome' => 'Aprovar'];

        foreach ($lista as $lin) {
            \App\Models\HabilidadeCloud::create($lin);
        }

    }
}
