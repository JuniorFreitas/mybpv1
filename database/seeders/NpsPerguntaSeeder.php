<?php

namespace Database\Seeders;

use App\Models\NpsPergunta;
use Illuminate\Database\Seeder;

class NpsPerguntaSeeder extends Seeder
{
    /**
     * Perguntas iniciais globais (empresa_id = null) para o NPS.
     *
     * @return void
     */
    public function run()
    {
        $perguntas = [
            ['texto' => 'De 1 a 5, quanto você recomenda o MyBP para outras empresas?', 'ordem' => 1],
            ['texto' => 'Como você avalia a usabilidade da plataforma?', 'ordem' => 2],
            ['texto' => 'Como você avalia o suporte oferecido pela equipe MyBP?', 'ordem' => 3],
        ];

        foreach ($perguntas as $item) {
            NpsPergunta::firstOrCreate(
                [
                    'texto' => $item['texto'],
                    'empresa_id' => null,
                ],
                [
                    'ordem' => $item['ordem'],
                    'ativo' => true,
                ]
            );
        }
    }
}
