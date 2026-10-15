<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtapaTipo extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'EtapaTipo';

    protected $table = 'etapa_tipos';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $fillable = [
        'nome',
        'cliente_id',
        'vaga_id',
        'ordem',
        'chave',
        'tipo',
        'obrigatoria',
        'depende_de_id',
        'is_apto_admissao',
        'permite_email',
        'habilidade',
        'ativo',
    ];

    protected $casts = [
        'nome' => 'string',
        'cliente_id' => 'int',
        'vaga_id' => 'int',
        'ordem' => 'int',
        'chave' => 'string',
        'tipo' => 'string',
        'obrigatoria' => 'boolean',
        'depende_de_id' => 'int',
        'is_apto_admissao' => 'boolean',
        'permite_email' => 'boolean',
        'habilidade' => 'string',
        'ativo' => 'boolean',
    ];

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }

    public function Vaga()
    {
        return $this->belongsTo(Vaga::class, 'vaga_id', 'id');
    }

    public function DependeDe()
    {
        return $this->belongsTo(self::class, 'depende_de_id', 'id');
    }
}
