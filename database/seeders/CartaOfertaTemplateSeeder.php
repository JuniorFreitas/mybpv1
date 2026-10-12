<?php

namespace Database\Seeders;

use App\Models\CartaOfertaTemplate;
use App\Models\Cliente;
use Illuminate\Database\Seeder;

class CartaOfertaTemplateSeeder extends Seeder
{
    public function run()
    {
        $templateHtml = CartaOfertaTemplate::htmlPadrao();

        Cliente::withoutGlobalScopes()->select(['id'])->chunkById(200, function ($clientes) use ($templateHtml) {
            foreach ($clientes as $cliente) {
                $exists = CartaOfertaTemplate::withoutGlobalScopes()->where('empresa_id', $cliente->id)->exists();
                if ($exists) {
                    continue;
                }

                CartaOfertaTemplate::withoutGlobalScopes()->create([
                    'empresa_id' => $cliente->id,
                    'titulo' => 'Carta Oferta',
                    'conteudo_html' => $templateHtml,
                    'status' => CartaOfertaTemplate::STATUS_PUBLICADO,
                    'versao' => 1,
                    'criado_por' => null,
                    'atualizado_por' => null,
                ]);
            }
        });
    }
}
