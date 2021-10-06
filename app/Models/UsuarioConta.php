<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioConta extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'banco',
        'agencia',
        'conta',
        'pix',
        'tipochavepix',
        'chavepix',
    ];

    protected $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'banco' => 'string',
        'agencia' => 'string',
        'conta' => 'string',
        'pix' => 'boolean',
        'tipochavepix' => 'string',
        'chavepix' => 'string',
    ];
}
