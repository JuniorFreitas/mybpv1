<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('habilidades')->where('nome', 'preferencias_notificacao_whatsapp')->exists();

        if (!$exists) {
            DB::table('habilidades')->insert([
                'nome' => 'preferencias_notificacao_whatsapp',
                'descricao' => 'Escolher quais notificações WhatsApp deseja receber da empresa',
            ]);
        }
    }

    public function down(): void
    {
        DB::table('habilidades')->where('nome', 'preferencias_notificacao_whatsapp')->delete();
    }
};
