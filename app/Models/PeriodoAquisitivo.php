<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoAquisitivo extends Model
{
    use HasFactory;

    protected $table = 'periodos_aquisitivos';

    protected $fillable = [
        'label',
        'ano_inicial',
        'ano_final',
    ];
    protected $casts = [
        'label' => 'string',
        'ano_inicial' => 'int',
        'ano_final' => 'int',
    ];

}
