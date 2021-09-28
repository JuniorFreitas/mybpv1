<?php

namespace Database\Seeders;

use App\Models\Habilidade;
use App\Models\Papel;
use Illuminate\Database\Seeder;

class PapeisHabilidadesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $papel = Papel::find(1); // Suporte
        $habilidades = Habilidade::all();
        $papel->habilidades()->attach($habilidades);


       /* $papel = Papel::find(2); // Administrador: Não tem a página de Habilidade para não danificar o sistema
        $habilidades= Habilidade::where('id', '>',1 )->get();
        $papel->habilidades()->attach($habilidades);*/
    }
}
