<?php

namespace Tests\Unit\Support;

use App\Support\Tenant\WithoutEmpresaScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WithoutEmpresaScopeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('wgs_probe');
        Schema::create('wgs_probe', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nome')->nullable();
        });

        WgsProbe::query()->insert([
            ['empresa_id' => 1, 'nome' => 'um'],
            ['empresa_id' => 2, 'nome' => 'dois'],
        ]);
    }

    #[Test]
    public function exige_empresa_id_positivo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        WithoutEmpresaScope::forEmpresa(WgsProbe::class, 0, fn ($q) => $q->get());
    }

    #[Test]
    public function filtra_por_empresa_apos_remover_scopes(): void
    {
        $nomes = WithoutEmpresaScope::forEmpresa(
            WgsProbe::class,
            2,
            fn ($q) => $q->pluck('nome')->all()
        );

        $this->assertSame(['dois'], $nomes);
    }
}

class WgsProbe extends Model
{
    protected $table = 'wgs_probe';

    public $timestamps = false;

    protected $guarded = [];
}
