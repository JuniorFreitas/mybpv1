<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exame_funcionarios', function (Blueprint $table) {
            if (!Schema::hasColumn('exame_funcionarios', 'exames_catalogo')) {
                $table->json('exames_catalogo')->nullable()->after('exame_tipo_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exame_funcionarios', function (Blueprint $table) {
            if (Schema::hasColumn('exame_funcionarios', 'exames_catalogo')) {
                $table->dropColumn('exames_catalogo');
            }
        });
    }
};
