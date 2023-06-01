<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DocumentosCurriculosAdmissaoEmpresa extends Model
{
    use HasFactory;

    protected $table = 'documentos_curriculos_adm_empresa';

    protected $fillable = [
        'empresa_id',
        'categoria_id',
        'label',
        'metodo',
        'descricao',
        'tipo',
        'url_arquivo',
        'configuracoes',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'categoria_id' => 'int',
        'label' => 'string',
        'metodo' => 'string',
        'descricao' => 'string',
        'tipo' => 'string',
        'url_arquivo' => 'string',
        'configuracoes' => 'json',
        'ordem' => 'int',
        'ativo' => 'boolean',
    ];

    public function getConfiguracoesAttribute($value)
    {
        return json_decode($value, 1);
    }

    public static function getDocumentoCurriculoAdmissaoEmpresa($empresa_id)
    {
        $docAdmEmpresa = \Cache::forget('docAdmEmpresa_' . $empresa_id);
        $docAdmEmpresa = \Cache::get('docAdmEmpresa_' . $empresa_id);

        if (!$docAdmEmpresa) {
            $docAdmEmpresa = DocumentosCurriculosAdmissaoEmpresa::where('documentos_curriculos_adm_empresa.empresa_id', $empresa_id)
                ->whereAtivo(true)
                ->orderBy('documentos_curriculos_adm_empresa.categoria_id')
                ->orderBy('documentos_curriculos_adm_empresa.ordem')
                ->get()
                ->transform(function ($doc) {
                    $doc->categoria = DB::table('documentos_curriculos_cat_adm_empresa')->select(['label'])->where('id', $doc->categoria_id)->first()->label;
                    return $doc;
                });
            \Cache::put('docAdmEmpresa_' . $empresa_id, $docAdmEmpresa, now()->addHours(168));
        }

        return $docAdmEmpresa;

    }
}
