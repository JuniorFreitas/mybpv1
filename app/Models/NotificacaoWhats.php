<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\NotificacaoWhats
 *
 * @property int $id
 * @property int $curriculo_id
 * @property int $vaga_id
 * @property int $etapa_id
 * @property int $messageid
 * @property int $user_id
 * @property string|null $mensagem
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NotificacaoWhats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NotificacaoWhats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NotificacaoWhats query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NotificacaoWhats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NotificacaoWhats whereCurriculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NotificacaoWhats whereEtapaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NotificacaoWhats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NotificacaoWhats whereMensagem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NotificacaoWhats whereMessageid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NotificacaoWhats whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NotificacaoWhats whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NotificacaoWhats whereVagaId($value)
 * @mixin \Eloquent
 * @property int|null $feedback_id
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats whereFeedbackId($value)
 */
class NotificacaoWhats extends Model
{
    protected $fillable = [
        'curriculo_id',
        'feedback_id',
        'vaga_id',
        'etapa_id',
        'messageid',
        'mensagem',
        'user_id',
    ];

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
