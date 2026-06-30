<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('habilidades')->where('nome', 'configuracao_whatsapp')->exists();

        if (!$exists) {
            DB::table('habilidades')->insert([
                'nome' => 'configuracao_whatsapp',
                'descricao' => 'Configurar templates e dados de contato WhatsApp (menu Customizações)',
            ]);
        }
    }

    public function down(): void
    {
        DB::table('habilidades')->where('nome', 'configuracao_whatsapp')->delete();
    }
};
