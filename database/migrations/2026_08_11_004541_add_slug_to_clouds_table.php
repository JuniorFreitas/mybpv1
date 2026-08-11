<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clouds', function (Blueprint $table) {
            $table->string('slug')->default('')->after('nome');
        });

        $clouds = DB::table('clouds')->select(['id', 'nome', 'empresa_id'])->orderBy('id')->get();
        $usados = [];

        foreach ($clouds as $cloud) {
            $base = Str::slug((string) $cloud->nome);
            if ($base === '') {
                $base = 'cloud';
            }

            $slug = $base;
            $sufixo = 2;
            $chaveEmpresa = (string) $cloud->empresa_id;

            while (isset($usados[$chaveEmpresa][$slug])) {
                $slug = $base . '-' . $sufixo;
                $sufixo++;
            }

            $usados[$chaveEmpresa][$slug] = true;

            DB::table('clouds')->where('id', $cloud->id)->update(['slug' => $slug]);
        }

        Schema::table('clouds', function (Blueprint $table) {
            $table->unique(['empresa_id', 'slug'], 'clouds_empresa_id_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('clouds', function (Blueprint $table) {
            $table->dropUnique('clouds_empresa_id_slug_unique');
            $table->dropColumn('slug');
        });
    }
};
