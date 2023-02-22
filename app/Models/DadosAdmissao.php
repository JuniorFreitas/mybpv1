<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\DadosAdmissao
 *
 * @property int $id
 * @property int|null $admissao_id
 * @property string|null $ctps_numero
 * @property string|null $ctps_serie
 * @property string|null $ctps_data_emissao
 * @property string|null $titulo_eleitor_numero
 * @property string|null $titulo_eleitor_sessao
 * @property string|null $titulo_eleitor_zona
 * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao query()
 * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereAdmissaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereCtpsDataEmissao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereCtpsNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereCtpsSerie($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereTituloEleitorNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereTituloEleitorSessao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereTituloEleitorZona($value)
 * @mixin \Eloquent
 * @property string|null $ctps_uf
 * @property string|null $cert_reservista_num
 * @property string|null $cert_reservista_categoria
 * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereCertReservistaCategoria($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereCertReservistaNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereCtpsUf($value)
 */
class DadosAdmissao extends Model
{
    use HasFactory;

    protected $fillable = [
        'admissao_id',
        'ctps_numero',
        'ctps_serie',
        'ctps_data_emissao',
        'titulo_eleitor_numero',
        'titulo_eleitor_sessao',
        'titulo_eleitor_zona',
        'ctps_uf',
        'cert_reservista_num',
        'cert_reservista_categoria',
    ];

    protected $casts = [
        'admissao_id' => 'int',
        'ctps_numero' => 'string',
        'ctps_serie' => 'string',
        'ctps_data_emissao' => 'string',
        'titulo_eleitor_numero' => 'string',
        'titulo_eleitor_sessao' => 'string',
        'titulo_eleitor_zona' => 'string',
        'ctps_uf' => 'string',
        'cert_reservista_num' => 'string',
        'cert_reservista_categoria' => 'string',
    ];

    public $timestamps = false;

    //Acessor ->ctps_data_emissao
    public function getCtpsDataEmissaoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['ctps_data_emissao']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->ctps_data_emissao
    public function setCtpsDataEmissaoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['ctps_data_emissao'] = $data->dataInsert();
        }
    }

//    public function Admissao()
//    {
//        return $this->hasOne(Admissao::class, 'id','admissao_id');
//    }

}
