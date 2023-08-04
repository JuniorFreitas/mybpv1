<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TipoContratacao
 *
 * @property int $id
 * @property int $requisicao_vaga_id
 * @property string $posicao
 * @property string $processo
 * @property string|null $nome_indicacao
 * @property string $contrato
 * @property string|null $local_trabalho
 * @property string $horario
 * @property int|null $gestor_id
 * @property string|null $gestor
 * @property bool|null $ppra
 * @property string|null $salario
 * @property float|null $salario_valor
 * @property string|null $beneficio
 * @property string|null $beneficio_excecao
 * @property string|null $treinamento
 * @property string|null $treinamento_excecao
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User|null $GestorAprovacao
 * @property-read \App\Models\RequisicaoVaga|null $Requisicao
 * @property-read mixed $salario_valor_format
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao query()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereBeneficio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereBeneficioExcecao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereContrato($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereGestor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereGestorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereHorario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereLocalTrabalho($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereNomeIndicacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao wherePosicao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao wherePpra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereProcesso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereRequisicaoVagaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereSalario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereSalarioValor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereTreinamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereTreinamentoExcecao($value)
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
        'nome_indicacao',
        'contrato',
        'horario',
        'gestor_id',
        'gestor',
        'ppra',
        'salario',
        'salario_valor',
        'beneficio',
        'beneficio_excecao',
        'treinamento',
        'treinamento_excecao',
    ];

    protected $casts = [
        'id' => 'int',
        'requisicao_vaga_id' => 'int',
        'posicao' => 'string',
        'processo' => 'string',
        'nome_indicacao' => 'string',
        'contrato' => 'string',
        'horario' => 'string',
        'gestor_id' => 'int',
        'gestor' => 'string',
        'ppra' => 'boolean',
        'salario' => 'string',
        'salario_valor' => 'float',
        'beneficio' => 'string',
        'beneficio_excecao' => 'string',
        'treinamento' => 'string',
        'treinamento_excecao' => 'string',
    ];

    protected $appends = ['salario_valor_format'];


    public function getSalarioValorFormatAttribute($value)
    {
        if (!is_null($this->attributes['salario_valor'])) {
            return number_format($this->attributes['salario_valor'], 2, ',', '.');
        }
    }

    public function setSalarioValorAttribute($value)
    {
        if ($value) {
            $this->attributes['salario_valor'] = Sistema::DinheiroInsert($value);
        }
    }

    public function Requisicao()
    {
        return $this->hasOne(RequisicaoVaga::class, 'id', 'requisicao_vaga_id');
    }

    public function GestorAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'gestor_id');
    }
}
