<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;

class AuditoriaInterna extends Model
{
    use TenantTrait;

    protected $table = 'auditoria_internas';
    protected $fillable = [
        'empresa_id',
        'usuario_id',
        'feedback_id',
        'colaborador_id',
        'tipo',
        'descricao',
        'dados'
    ];

    protected $casts = [
        'empresa_id' => 'int',
        'usuario_id' => 'int',
        'feedback_id' => 'int',
        'colaborador_id' => 'int',
        'tipo' => 'string',
        'descricao' => 'string',
        'dados' => 'json'
    ];

    const TIPOREMOCAODEMISSAO = 'remocao_demissao';

    public function empresa()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function DTO()
    {
        return [
            'id' => '',
            'empresa_id' => '',
            'usuario_id' => '',
            'feedback_id' => '',
            'colaborador_id' => '',
            'tipo' => '',
            'descricao' => '',
            'dados' => [
                'nome' => '',
                'cpf' => "",
                'vaga' => "",
                'cargo' => "",
                'funcao' => "",
                'data_admissao' => "",
                'data_demissao' => "",
                'autenticado_nome' => "",
                'termo' => "",
                'motivo' => "",
                'token' => "",
            ]
        ];
    }

    public function setDTO($data)
    {
        $this->empresa_id = $data['empresa_id'];
        $this->usuario_id = $data['usuario_id'];
        $this->feedback_id = $data['feedback_id'];
        $this->colaborador_id = $data['colaborador_id'];
        $this->tipo = $data['tipo'];
        $this->descricao = $data['descricao'];
        $this->dados = $data['dados'];
    }

}
