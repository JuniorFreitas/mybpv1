<?php

namespace Tests\Unit\Tenant;

use App\Models\User;
use App\Scopes\ScopeClientesEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use Tests\TestCase;

class ScopeClientesEmpresaIsolamentoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['clima_probe', 'cliente_funcionarios', 'empresa_clientes', 'users'] as $tabela) {
            Schema::dropIfExists($tabela);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('empresa_clientes', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('cliente_id');
        });

        Schema::create('cliente_funcionarios', function (Blueprint $table) {
            $table->unsignedBigInteger('funcionario_id');
            $table->unsignedBigInteger('cliente_id');
        });

        Schema::create('clima_probe', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->string('nome')->nullable();
        });

        ClimaProbe::query()->insert([
            ['cliente_id' => 104, 'nome' => 'Tenant'],
            ['cliente_id' => 60331, 'nome' => 'OutroTenant'],
            ['cliente_id' => 21, 'nome' => 'ClienteNoTenant'],
        ]);
    }

    private function forceRunningInConsole(bool $value): void
    {
        $prop = new ReflectionProperty($this->app, 'isRunningInConsole');
        $prop->setAccessible(true);
        $prop->setValue($this->app, $value);
    }

    private function montarUsuario(int $userId, int $empresaId): User
    {
        DB::table('users')->insert([
            'id' => $userId,
            'nome' => 'RH',
            'empresa_id' => $empresaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ClientesEmpresa plucka users.id — stubs mínimos dos clientes.
        foreach ([104, 21, 60331] as $clienteUserId) {
            if (!DB::table('users')->where('id', $clienteUserId)->exists()) {
                DB::table('users')->insert([
                    'id' => $clienteUserId,
                    'nome' => "Cliente {$clienteUserId}",
                    'empresa_id' => $clienteUserId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return User::find($userId);
    }

    #[Test]
    public function guest_http_nao_enxerga_registros(): void
    {
        Auth::logout();
        $this->forceRunningInConsole(false);

        $this->assertSame(0, ClimaProbe::query()->count());
    }

    #[Test]
    public function pivot_fora_do_tenant_nao_vaza_cliente_alheio(): void
    {
        $user = $this->montarUsuario(50393, 104);

        DB::table('empresa_clientes')->insert([
            ['empresa_id' => 104, 'cliente_id' => 104],
            ['empresa_id' => 104, 'cliente_id' => 21],
        ]);

        // Pivot sujo — mesmo cenário BUG-004 / SEC-008.
        DB::table('cliente_funcionarios')->insert([
            'funcionario_id' => 50393,
            'cliente_id' => 60331,
        ]);

        Auth::setUser($user);
        $this->forceRunningInConsole(false);

        $ids = ClimaProbe::query()->orderBy('cliente_id')->pluck('cliente_id')->all();

        $this->assertSame([21, 104], $ids);
        $this->assertNotContains(60331, $ids);
    }

    #[Test]
    public function pivot_dentro_do_tenant_restringe_ao_cliente(): void
    {
        $user = $this->montarUsuario(8, 104);

        DB::table('empresa_clientes')->insert([
            ['empresa_id' => 104, 'cliente_id' => 104],
            ['empresa_id' => 104, 'cliente_id' => 21],
        ]);

        DB::table('cliente_funcionarios')->insert([
            'funcionario_id' => 8,
            'cliente_id' => 21,
        ]);

        Auth::setUser($user);
        $this->forceRunningInConsole(false);

        $ids = ClimaProbe::query()->pluck('cliente_id')->all();

        $this->assertSame([21], array_values($ids));
    }

    #[Test]
    public function sem_pivot_lista_todos_clientes_da_empresa(): void
    {
        $user = $this->montarUsuario(9, 104);

        DB::table('empresa_clientes')->insert([
            ['empresa_id' => 104, 'cliente_id' => 104],
            ['empresa_id' => 104, 'cliente_id' => 21],
        ]);

        Auth::setUser($user);
        $this->forceRunningInConsole(false);

        $ids = ClimaProbe::query()->orderBy('cliente_id')->pluck('cliente_id')->all();

        $this->assertSame([21, 104], $ids);
    }
}

class ClimaProbe extends Model
{
    protected $table = 'clima_probe';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'cliente_id' => 'int',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new ScopeClientesEmpresa());
    }
}
