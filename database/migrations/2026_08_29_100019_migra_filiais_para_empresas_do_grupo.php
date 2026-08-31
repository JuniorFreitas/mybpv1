<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Cada filial ativa em cliente_filials vira uma empresa real (par
     * users+clientes, mesmo padrão STI das empresas existentes) dentro do
     * grupo da empresa-mãe, com matriz=false. Não altera nem remove
     * cliente_filials — puramente aditivo. cliente_filials.empresa_nova_id
     * guarda o link pra quando o código que usa filial_id for migrado.
     *
     * Login sintético (não é conta de login de verdade, só a âncora STI
     * clientes/users) marcado is_sistema=true — protegido pelo guard de
     * e-mail já existente.
     */
    public function up(): void
    {
        $filiais = DB::table('cliente_filials')
            ->whereNull('empresa_nova_id')
            ->where('ativo', true)
            ->get();

        foreach ($filiais as $filial) {
            $dados = json_decode($filial->dados, true) ?? [];

            $empresaMae = DB::table('clientes')->where('id', $filial->empresa_id)->first();
            if (!$empresaMae) {
                continue;
            }

            $agora = now();
            $nome = $dados['nome_fantasia'] ?? $dados['razao_social'] ?? ('Filial #' . $filial->id);

            $novoUserId = DB::table('users')->insertGetId([
                'nome' => $nome,
                'login' => 'filial+cf' . $filial->id . '@mybp.com.br',
                'is_sistema' => true,
                'tipo' => 'Empresa',
                'ativo' => true,
                'temp' => false,
                'termos' => false,
                'privilegio_gestor_area' => false,
                'privilegio_gestor_centro_custo' => false,
                'require_password_reset' => false,
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);

            DB::table('users')->where('id', $novoUserId)->update(['empresa_id' => $novoUserId]);

            DB::table('clientes')->insert([
                'id' => $novoUserId,
                'grupo_id' => $empresaMae->grupo_id,
                'matriz' => false,
                'tipo_cliente' => 'Cliente',
                'tipo' => 'Pessoa Jurídica',
                'cnpj' => $dados['cnpj'] ?? null,
                'nome' => $nome,
                'razao_social' => $dados['razao_social'] ?? null,
                'nome_fantasia' => $dados['nome_fantasia'] ?? null,
                'area_id' => $dados['area_id'] ?? $empresaMae->area_id,
                'ramo' => $dados['ramo'] ?? null,
                'cep' => $dados['cep'] ?? null,
                'logradouro' => $dados['logradouro'] ?? null,
                'numero' => $dados['end_numero'] ?? null,
                'complemento' => $dados['complemento'] ?? null,
                'bairro' => $dados['bairro'] ?? null,
                'municipio' => $dados['municipio'] ?? null,
                'uf' => $dados['uf'] ?? null,
                'contato' => $dados['contato'] ?? null,
                'email' => $dados['email'] ?? null,
                'tel_principal' => $dados['tel_principal'] ?? ($dados['telefone'] ?? null),
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);

            DB::table('cliente_filials')->where('id', $filial->id)->update(['empresa_nova_id' => $novoUserId]);
        }
    }

    public function down(): void
    {
        $novosIds = DB::table('cliente_filials')->whereNotNull('empresa_nova_id')->pluck('empresa_nova_id');
        DB::table('cliente_filials')->whereNotNull('empresa_nova_id')->update(['empresa_nova_id' => null]);
        DB::table('clientes')->whereIn('id', $novosIds)->delete();
        DB::table('users')->whereIn('id', $novosIds)->delete();
    }
};
