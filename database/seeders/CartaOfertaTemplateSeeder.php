<?php

namespace Database\Seeders;

use App\Models\CartaOfertaTemplate;
use App\Models\Cliente;
use Illuminate\Database\Seeder;

class CartaOfertaTemplateSeeder extends Seeder
{
    public function run()
    {
        $templateHtml = $this->templatePadrao();

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

    protected function templatePadrao(): string
    {
        return <<<HTML
<p style="text-align: center; font-weight: bold; text-transform: uppercase;">CARTA OFERTA</p>
<p>Prezado(a) <strong>{{colaborador.nome}}</strong>,</p>
<p>Conforme processo seletivo, temos o prazer de formalizar a presente carta oferta para o cargo de <strong>{{cargo}}</strong>.</p>
<p>Salario: <strong>{{salario}}</strong></p>
<p>Data de inicio prevista: <strong>{{data_inicio}}</strong></p>
<p>Ao assinar este documento, voce declara estar ciente e de acordo com as condicoes apresentadas.</p>
<p>Data: {{data_emissao}}</p>
<p style="text-align: center;">{{empresa.razao_social}}</p>
HTML;
    }
}
