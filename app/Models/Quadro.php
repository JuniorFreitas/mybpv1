<?php

namespace App\Models;

use App\Models\Concerns\HasActivitylogOptions;
use App\Scopes\ScopeEmpresa;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Quadro
 *
 * @property int $id
 * @property int $user_id
 * @property string $titulo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $empresa_id
 * @property int|null $quem_deletou_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Cliente|null $Empresa
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ListaTarefa> $Listas
 * @property-read int|null $listas_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LogWeekly> $Logs
 * @property-read int|null $logs_count
 * @property-read \App\Models\User|null $Usuario
 * @property-read \App\Models\User|null $QuemDeletou
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Quadro newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Quadro newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Quadro query()
 * @method static \Illuminate\Database\Eloquent\Builder|Quadro whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quadro whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quadro whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quadro whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quadro whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quadro whereUserId($value)
 * @mixin \Eloquent
 */
class Quadro extends Model {
    use HasFactory, SoftDeletes, LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'Quadros';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    public $timestamps = true;
    protected $table = 'quadros';
    protected $fillable = [
        'titulo',
        'empresa_id',
        'user_id',
        'quem_deletou_id',
    ];
    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'user_id' => 'int',
        'quem_deletou_id' => 'int',
        'titulo' => 'string',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
        'deleted_at' => 'datetime:d/m/Y à\s H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $user = auth()->user();
            if ($user) {
                $model->empresa_id = $user->empresa_id;
                $model->user_id = $user->id;
            }
        });

        static::created(function (Quadro $model) {
            $userId = (int) $model->user_id;
            if (!$userId) {
                return;
            }
            QuadroMembro::query()->firstOrCreate(
                [
                    'quadro_id' => $model->id,
                    'user_id' => $userId,
                ],
                [
                    'papel' => QuadroMembro::PAPEL_DONO,
                ]
            );
        });

        static::deleting(function (Quadro $model) {
            if ($model->isForceDeleting()) {
                return;
            }
            $userId = (int) auth()->id();
            if (!$userId) {
                return;
            }
            // Grava quem excluiu antes do soft delete
            $model->quem_deletou_id = $userId;
            $model->saveQuietly();
        });

        static::addGlobalScope(new ScopeEmpresa());
    }

    public function getDeletedAtBrAttribute(): ?string
    {
        return $this->deleted_at?->format('d/m/Y \à\s H:i');
    }

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName) {
        $activity->descricao = "";
    }

    public function Empresa() {
        return $this->hasOne(Cliente::class, 'id', 'empresa_id');
    }

    public function Usuario() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function QuemDeletou()
    {
        return $this->belongsTo(User::class, 'quem_deletou_id', 'id');
    }

    public function Membros()
    {
        return $this->belongsToMany(User::class, 'quadros_membros', 'quadro_id', 'user_id')
            ->withPivot(['papel', 'id'])
            ->withTimestamps()
            ->select(['users.id', 'users.nome', 'users.login']);
    }

    public function QuadrosMembros()
    {
        return $this->hasMany(QuadroMembro::class, 'quadro_id', 'id');
    }

    public function Listas() {
        return $this->hasMany(ListaTarefa::class, 'quadro_id', 'id'); // nao colocar orderBy('ordem')
    }
    public function Logs(){
        return $this->hasMany(LogWeekly::class,'quadro_id', 'id')->whereNull('tarefa_id')
            ->with('Usuario:id,nome')
            ->orderByDesc('created_at');
    }

    public function temMembro(?User $user = null): bool
    {
        $userId = (int) ($user?->id ?? auth()->id());
        if (!$userId) {
            return false;
        }

        return $this->QuadrosMembros()->where('user_id', $userId)->exists();
    }

    public function isDono(?User $user = null): bool
    {
        $userId = (int) ($user?->id ?? auth()->id());
        if (!$userId) {
            return false;
        }

        return $this->QuadrosMembros()
            ->where('user_id', $userId)
            ->where('papel', QuadroMembro::PAPEL_DONO)
            ->exists();
    }

    public function papelDoUsuario(?User $user = null): ?string
    {
        $userId = (int) ($user?->id ?? auth()->id());
        if (!$userId) {
            return null;
        }

        return $this->QuadrosMembros()->where('user_id', $userId)->value('papel');
    }
}
