<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

/**
 * App\Models\Etapas
 *
 * @property int $id
 * @property int|null $feedback_id
 * @property int $user_id
 * @property int $vaga_id
 * @property string $etapa
 * @property bool|null $enviado_email
 * @property string|null $text_email
 * @property string|null $observacao
 * @property string $status classificado,desclassificado,andamento
 * @property string|null $preenchido_por
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $passo_id é o id da etapa_tipo
 * @property-read \App\Models\Curriculo|null $Curriculo
 * @method static \Illuminate\Database\Eloquent\Builder|Etapas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Etapas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Etapas query()
 * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereEnviadoEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereEtapa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereObservacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Etapas wherePassoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Etapas wherePreenchidoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereTextEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereVagaId($value)
 * @mixin \Eloquent
 */
class Etapas extends Model
{
    protected $fillable = [
        'feedback_id',
        'curriculo_id',
        'user_id',
        'vaga_id',
        'etapa',
        'enviado_email',
        'text_email',
        'observacao',
        'preenchido_por',
        'status',
    ];

    protected $casts = [
        'feedback_id' => 'int',
        'curriculo_id' => 'int',
        'user_id' => 'int',
        'vaga_id' => 'int',
        'etapa' => 'string',
        'enviado_email' => 'boolean',
        'text_email' => 'string',
        'observacao' => 'string',
        'preenchido_por' => 'string',
        'status' => 'string',
    ];

    //Acessor ->data_nr_trinta_tres
    public function getCreatedAtAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['created_at']);
            return $data->dataCompleta() . ' às ' . $data->hora() . ':' . $data->minuto() . 'hs';
        }
    }

    public static function classificar($dados = [])
    {
        try {
            \DB::beginTransaction();
           $etapa = Etapas::create([
                'curriculo_id' => $dados['curriculo_id'],
                'user_id' => auth()->id(),
                'vaga_id' => $dados['vaga_id'],
                'etapa' => $dados['etapa'],
                'enviado_email' => true,
                'text_email' => $dados['mensagem'],
                'observacao' => $dados['observacao'],
                'preenchido_por' => $dados['preenchido_por'],
                'status' => $dados['status'],
            ]);

            \DB::commit();

//            if ($dados['status'] == 'desclassificado') {
//                \Mail::send(new DesclassificacaoMail([
//                    'nome' => $dados['nome'],
//                    'email' => $dados['email']
//                ]));
//            } else {
//                \Mail::send(new EtapaProvaManualMail([
//                    'nome' => $dados['nome'],
//                    'email' => $dados['email'],
//                    'mensagem' => $dados['mensagem']
//                ]));
//            }

            return response()->json([$etapa], 201);
        } catch (\Exception $e) {

            \Log::debug("error ao modificar Etapa:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome);
            DB::rollback();
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function Curriculo()
    {
        return $this->hasOne(Curriculo::class, 'id', 'curriculo_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($etapa) {
            FeedbackCurriculo::whereCurriculoId($etapa->curriculo_id)->whereVagaId($etapa->vaga_id)->first()->update(['status' => $etapa->status]);
        });
    }
}
