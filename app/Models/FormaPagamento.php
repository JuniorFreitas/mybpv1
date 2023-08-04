<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\FormaPagamento
 *
 * @property int $id
 * @property string $descricao
 * @property bool $ativo
 * @property \datetime|null $created_at
 * @property \datetime|null $updated_at
 * @property int $empresa_id
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|FormaPagamento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormaPagamento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormaPagamento query()
 * @method static \Illuminate\Database\Eloquent\Builder|FormaPagamento whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormaPagamento whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormaPagamento whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormaPagamento whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormaPagamento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormaPagamento whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FormaPagamento extends Model {
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'formas_pagamento';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    protected $table = 'formas_pagamento';
    protected $fillable = [
        //'cliente_id',
        'descricao',
        'ativo',

    ];
    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'descricao' => 'string',
        'ativo' => 'boolean',

        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->empresa_id = auth()->user()->empresa_id;
        });

        static::updating(function ($model) {
            $model->empresa_id = auth()->user()->empresa_id;
        });

        static::addGlobalScope(new ScopeEmpresa());
    }

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName) {
        $activity->descricao = "";
    }
}
