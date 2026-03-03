<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\PlanoConta
 *
 * @property int $id
 * @property int|null $categoria_plano_id
 * @property string $descricao
 * @property string $operacao c-credito , d-debito, t-todos
 * @property bool $ativo
 * @property int $empresa_id
 * @property-read \App\Models\CategoriaPlanoConta|null $Categoria
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read mixed $operacao_credito
 * @property-read mixed $operacao_debito
 * @property-read mixed $operacao_text
 * @property-read mixed $operacao_todas
 * @method static \Illuminate\Database\Eloquent\Builder|PlanoConta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanoConta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanoConta query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanoConta whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanoConta whereCategoriaPlanoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanoConta whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanoConta whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanoConta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanoConta whereOperacao($value)
 * @mixin \Eloquent
 */
class PlanoConta extends Model
{
    use HasFactory,LogsActivity, HasActivitylogOptions;
    protected static $logFillable = true;
    protected static $logName = 'PlanosConta';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName) {
        $activity->descricao = "";
    }

    protected $table = 'plano_contas';
    protected $fillable = [
        'categoria_plano_id',
        'descricao',
        'operacao',
        'ativo',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'categoria_plano_id' => 'int',
        'descricao' => 'string',
        'operacao' => 'string',
        'ativo' => 'boolean',
    ];

    public $timestamps = false;

    // Operações
    public const OPERACAO_CREDITO = "C";
    public const OPERACAO_DEBITO = "D";
    public const OPERACAO_TODAS = "T";

    protected $appends = [
        'operacaoText',
        'operacaoCredito',
        'operacaoDebito',
        'operacaoTodas'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->empresa_id = auth()->user()->empresa_id;
        });

        static::updating(function ($model) {
            $model->empresa_id = auth()->user()->empresa_id;
        });

        static::addGlobalScope(new ScopeEmpresa());
    }
    //Relacionamentos

    public function Categoria(){
        return $this->hasOne(CategoriaPlanoConta::class,'id','categoria_plano_id');
    }

    public function getOperacaoTextAttribute()
    {
        switch ($this->operacao) {
            case self::OPERACAO_CREDITO:
                return 'Crédito';

            case self::OPERACAO_DEBITO:
                return 'Débito';

            case self::OPERACAO_TODAS:
                return 'Todas';
        }
    }

    public function getOperacaoCreditoAttribute(){
        return $this->operacao == self::OPERACAO_CREDITO ? true:false;
    }

    public function getOperacaoDebitoAttribute(){
        return $this->operacao == self::OPERACAO_DEBITO ? true:false;
    }

    public function getOperacaoTodasAttribute(){
        return $this->operacao == self::OPERACAO_TODAS ? true:false;
    }

    // Valida (TRUE OU FALSE) se a operacao passada por parametro, é valido com a rubrica informada. operacado de debito ou credito para uma rubrica "TODAS" é validado como TRUE
    public static function operacaoValida($ID_PLANO,$operacao){

        $plano = PlanoConta::find($ID_PLANO);

        if( ($plano->operacao_credito || $plano->operacao_todas) && $operacao == self::OPERACAO_CREDITO){
            return TRUE;
        }

        if( ($plano->operacao_debito || $plano->operacao_todas) && $operacao == self::OPERACAO_DEBITO){
            return TRUE;
        }

        return FALSE;

    }

}
