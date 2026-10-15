<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmpresaApiCredencial extends Model
{
    use SoftDeletes;

    protected $table = 'empresa_api_credenciais';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'client_id',
        'client_secret_hash',
        'scopes',
        'ativo',
        'last_used_at',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'user_id' => 'int',
        'client_id' => 'string',
        'client_secret_hash' => 'string',
        'scopes' => 'array',
        'ativo' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = [
        'client_secret_hash',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'empresa_id');
    }
}
