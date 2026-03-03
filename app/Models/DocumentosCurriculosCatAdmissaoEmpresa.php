<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\DocumentosCurriculosCatAdmissaoEmpresa
 *
 * @property int $id
 * @property int $empresa_id
 * @property int|null $categoria_id
 * @property string $label
 * @property string|null $metodo
 * @property string|null $descricao
 * @property string $tipo
 * @property string|null $url_arquivo
 * @property mixed|null $configuracoes
 * @property int $ordem
 * @property bool $ativo
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa query()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereCategoriaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereConfiguracoes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereMetodo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereOrdem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereUrlArquivo($value)
 * @mixin \Eloquent
 */
class DocumentosCurriculosCatAdmissaoEmpresa extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'DocumentosCurriculosCatAdmissaoEmpresa';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

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
