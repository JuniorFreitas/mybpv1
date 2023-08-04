<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\ItensCloud
 *
 * @property int $id
 * @property int $cloud_id
 * @property int|null $arquivo_id
 * @property string $label
 * @property string $tipo
 * @property int|null $pertence
 * @property int $quem_criou
 * @property bool $aprovado
 * @property int|null $quem_aprovou
 * @property mixed|null $data_aprovacao
 * @property bool $revisado
 * @property int|null $quem_revisou
 * @property mixed|null $data_revisao
 * @property int|null $quem_editou
 * @property int|null $quem_excluiu
 * @property mixed|null $created_at
 * @property mixed|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property bool $movido
 * @property int|null $quem_moveu
 * @property mixed|null $data_movido
 * @property int|null $pertence_anterior
 * @property-read \App\Models\User|null $Aprovou
 * @property-read \App\Models\Arquivo|null $Arquivo
 * @property-read \App\Models\Cloud|null $Cloud
 * @property-read \App\Models\User|null $Criou
 * @property-read \App\Models\User|null $Editou
 * @property-read \App\Models\User|null $Excluiu
 * @property-read \Illuminate\Database\Eloquent\Collection|ItensCloud[] $Itens
 * @property-read int|null $itens_count
 * @property-read \App\Models\User|null $Moveu
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GrupoCloud[] $Permissoes
 * @property-read int|null $permissoes_count
 * @property-read ItensCloud|null $Pertence
 * @property-read ItensCloud|null $PertenceAntes
 * @property-read \App\Models\User|null $Revisou
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read mixed $tem_permissao
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud newQuery()
 * @method static \Illuminate\Database\Query\Builder|ItensCloud onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud query()
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereAprovado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereArquivoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereCloudId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereDataAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereDataMovido($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereDataRevisao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereMovido($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud wherePertence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud wherePertenceAnterior($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereQuemAprovou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereQuemCriou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereQuemEditou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereQuemExcluiu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereQuemMoveu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereQuemRevisou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereRevisado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|ItensCloud withTrashed()
 * @method static \Illuminate\Database\Query\Builder|ItensCloud withoutTrashed()
 * @mixin \Eloquent
 */
class ItensCloud extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'itens_cloud';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $table = 'itens_cloud';

    protected $fillable = [
        'cloud_id',
        'arquivo_id',
        'label',
        'tipo',
        'pertence',
        'quem_criou',
        'aprovado',
        'quem_aprovou',
        'data_aprovacao',
        'revisado',
        'quem_revisou',
        'data_revisao',
        'quem_editou',
        'quem_excluiu',
        'movido',
        'quem_moveu',
        'data_movido',
        'pertence_anterior',
    ];

    protected $casts = [
        'id' => 'int',
        'cloud_id' => 'int',
        'arquivo_id' => 'int',
        'label' => 'string',
        'tipo' => 'string',
        'pertence' => 'int', //pertence a qual pasta
        'quem_criou' => 'int',
        'aprovado' => 'boolean',
        'quem_aprovou' => 'int',
        'data_aprovacao' => 'date:d/m/Y \\à\\s H:m\\h',
        'revisado' => 'boolean',
        'quem_revisou' => 'int',
        'data_revisao' => 'date:d/m/Y \\à\\s H:m\\h',
        'quem_editou' => 'int',
        'quem_excluiu' => 'int',
        'created_at' => 'date:d/m/Y \\à\\s H:m\\h',
        'updated_at' => 'date:d/m/Y \\à\\s H:m\\h',
        'movido' => 'boolean',
        'quem_moveu' => 'int',
        'data_movido' => 'date:d/m/Y \\à\\s H:m\\h',
        'pertence_anterior' => 'int',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

//    //Acessor ->created
//    public function getCreatedAtAttribute($value)
//    {
//        $data = new DataHora($this->attributes['created_at']);
//        return $data->dataCompleta() . ' ' . $data->hora() . ':' . $data->minuto() . 'h';
//    }
//
//    //Acessor ->updated
//    public function getUpdatedAtAttribute($value)
//    {
//        $data = new DataHora($this->attributes['updated_at']);
//        return $data->dataCompleta() . ' ' . $data->hora() . ':' . $data->minuto() . 'h';
//    }


    public function Cloud()
    {
        return $this->hasOne(Cloud::class, 'id', 'cloud_id');
    }

    public function Itens()
    {
        return $this->hasMany(ItensCloud::class, 'pertence', 'id');
    }

    public function Pertence()
    {
        return $this->hasOne(ItensCloud::class, 'id', 'pertence');
    }

    public function PertenceAntes()
    {
        return $this->hasOne(ItensCloud::class, 'id', 'pertence_anterior');
    }

    public function Criou()
    {
        return $this->hasOne(User::class, 'id', 'quem_criou');
    }

    public function Editou()
    {
        return $this->hasOne(User::class, 'id', 'quem_editou');
    }

    public function Aprovou()
    {
        return $this->hasOne(User::class, 'id', 'quem_aprovou');
    }

    public function Revisou()
    {
        return $this->hasOne(User::class, 'id', 'quem_revisou');
    }

    public function Moveu()
    {
        return $this->hasOne(User::class, 'id', 'quem_moveu');
    }

    public function Excluiu()
    {
        return $this->hasOne(User::class, 'id', 'quem_excluiu');
    }

    public function Permissoes()
    {
        return $this->belongsToMany(GrupoCloud::class, 'permissoes_itens_clouds', 'item_id', 'grupo_cloud_id');
    }

    public function getTemPermissaoAttribute()
    {
        return $this->Permissoes()->whereIn('grupo_cloud_id', [auth()->user()->GrupoCloud->id])->count() > 0 ? true : false;
    }

    public function Arquivo()
    {
        return $this->hasOne(Arquivo::class, 'id', 'arquivo_id');
    }

    public function recursivo($permissoes)
    {
        $listaItens = $this->wherePertence($this->id)->select('id', 'pertence')->get();

        foreach ($listaItens as $outroItem) {
            $outroItem->Permissoes()->sync($permissoes);
            $outroItem->recursivo($permissoes);
        }

    }

    public function deleteRecursivo()
    {
        ItensCloud::wherePertence($this->id)->get()
            ->each(function (ItensCloud $item) {
                $item->update(['quem_excluiu' => auth()->id()]);
                $item->delete();
                $item->deleteRecursivo();
            });
    }

}
