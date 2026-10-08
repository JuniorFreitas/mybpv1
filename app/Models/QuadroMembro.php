<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $quadro_id
 * @property int $user_id
 * @property string $papel
 */
class QuadroMembro extends Model
{
    public const PAPEL_DONO = 'dono';
    public const PAPEL_MEMBRO = 'membro';

    protected $table = 'quadros_membros';

    protected $fillable = [
        'quadro_id',
        'user_id',
        'papel',
    ];

    protected $casts = [
        'id' => 'int',
        'quadro_id' => 'int',
        'user_id' => 'int',
        'papel' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function Quadro(): BelongsTo
    {
        return $this->belongsTo(Quadro::class, 'quadro_id', 'id');
    }

    public function Usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function isDono(): bool
    {
        return $this->papel === self::PAPEL_DONO;
    }
}
