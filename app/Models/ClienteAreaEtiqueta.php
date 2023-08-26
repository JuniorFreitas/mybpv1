<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ClienteAreaEtiqueta
 *
 * @property int $cliente_id
 * @property int $area_etiqueta_id
 * @property string|null $numero_supervisor
 * @property mixed $0
 * @property mixed $1
 * @property mixed $2
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteAreaEtiqueta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteAreaEtiqueta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteAreaEtiqueta query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteAreaEtiqueta whereAreaEtiquetaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteAreaEtiqueta whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteAreaEtiqueta whereNumeroSupervisor($value)
 * @mixin \Eloquent
 */
class ClienteAreaEtiqueta extends Model
{
    use HasFactory;
    protected $table = 'cliente_area_etiquetas';

    protected $casts = ['cliente_id', 'area_etiqueta_id', 'numero_supervisor'];
}
