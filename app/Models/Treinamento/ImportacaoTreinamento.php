<?php

namespace App\Models\Treinamento;

use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

class ImportacaoTreinamento extends Model
{
    public const STATUS_PROCESSANDO = 'processando';
    public const STATUS_CONCLUIDA = 'concluida';
    public const STATUS_FALHA = 'falha';

    protected $table = 'importacao_treinamentos';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'uuid',
        'status',
        'total',
        'sucesso',
        'erros',
        'arquivo_sucesso',
        'arquivo_erro',
        'mensagem_erro',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'user_id' => 'int',
        'uuid' => 'string',
        'status' => 'string',
        'total' => 'int',
        'sucesso' => 'int',
        'erros' => 'int',
        'arquivo_sucesso' => 'string',
        'arquivo_erro' => 'string',
        'mensagem_erro' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['created_at_br'];

    public function getCreatedAtBrAttribute(): string
    {
        if (empty($this->attributes['created_at'])) {
            return '';
        }
        $data = new DataHora($this->attributes['created_at']);

        return $data->dataCompleta() . ' ' . $data->hora() . ':' . $data->minuto();
    }
}
