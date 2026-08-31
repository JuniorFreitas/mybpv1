<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Grupo
 *
 * @property int $id
 * @property string $nome
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cliente> $Empresas
 * @property-read int|null $empresas_count
 */
class Grupo extends Model
{
    protected $table = 'grupos';

    protected $fillable = [
        'nome',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function Empresas()
    {
        return $this->hasMany(Cliente::class, 'grupo_id', 'id');
    }

    public function Matriz()
    {
        return $this->hasOne(Cliente::class, 'grupo_id', 'id')->where('matriz', true);
    }
}
