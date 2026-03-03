<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\NotificacaoWhats
 *
 * @property int $id
 * @property int|null $feedback_id
 * @property int $vaga_id
 * @property int $etapa_id
 * @property int $messageid
 * @property int $user_id
 * @property string|null $mensagem
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats query()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats whereEtapaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats whereMensagem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats whereMessageid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats whereVagaId($value)
 * @mixin \Eloquent
 */
class NotificacaoWhats extends Model
{

    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'NotificacaoWhats';
    protected $fillable = [
        'curriculo_id',
        'feedback_id',
        'vaga_id',
        'etapa_id',
        'messageid',
        'mensagem',
        'user_id',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $casts = [
        'curriculo_id' => 'int',
        'feedback_id' => 'int',
        'vaga_id' => 'int',
        'etapa_id' => 'int',
        'messageid' => 'int',
        'mensagem' => 'string',
        'user_id' => 'int',
    ];

    public static function sendNotificacaoAptoAdmissao($dados = [], $msg = null)
    {
        $whatsMsg = new \MasterTag\ZapMEApi();
        $whatsMsg->api = env('API_ZAPME');
        $whatsMsg->secret = env('SECRET_ZAPME');
        $whatsMsg->method = 'sendmessage';

        $whatsMsg->phone = $dados['fone'];

        if (is_null($msg)) {
            $msg = "*Parabéns, {$dados['nome']}!!!* Chegou e-mail na sua caixa de entrada sobre a documentação que deve providenciar para a fase de admissão no processo seletivo da 55 soluções.
            De já Ratificamos que NÃO precisará providenciar a CARTA DE SINDICALIZAÇÃO que consta no anexo do checklist";
        }

        $whatsMsg->message = $msg;

        $return = $whatsMsg->Run();

        if ($return['result'] === 'success' && $return['status_result'] === 'message_queued') {
            NotificacaoWhats::create([
                'curriculo_id' => $dados['curriculo_id'],
                'vaga_id' => $dados['vaga_id'],
                'etapa_id' => $dados['etapa_id'],
                'messageid' => $return['messageid'],
                'mensagem' => $msg,
                'user_id' => auth()->id(),
            ]);
//            echo 'Mensagem enviada!';
        } else {
//            echo $return['message'];
        }
    }

}
