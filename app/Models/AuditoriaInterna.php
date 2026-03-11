<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AuditoriaInterna
 *
 * @property int $id
 * @property int $empresa_id
 * @property int $usuario_id
 * @property int|null $feedback_id
 * @property int $colaborador_id
 * @property string $tipo
 * @property string $descricao
 * @property array $dados
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cliente $empresa
 * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna query()
 * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereColaboradorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereDados($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereUsuarioId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class AuditoriaInterna extends Model
{
    use LogsActivity, HasActivitylogOptions, TenantTrait;

    protected static $logName = 'AuditoriaInterna';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $table = 'auditoria_internas';
    protected $fillable = [
        'empresa_id',
        'usuario_id',
        'feedback_id',
        'colaborador_id',
        'tipo',
        'descricao',
        'dados'
    ];

    protected $casts = [
        'empresa_id' => 'int',
        'usuario_id' => 'int',
        'feedback_id' => 'int',
        'colaborador_id' => 'int',
        'tipo' => 'string',
        'descricao' => 'string',
        'dados' => 'json'
    ];

    const TIPOREMOCAODEMISSAO = 'remocao_demissao';

    public function empresa()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function DTO()
    {
        return [
            'id' => '',
            'empresa_id' => '',
            'usuario_id' => '',
            'feedback_id' => '',
            'colaborador_id' => '',
            'tipo' => '',
            'descricao' => '',
            'dados' => [
                'nome' => '',
                'cpf' => "",
                'vaga' => "",
                'cargo' => "",
                'funcao' => "",
                'data_admissao' => "",
                'data_demissao' => "",
                'autenticado_nome' => "",
                'termo' => "",
                'motivo' => "",
                'token' => "",
            ]
        ];
    }

    public function setDTO($data)
    {
        $this->empresa_id = $data['empresa_id'];
        $this->usuario_id = $data['usuario_id'];
        $this->feedback_id = $data['feedback_id'];
        $this->colaborador_id = $data['colaborador_id'];
        $this->tipo = $data['tipo'];
        $this->descricao = $data['descricao'];
        $this->dados = $data['dados'];
    }

}
