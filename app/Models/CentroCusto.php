<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use LaravelIdea\Helper\App\Models\_IH_CentroCusto_C;
use LaravelIdea\Helper\App\Models\_IH_CentroCusto_QB;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * App\Models\CentroCusto
 *
 * @property int $id
 * @property int|null $gestor_id
 * @property int|null $cliente_id
 * @property string $label
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $empresa_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Admissao> $Admissao
 * @property-read int|null $admissao_count
 * @property-read \App\Models\User|null $Empresa
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CentroCustoFilial> $Filiais
 * @property-read int|null $filiais_count
 * @property-read \App\Models\User|null $Gestor
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto query()
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereGestorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CentroCusto extends Model
{
    use TenantTrait;

    protected $fillable = ['gestor_id', 'label', 'empresa_id', 'ativo'];
    protected $casts = ['id' => 'int', 'gestor_id' => 'int', 'label' => 'string', 'empresa_id' => 'int', 'ativo' => 'boolean'];

    public function Empresa()
    {
        return $this->hasOne(User::class, 'id', 'empresa_id');
    }

    public function Gestor()
    {
        return $this->hasOne(User::class, 'id', 'gestor_id')->select(['id', 'nome', 'login']);
    }

    public function Admissao()
    {
        return $this->hasMany(Admissao::class, 'centro_custo_id', 'id');
    }

    public function Filiais()
    {
        return $this->hasMany(CentroCustoFilial::class, 'centro_custo_id', 'id');
    }

    /**
     * @param $empresaId
     * @return JsonResponse|Collection
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function listaCentroCustoPorCnpj($empresaId): \Illuminate\Http\JsonResponse|\Illuminate\Support\Collection
    {
        $empresaId = $this->getEmpresaId($empresaId);

        $cache_key = "lista_cc_{$empresaId}";

        if (!$empresaId) {
            return response()->json(['msg' => 'Empresa não informada'], 400);
        }

        if (is_null(cache()->get($cache_key))) {
            $centrosDeCusto = $this->buscarCentrosDeCusto();
            $relatoriosCentroCustos = [];
            $informacaoMatrizGeral = Cliente::select(['id', 'razao_social', 'nome_fantasia', 'cnpj'])->find($empresaId);

            foreach ($centrosDeCusto as $centroDeCusto) {
                $temFiliais = count($centroDeCusto->Filiais) > 0;
                $cnpj = $temFiliais ? $centroDeCusto->Filiais[0]->Filial->cnpj : $informacaoMatrizGeral->cnpj;
                $relatoriosCentroCustos[] = [
                    'id' => $centroDeCusto->id,
                    'label' => $centroDeCusto->label,
                    'filial_id' => $temFiliais ? $centroDeCusto->Filiais[0]->id : null,
                    'razao_social' => $temFiliais ? $centroDeCusto->Filiais[0]->Filial->razao_social : $informacaoMatrizGeral->razao_social,
                    'nome_fantasia' => $temFiliais ? $centroDeCusto->Filiais[0]->Filial->nome_fantasia : $informacaoMatrizGeral->nome_fantasia,
                    'cnpj_format' => $temFiliais ? $centroDeCusto->Filiais[0]->Filial->cnpj : $informacaoMatrizGeral->cnpj,
                    'cnpj' => preg_replace("/[^0-9]/", "", $cnpj),
                    'matriz' => !$temFiliais,
                    'ativo' => $centroDeCusto->ativo
                ];
            }

            $results = collect($relatoriosCentroCustos)->groupBy('cnpj');
            $cnpjs = collect();

            foreach ($results as $key => $value) {
                $cnpjs[$key] = [
                    'cnpj' => $value[0]['cnpj_format'],
                    'razao_social' => $value[0]['razao_social'],
                    'nome_fantasia' => $value[0]['nome_fantasia'],
                    'matriz' => $value[0]['matriz'],
                    'ativo' => $value[0]['ativo']
                ];
            }

            cache()->put($cache_key, collect([
                'cnpjs' => $cnpjs,
                'centros_custos' => $results,
                'ultima_atualizacao' => now()->format('d/m/Y H:i:s'),
            ]), now()->addDays(7));

            return cache()->get($cache_key);
        }

        return cache()->get($cache_key);
    }

    /**
     * @param Request $request
     * @return int|null
     */
    private function getEmpresaId($empresaId): ?int
    {
        return !auth()->check() ? $empresaId : auth()->user()->empresa_id;
    }

    /**
     * @return CentroCusto[]|Builder[]|\Illuminate\Database\Eloquent\Collection|Collection|_IH_CentroCusto_C|_IH_CentroCusto_QB[]
     */
    private function buscarCentrosDeCusto()
    {
        return CentroCusto::select(['id', 'label', 'ativo'])->whereAtivo(true)
            ->with('Filiais', function ($query) {
                $query->select(['id', 'centro_custo_id', 'cliente_filial_id', 'empresa_id'])
                    ->whereAtivo(true)
                    ->with('Filial:id,dados->razao_social as razao_social,dados->nome_fantasia as nome_fantasia,dados->cnpj as cnpj');
            })
            ->get()->transform(function ($item) {
                $item->text = $item->label;
                return $item;
            });
    }

    protected static function booted()
    {
        static::created(function ($model) {
            cache()->forget("lista_cc_{$model->empresa_id}");
            $this->listaCentroCustoPorCnpj($model->empresa_id);
        });
        static::updated(function ($model) {
            cache()->forget("lista_cc_{$model->empresa_id}");
            (new CentroCusto())->listaCentroCustoPorCnpj($model->empresa_id);
        });
    }
}
