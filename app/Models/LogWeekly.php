<?php

namespace App\Models;

use App\Events\WeeklyReport\ListaEvent;
use App\Events\WeeklyReport\LogWeeklyEvent;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;

/**
 * App\Models\LogWeekly
 *
 * @property int $id
 * @property int $quadro_id
 * @property int|null $tarefa_id
 * @property int $user_id
 * @property string $descricao
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Quadro|null $Quadro
 * @property-read \App\Models\User|null $Usuario
 * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly query()
 * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly whereQuadroId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly whereTarefaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly whereUserId($value)
 * @mixin \Eloquent
 */
class LogWeekly extends Model
{
    use HasFactory;
    protected $table = 'log_weekly';
    protected $fillable = [
        'quadro_id',
        'tarefa_id',
        //'user_id',
        'descricao',
    ];
    protected $casts = [
        'id' => 'int',
        'quadro_id' => 'int',
        'tarefa_id' => 'int',
        'user_id' => 'int',
        'descricao' => 'string',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
    ];

    protected $with=['Usuario'];

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->user_id = auth()->user()->id;
        });

        static::created(function ($model) {
            Event::dispatch(new LogWeeklyEvent($model));
        });
    }

    public function Quadro(){
        return $this->hasOne(Quadro::class,'id','quadro_id');
    }

    public function Usuario(){
        return $this->hasOne(User::class,'id','user_id');
    }

}
