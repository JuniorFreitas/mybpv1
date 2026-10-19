<?php

use App\Services\Treinamento\MigrarSegmentosParaPadroesTreinamento;
use Illuminate\Database\Migrations\Migration;

class SeedPadroesFromSegmentosTreinamento extends Migration
{
    public function up()
    {
        (new MigrarSegmentosParaPadroesTreinamento())->executar();
    }

    public function down()
    {
        // Dados migrados não são revertidos automaticamente (seguro para produção).
    }
}
