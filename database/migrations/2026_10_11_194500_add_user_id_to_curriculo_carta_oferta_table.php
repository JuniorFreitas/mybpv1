<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curriculo_carta_oferta', function (Blueprint $table) {
            if (!Schema::hasColumn('curriculo_carta_oferta', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('empresa_id');
                $table->index('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('curriculo_carta_oferta', function (Blueprint $table) {
            if (Schema::hasColumn('curriculo_carta_oferta', 'user_id')) {
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
