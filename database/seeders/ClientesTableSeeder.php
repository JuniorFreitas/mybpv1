<?php

namespace Database\Seeders;

use App\Models\Cliente;
use DB;
use Exception;
use Illuminate\Database\Seeder;
use MasterTag\DataHora;

class ClientesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        $lista[] = ['nome' => 'Suporte', 'descricao' => 'Tipo de usuário usado pelos desenvolvedores do sistema', 'email' => 'atendimento@mastertag.com.br', 'ativo' => 's'];
//        $lista[] = ['nome' => 'Administrador', 'descricao' => 'Tipo de usuário administrador do sistema', 'email' => 'diretoria@bpse.com.br', 'ativo' => 's'];
//
//        foreach ($lista as $papel) {
//            Papel::create($papel);
//        }

        try {
            DB::beginTransaction();
            $table = DB::connection('mysql_antes')
                ->table('clientes')->first();
            foreach ($table as $tabela) {
                $cliente = Cliente::create([
                    'id' => $tabela->id,
                    'tipo_cliente' => !isset($tabela->tipo_cliente) ? null : $tabela->tipo_cliente ,
                    'cnpj' => !isset($tabela->cnpj) ? null : $tabela->cnpj ,
                    'cpf' => !isset($tabela->cpf) ? null : $tabela->cpf ,
                    'nome' => !isset($tabela->nome) ? null : $tabela->nome,
                    'tipo' => !isset($tabela->tipo) ? null : $tabela->tipo,
                    'razao_social' => !isset($tabela->razao_social) ? null : $tabela->razao_social,
                    'nome_fantasia' => !isset($tabela->nome_fantasia) ? null : $tabela->nome_fantasia,
                    'area_id' => !isset($tabela->area_id) ? null : $tabela->area_id,
                    'ramo' => !isset($tabela->ramo) ? null : $tabela->ramo,
                    'cep' => !isset($tabela->cep) ? null : $tabela->cep,
                    'logradouro' => !isset($tabela->logradouro) ? null : $tabela->logradouro,
                    'numero' => !isset($tabela->numero) ? null : $tabela->numero,
                    'complemento' => !isset($tabela->complemento) ? null : $tabela->complemento,
                    'bairro' => !isset($tabela->bairro) ? null : $tabela->bairro,
                    'municipio' => !isset($tabela->municipio) ? null : $tabela->municipio,
                    'uf' => !isset($tabela->uf) ? null : $tabela->uf,
                    'contato' => !isset($tabela->contato) ? null : $tabela->contato,
                    'email' => !isset($tabela->email) ? null : $tabela->email,
                    'aniversario' => !isset($tabela->aniversario) ? null : $tabela->aniversario,
                    'como_conheceu' => !isset($tabela->como_conheceu) ? null : $tabela->como_conheceu,
                    'como_conheceu_outro' => !isset($tabela->como_conheceu_outro) ? null : $tabela->como_conheceu_outro,
                    'apelido' => !isset($tabela->apelido) ? null : $tabela->apelido,
                    'tel_principal' => !isset($tabela->tel_principal) ? null : $tabela->tel_principal,
                    'politica_ehs' => !isset($tabela->politica_ehs) ? null : $tabela->politica_ehs,
                    'ativo' => !isset($tabela->ativo) ? null : $tabela->ativo,
//                    'created_at' => !isset($tabela->created_at) ? null : $tabela->created_at,
//                    'updated_at' => !isset($tabela->updated_at) ? null : $tabela->updated_at,
                ]);

                $cliente_logotipo = DB::connection('mysql_antes')
                    ->table('cliente_logotipo')->get();

                foreach ($cliente_logotipo as $foto) {
                    $cliente->Logo->attach($foto->arquivo_id);
                }

                $cliente_telefones = DB::connection('mysql_antes')
                    ->table('cliente_telefones')->get();

                foreach ($cliente_telefones as $tel) {
                    $cliente->Telefones()->create([
                        'id' => $tel->id,
                        'tipo' => $tel->tipo,
                        'pais' => $tel->pais,
                        'numero' => $tel->numero,
                        'ramal' => $tel->ramal,
                        'detalhe' => $tel->detalhe,
                        'cliente_id' => $tel->cliente_id,
                    ]);
                }

            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            echo $msg = "error {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} ";
        }
    }
}
