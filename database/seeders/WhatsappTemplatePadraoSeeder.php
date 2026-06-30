<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\EmpresaWhatsappTemplate;
use Illuminate\Database\Seeder;

class WhatsappTemplatePadraoSeeder extends Seeder
{
    public function run(): void
    {
        $templates = config('whatsapp_templates.templates', []);

        if ($templates === []) {
            $this->command?->warn('Nenhum template padrão encontrado em config/whatsapp_templates.php');

            return;
        }

        Cliente::withoutGlobalScopes()
            ->select(['id'])
            ->chunkById(200, function ($clientes) use ($templates) {
                foreach ($clientes as $cliente) {
                    foreach ($templates as $tipoMensagem => $corpo) {
                        $exists = EmpresaWhatsappTemplate::query()
                            ->where('empresa_id', $cliente->id)
                            ->where('tipo_mensagem', $tipoMensagem)
                            ->exists();

                        if ($exists) {
                            continue;
                        }

                        EmpresaWhatsappTemplate::query()->create([
                            'empresa_id' => $cliente->id,
                            'tipo_mensagem' => $tipoMensagem,
                            'corpo' => $corpo,
                            'ativo' => true,
                        ]);

                        $this->command?->info("Template WhatsApp [{$tipoMensagem}] criado para empresa {$cliente->id}");
                    }
                }
            });
    }
}
