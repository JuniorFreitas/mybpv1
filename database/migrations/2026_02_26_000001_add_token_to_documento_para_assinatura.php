<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddTokenToDocumentoParaAssinatura extends Migration
{
    /**
     * Run the migrations.
     * Token público para uso em URLs (evita expor o id numérico).
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documento_para_assinatura', function (Blueprint $table) {
            $table->string('token', 64)->nullable()->unique()->after('id');
        });

        $tableName = (new \App\Models\DocumentoParaAssinatura())->getTable();
        $rows = \DB::table($tableName)->whereNull('token')->get(['id']);
        foreach ($rows as $row) {
            \DB::table($tableName)->where('id', $row->id)->update([
                'token' => Str::random(32),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documento_para_assinatura', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }
}
