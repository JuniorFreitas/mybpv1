<?php
require dirname(__DIR__) . '/vendor/autoload.php';

$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$kernel->terminate($request, $response);

ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

unset($argv[0]);
$Empresas = \App\Models\User::select('id', 'nome')->whereTipo(\App\Models\User::EMPRESA)->whereAtivo(true)->get();
$formPos = "Formulario CheckList Pos Admissão";
$formExame = "Exames";

try {
    DB::beginTransaction();
    $tblForm = \App\Models\Formulario::withoutGlobalScopes();
    //    ---------------------Formulario de PosAdmissao -----------------------------
    echo "Iniciando Sincronização Formulario $formPos...\n";
    foreach ($Empresas as $Empresa) {

        if ($tblForm->where('empresa_id', $Empresa->id)->where('titulo', $formPos)->count() == 0) {
            echo $Empresa->nome . " Formulario $formPos" . "\n";
            $dadosFormPosAdmissao = ['empresa_id' => $Empresa->id, 'titulo' => $formPos, 'descricao' => null];
            $FormPosAdmissao = $tblForm->create($dadosFormPosAdmissao);

            $dadosPosAdmissaoSetores[] = ["empresa_id" => $Empresa->id, "nome" => "Recursos Humanos"];
            $dadosPosAdmissaoSetores[] = ["empresa_id" => $Empresa->id, "nome" => "ALMOXARIFADO / ADM"];
            $dadosPosAdmissaoSetores[] = ["empresa_id" => $Empresa->id, "nome" => "SEGURANÇA DO TRABALHO / SSMA"];

            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Formulário de Solicitação de Desligamento", "tipo" => "checkbox", "setor" => "Recursos Humanos"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Entrega de Encaminhamento de Exame Dem.", "tipo" => "checkbox", "setor" => "Recursos Humanos"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Devolução de Crachá", "tipo" => "checkbox", "setor" => "Recursos Humanos"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Continuidade no Plano de Saúde", "tipo" => "checkbox", "setor" => "Recursos Humanos"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Valor Vale Transporte para desconto", "tipo" => "checkbox", "setor" => "Recursos Humanos"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Checar valor Empréstimo para desconto", "tipo" => "checkbox", "setor" => "Recursos Humanos"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Checar valores de descontos autorizados pendente", "tipo" => "checkbox", "setor" => "Recursos Humanos"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Solicitar devolução Carimbo", "tipo" => "checkbox", "setor" => "Recursos Humanos"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Solicitar CTPS para entrega", "tipo" => "checkbox", "setor" => "Recursos Humanos"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Atualização Endereço e Telefone", "tipo" => "checkbox", "setor" => "Recursos Humanos"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Assinatura de documentos do dossiê pendentes", "tipo" => "checkbox", "setor" => "Recursos Humanos"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Conferência de Cartão de Ponto do mês", "tipo" => "checkbox", "setor" => "Recursos Humanos"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Nada Consta Ferramentas área", "tipo" => "checkbox", "setor" => "ALMOXARIFADO / ADM"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Nada Consta EPIS", "tipo" => "checkbox", "setor" => "ALMOXARIFADO / ADM"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Celular (carregador e chip)", "tipo" => "checkbox", "setor" => "ALMOXARIFADO / ADM"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Notebook (carregador)", "tipo" => "checkbox", "setor" => "ALMOXARIFADO / ADM"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Pendrive", "tipo" => "checkbox", "setor" => "ALMOXARIFADO / ADM"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Exclusão de Acesso a e-mail", "tipo" => "checkbox", "setor" => "ALMOXARIFADO / ADM"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Modem", "tipo" => "checkbox", "setor" => "ALMOXARIFADO / ADM"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Outros", "tipo" => "checkbox", "setor" => "ALMOXARIFADO / ADM"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "Solicitar Ficha Registro Atualizada do colaborador", "tipo" => "checkbox", "setor" => "SEGURANÇA DO TRABALHO / SSMA"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "ASOS do colaborador (todo o histórico)", "tipo" => "checkbox", "setor" => "SEGURANÇA DO TRABALHO / SSMA"];
            $alternativasPosAdmissao[] = ["empresa_id" => $Empresa->id, "nome" => "PPP - perguntar área de atuação: ", "tipo" => "checkbox", "setor" => "SEGURANÇA DO TRABALHO / SSMA"];

            $ORDEM = 1;

            foreach ($dadosPosAdmissaoSetores as $setor) {
                $Setor = \App\Models\SetoresFormulario::withoutGlobalScopes();

                if ($Setor->where('empresa_id', $Empresa->id)->where('nome', $setor['nome'])->count() == 0) {
                    echo " Criando o Setor {$setor['nome']} para a Empresa {$Empresa->nome}" . "\n";

                    $Setor = $Setor->create($setor);
                    $FormPosAdmissao->Setores()->withoutGlobalScopes()->attach($Setor->id, ['ordem' => $ORDEM]);
                    $ORDEM++;

                    $ORDERALT = 1;

                    foreach ($alternativasPosAdmissao as $alternativa) {
                        $Alternativa = \App\Models\AlternativaFormulario::withoutGlobalScopes();

                        if ($Alternativa->where('empresa_id', $Empresa->id)->where('nome', $alternativa['nome'])->count() == 0) {
                            echo " Criando a Alternativa {$alternativa['nome']} para a EMPRESA {$Empresa->nome}" . "\n";
                            $Alternativa = $Alternativa->create($alternativa);
                            $Setor->Alternativas()->withoutGlobalScopes()->attach($Setor->id, [
                                'obrigatorio' => false,
                                'min' => null,
                                'max' => null,
                                'ordem' => $ORDERALT,
                                'class_especial' => null,
                            ]);
//                        DB::raw("INSERT INTO setor_alternativas ('setor_id', 'alternativa_id', 'obrigatorio','min','max','ordem','class_especial') VALUES ($Setor->id, $Alternativa->id, false, null, null $ORDERALT, null)");
//                        DB::table('setor_alternativas')->insert([
//                            'setor_id' => $Setor->id,
//                            'alternativa_id' => $Alternativa->id,
//                            'obrigatorio' => false,
//                            'min' => null,
//                            'max' => null,
//                            'ordem' => $ORDERALT,
//                            'class_especial' => null,
//                        ]);
//                        $Setor->Alternativas()->attach($Alternativa->id, [
//                            'obrigatorio' => false,
//                            'min' => null,
//                            'max' => null,
//                            'ordem' => $ORDERALT,
//                            'class_especial' => null,
//                        ]);
                            $ORDERALT++;
                        }
                    }
                }


//                $FormPosAdmissao->Setores()->create($setor);
//               DB::table('setores_formularios')->insert($setor);
//                $Setor = \App\Models\Setor::create($dadosSetor);
            }
        }
    }
    echo "Fim de Sincronização Formulario $formPos...\n";

    //    ---------------------Formulario de Exames -----------------------------
//    echo "Iniciando Sincronização Formulario $formExame...\n";
//    foreach ($Empresas as $Empresa) {
//        $tblForm = \App\Models\Formulario::withoutGlobalScopes();
//        if ($tblForm->where('empresa_id', $Empresa->id)->where('titulo', $formExame)->count() == 0) {
//            echo $Empresa->nome . " Formulario $$formExame" . "\n";
//            $dadosFormExame = ['empresa_id' => $Empresa->id, 'titulo' => $formExame, 'descricao' => 'Ordem'];
//            $FormExame = $tblForm->create($dadosFormExame);
//        }
//    }
//    echo "Fim de Sincronização Formulario $formExame...\n";
//    DB::commit();

} catch (Exception $e) {
    DB::rollBack();
    echo $e->getLine() . ' - ' . $e->getFile() . ' - ' . $e->getMessage();
}

