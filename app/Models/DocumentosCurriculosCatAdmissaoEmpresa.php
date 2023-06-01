<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentosCurriculosCatAdmissaoEmpresa extends Model
{
    use HasFactory;

    protected $table = 'documentos_curriculos_adm_empresa';

    protected $fillable = [
        'empresa_id',
        'label',
        'ativo',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'label' => 'string',
        'ativo' => 'boolean',
    ];

}
