<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\LancamentoForma
 *
 * @property int $id
 * @property int $lancamento_id
 * @property int $forma_pagamento_id
 * @property float $valor
 * @property string|null $observacoes
 * @property-read \App\Models\FormaPagamento|null $FormaPagamento
 * @property-read \App\Models\Lancamento $Lancamento
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read mixed $valor_format
 * @method static \Illuminate\Database\Eloquent\Builder|LancamentoForma newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LancamentoForma newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LancamentoForma query()
 * @method static \Illuminate\Database\Eloquent\Builder|LancamentoForma whereFormaPagamentoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LancamentoForma whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LancamentoForma whereLancamentoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LancamentoForma whereObservacoes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LancamentoForma whereValor($value)
 * @mixin \Eloquent
 */
class LancamentoForma extends Model
{
    use HasFactory,LogsActivity;
    protected static $logFillable = true;
    protected static $logName = 'lancamentos_formas';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    public $timestamps = false;
    protected $table = 'lancamento_formas';
    protected $fillable = [
        'id',
        'lancamento_id',
        'valor',
        'forma_pagamento_id',
        'observacoes',
    ];
    protected $casts = [
        'id' => 'int',
        'lancamento_id' => 'int',
        'valor' => 'float',
        'forma_pagamento_id' => 'int',
        'observacoes' => 'string',
    ];
    protected $appends = [
        //'formaText',
        'valorFormat'
    ];

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName) {
        $activity->descricao = "";
    }

    public static function cadastrar($ID_LANCAMENTO,$valor,$FORMA_PAGAMENTO_ID,$observacao){

        return LancamentoForma::create([
            'lancamento_id'=> $ID_LANCAMENTO,
            'valor'=> $valor,
            'forma_pagamento_id'=> $FORMA_PAGAMENTO_ID,
            'observacoes'=> $observacao,
        ]);


    }

    public static function alterar($ID_FORMA,$valor,$FORMA_PAGAMENTO_ID,$observacao){

        $formaLancamento = LancamentoForma::find($ID_FORMA);

        $formaLancamento->valor= $valor;
        $formaLancamento->forma_pagamento_id = $FORMA_PAGAMENTO_ID;
        $formaLancamento->observacoes = $observacao;
        $formaLancamento->save();


        return $formaLancamento;

    }

    //Relacionamentos --------------------------


    //Mutators ----------------------------------

    public function Lancamento(){
        return $this->belongsTo(Lancamento::class,'lancamento_id','id');
    }

    // ##################################### ESPECIAIS ##############################

    public function FormaPagamento(){
        return $this->hasOne(FormaPagamento::class,'id','forma_pagamento_id');
    }

    public function getValorFormatAttribute(){
        return number_format($this->valor,2,',','.');
    }
}
