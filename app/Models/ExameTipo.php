<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ExameTipo
 *
 * @property int $id
 * @property string $label
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ExameTipo extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'ativo'
    ];
    protected $casts = [
        'id' => 'int',
        'label' => 'string',
        'ativo' => 'boolean'
    ];
}
