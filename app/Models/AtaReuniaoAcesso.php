<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;

class AtaReuniaoAcesso extends Model
{
    use TenantTrait;

    public const PAPEL_PROPRIETARIO = 'proprietario';
    public const PAPEL_EDITOR = 'editor';
    public const PAPEL_APROVADOR = 'aprovador';
    public const PAPEL_LEITOR = 'leitor';
    public const PAPEL_PARTICIPANTE = 'participante';
    public const PAPEL_RESPONSAVEL_PENDENCIA = 'responsavel_pendencia';

    protected $table = 'ata_reuniao_acessos';

    protected $fillable = [
        'empresa_id',
        'ata_reuniao_id',
        'user_id',
        'papel',
        'origem',
        'expira_em',
        'revogado_em',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'ata_reuniao_id' => 'int',
        'user_id' => 'int',
        'papel' => 'string',
        'origem' => 'string',
        'expira_em' => 'datetime',
        'revogado_em' => 'datetime',
    ];
}
