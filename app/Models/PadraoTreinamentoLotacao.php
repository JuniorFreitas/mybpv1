<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Lotação (Matriz ou Filial) em que o padrão é válido.
 *
 * @property int $id
 * @property int $padrao_treinamento_id
 * @property bool $is_matriz
 * @property int|null $cliente_filial_id
 * @property string $lotacao_key
 */
class PadraoTreinamentoLotacao extends Model
{
    protected $table = 'padrao_treinamento_lotacao';

    protected $fillable = [
        'padrao_treinamento_id',
        'is_matriz',
        'cliente_filial_id',
        'lotacao_key',
    ];

    protected $casts = [
        'id' => 'int',
        'padrao_treinamento_id' => 'int',
        'is_matriz' => 'boolean',
        'cliente_filial_id' => 'int',
    ];

    public static function keyMatriz(): string
    {
        return 'matriz';
    }

    public static function keyFilial(int $clienteFilialId): string
    {
        return 'f:' . $clienteFilialId;
    }

    protected static function booted()
    {
        static::saving(function (self $model) {
            if ($model->is_matriz) {
                $model->cliente_filial_id = null;
                $model->lotacao_key = self::keyMatriz();
            } else {
                $model->is_matriz = false;
                $model->lotacao_key = self::keyFilial((int) $model->cliente_filial_id);
            }
        });
    }

    public function PadraoTreinamento()
    {
        return $this->belongsTo(PadraoTreinamento::class, 'padrao_treinamento_id', 'id');
    }

    public function ClienteFilial()
    {
        return $this->belongsTo(ClienteFilial::class, 'cliente_filial_id', 'id');
    }
}
