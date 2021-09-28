<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TipoContratacao
 *
 * @property int $id
 * @property int $requisicao_vaga_id
 * @property string $posicao
 * @property string $processo
 * @property string $contrato
 * @property string $local_trabalho
 * @property string $horario
 * @property int|null $gestor_id
 * @property string|null $gestor
 * @property string|null $ppra
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $GestorUser
 * @property-read \App\Models\RequisicaoVaga|null $Requisicao
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao query()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereContrato($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereGestor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereGestorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereHorario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereLocalTrabalho($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao wherePosicao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao wherePpra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereProcesso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereRequisicaoVagaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TipoContratacao extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisicao_vaga_id',
        'posicao',
        'processo',
        'contrato',
        'local_trabalho',
        'horario',
        'gestor_id',
        'gestor',
        'ppra',
    ];

    protected $casts = [
        'id' => 'int',
        'requisicao_vaga_id' => 'int',
        'posicao' => 'string',
        'processo' => 'string',
        'contrato' => 'string',
        'local_trabalho' => 'string',
        'horario' => 'string',
        'gestor_id' => 'int',
        'gestor' => 'string',
        'ppra' => 'string',
    ];

    public function Requisicao()
    {
        return $this->hasOne(RequisicaoVaga::class, 'id', 'requisicao_vaga_id');
    }

    public function GestorUser()
    {
        return $this->hasOne(User::class, 'id', 'gestor_id');
    }

}
