<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\Models\VagaProjeto
 *
 * @property int $id
 * @property int $empresa_id
 * @property int $projeto_id
 * @property int $vaga_aberta_id
 * @property int $qnt_total
 * @property int $qnt_preenchida
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FeedbackCurriculo[] $Feedbacks
 * @property-read int|null $feedbacks_count
 * @property-read \App\Models\Projeto|null $Projeto
 * @property-read \App\Models\VagasAbertas|null $VagaAberta
 * @property-read mixed $tem_vaga
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|VagaProjeto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VagaProjeto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VagaProjeto query()
 * @method static \Illuminate\Database\Eloquent\Builder|VagaProjeto whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VagaProjeto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VagaProjeto whereProjetoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VagaProjeto whereQntPreenchida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VagaProjeto whereQntTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VagaProjeto whereVagaAbertaId($value)
 * @mixin \Eloquent
 */
class VagaProjeto extends Model
{
    use HasFactory;
    use HasApiTokens;
    use TenantTrait;

    protected $table = 'vaga_projetos';

    protected $fillable = [
        'projeto_id',
        'vaga_aberta_id',
        'qnt_total',
        'qnt_preenchida',
        'empresa_id'
    ];

    protected $casts = [
        'projeto_id' => 'int',
        'vaga_aberta_id' => 'int',
        'qnt_total' => 'int',
        'qnt_preenchida' => 'int',
        'empresa_id' => 'int',
    ];

    protected $appends = ['tem_vaga'];

    public function getTemVagaAttribute()
    {
        return $this->qnt_total >= $this->qnt_preenchida;
    }

    public $timestamps = false;

    public function Projeto()
    {
        return $this->hasOne(Projeto::class, 'id', 'projeto_id');
    }

    public function VagaAberta()
    {
        return $this->hasOne(VagasAbertas::class, 'id', 'vaga_aberta_id');
    }

    public function Feedbacks()
    {
        return $this->hasMany(FeedbackCurriculo::class, 'vaga_projeto_id', 'id');
    }
}
