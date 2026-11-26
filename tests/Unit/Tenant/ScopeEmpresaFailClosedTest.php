<?php

namespace Tests\Unit\Tenant;

use App\Models\User;
use App\Tenant\Scopes\ScopeEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use Tests\TestCase;

class ScopeEmpresaFailClosedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('tenant_probe');

        Schema::create('tenant_probe', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nome')->nullable();
        });

        TenantProbe::query()->insert([
            ['empresa_id' => 10, 'nome' => 'Minha'],
            ['empresa_id' => 99, 'nome' => 'Outra'],
        ]);
    }

    private function forceRunningInConsole(bool $value): void
    {
        $prop = new ReflectionProperty($this->app, 'isRunningInConsole');
        $prop->setAccessible(true);
        $prop->setValue($this->app, $value);
    }

    #[Test]
    public function guest_http_nao_enxerga_registros(): void
    {
        Auth::logout();
        $this->forceRunningInConsole(false);

        $this->assertSame(0, TenantProbe::query()->count());
    }

    #[Test]
    public function usuario_so_ve_sua_empresa(): void
    {
        $user = new User();
        $user->forceFill([
            'id' => 1,
            'empresa_id' => 10,
            'nome' => 'RH',
        ]);
        Auth::setUser($user);
        $this->forceRunningInConsole(false);

        $ids = TenantProbe::query()->pluck('empresa_id')->all();
        $this->assertSame([10], array_values($ids));
    }
}

class TenantProbe extends Model
{
    protected $table = 'tenant_probe';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'empresa_id' => 'int',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new ScopeEmpresa());
    }
}
