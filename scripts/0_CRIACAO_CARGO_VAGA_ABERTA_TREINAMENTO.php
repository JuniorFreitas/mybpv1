<?php

use App\Models\VagasAbertas;
use Illuminate\Support\Facades\Auth;


require dirname(__DIR__) . '/vendor/autoload.php';

$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$kernel->terminate($request, $response);

ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

$vagasArray = [
    "ADMINISTRATIVO DE OBRA",
    "AJUDANTE DE CALDEIRARIA",
    "AJUDANTE DE CIVIL",
    "AJUDANTE DE ELÉTRICA",
    "AJUDANTE DE SOLDA",
    "ALMOXARIFE",
    "ANALISTA ADMINISTRATIVO",
    "ANALISTA DE DOCUMENTAÇÃO",
    "ANALISTA DE QUALIDADE",
    "ANALISTA DE SUPRIMENTOS",
    "ANALISTA ENGENHARIA TRAINEE",
    "APRENDIZ DE AUX",
    "ARMADOR(A)",
    "ASSISTENTE ADMINISTRATIVO",
    "ASSISTENTE DE TI",
    "ASSISTENTE SOCIAL",
    "ASSITENTE DE LOGÍSTICA",
    "AUXILIAR ADMINISTRATIVO",
    "AUXILIAR DE ALMOXARIFE",
    "AUXILIAR DE DOCUMENTAÇÃO",
    "AUXILIAR DE ELÉTRICA",
    "AUXILIAR DE LOGÍSTICA",
    "AUXILIAR DE MATERIAIS",
    "AUXILIAR DE PLANEJAMENTO",
    "AUXILIAR DE SEGURANÇA DO TRABALHO",
    "AUXILIAR DE SERVIÇOS GERAIS",
    "CALDEIREIRO(A)",
    "CALDEIREIRO(A) ESPECIALISTA",
    "CARPINTEIRO(A)",
    "COORDENADOR(A)",
    "COORDENADOR(A) ADMINISTRATIVO",
    "COORDENADOR(A) DE ALMOXARIFADO",
    "COORDENADOR(A) DE CONTRATOS",
    "COORDENADOR(A) DE OBRAS",
    "COORDENADOR(A) DE SMS",
    "COORDENADOR(A) OPERACIONAL",
    "DIRETOR(A)",
    "ELETRICISTA DE MANUTENÇÃO",
    "ELETRICISTA DE MÁQUINA PESADAS",
    "ELETRICISTA FC",
    "ELETRICISTA MONTADOR",
    "ENCANADOR(A)",
    "ENCARREGADO(A)",
    "ENCARREGADO(A) ADMINISTRATIVO",
    "ENCARREGADO(A) ALMOXARIFADO",
    "ENCARREGADO(A) DE ANDAIME",
    "ENCARREGADO(A) DE CALDEIRARIA",
    "ENCARREGADO(A) DE CÍVIL",
    "ENCARREGADO(A) DE DEPARTAMENTO PESSOAL",
    "ENCARREGADO(A) DE ELETROMECÂNICA",
    "ENCARREGADO(A) DE ELÉTRICA",
    "ENCARREGADO(A) DE LOGÍSTICA",
    "ENCARREGADO(A) DE MANUTENÇÃO",
    "ENCARREGADO(A) DE MECÂNICA",
    "ENCARREGADO(A) DE SOLDA",
    "ENCARREGADO(A) GERAL",
    "ENGENHEIRO(A)",
    "ENGENHEIRO(A) DE PLANEJAMENTO",
    "ENGENHEIRO(A) DE SEGURANÇA DO TRABALHO",
    "FERRAMENTEIRO(A)",
    "GERENTE DE OPERAÇÕES",
    "INSPETOR(A) DE EQUIPAMENTOS",
    "INSPETOR(A) DE LP/EVS",
    "INSPETOR(A) DE QUALIDADE",
    "INSPETOR(A) DE SOLDA",
    "INSPETOR(A) DE US",
    "INSPETOR(A) DIMENSIONAL",
    "INSTRUMENTISTA",
    "LIXADOR(A)",
    "LÍDER DE MECÂNICA",
    "LÍDER OPERACIONAL",
    "MANUTENÇÃO",
    "MAÇARIQUEIRO(A)",
    "MECÂNICO(A) AJUSTADOR",
    "MECÂNICO(A) ESPECIALIZADO",
    "MECÂNICO(A) INDUSTRIAL",
    "MECÂNICO(A) MONTADOR",
    "MECÂNICO(A) OPERACIONAL",
    "MEIO OFICIAL",
    "MESTRE DE CALDEIRARIA",
    "MONTADOR(A) DE ANDAIME",
    "MOTORISTA",
    "MOTORISTA DE MUNCK",
    "OBSERVADOR(A) DE SEGURANÇA",
    "OPERADOR(A) DE EMPILHADEIRA",
    "OPERADOR(A) DE MACACO HIDRAULICO",
    "OPERADOR(A) SACA-FEIXE",
    "ORÇAMENTISTA",
    "PEDREIRO(A)",
    "PROJETISTA",
    "SINALEIRO(A)",
    "SOLDADOR(A) ESPECIALISTA",
    "SOLDADOR(A):MIG, DE LIGAS, RX, TIG/ER",
    "SUPERVISOR(A) ADMINISTRATIVO",
    "SUPERVISOR(A) DE CALDEIRARIA",
    "SUPERVISOR(A) DE CIVIL",
    "SUPERVISOR(A) DE CQ",
    "SUPERVISOR(A) DE MECÂNICA",
    "SUPERVISOR(A) DE OPERACIONAL",
    "SUPERVISOR(A) DE SEGURANÇA DO TRABALHO",
    "SUPERVISOR(A) DE SOLDA",
    "SUPERVISOR(A) INDUSTRIAL",
    "TORNEIRO(A) MECÂNICO",
    "TÉCNICO(A) DE  ASSISTENTE DE PLANEJAMENTO",
    "TÉCNICO(A) DE DOCUMENTAÇÃO",
    "TÉCNICO(A) DE MATERIAIS",
    "TÉCNICO(A) DE PLANEJAMENTO",
    "TÉCNICO(A) DE SEGURANÇA DO TRABALHO",
    "TÉCNICO(A) ELÉTRICA",
    "TÉCNICO(A) ENFERMAGEM DO TRABALHO",
    "TÉCNICO(A) ESPECIALIZADO(A) EM MECÂNICA",
    "TÉCNICO(A) MECÂNICO"
];

$municipiosArray = [
    2743, 1069
];

$empresa_id = 78862;
$user_id = $empresa_id;
Auth::loginUsingId($user_id);
$count = 0;

foreach ($municipiosArray as $mun) {
    foreach ($vagasArray as $vaga) {
        $cargo = firstOrCreateCargo($vaga, $empresa_id);
        $vaga_aberta = firstOrCreateVagaAberta($cargo->id, $mun, $empresa_id, $vaga);
        $count++;
        print_r($count . ' - ' . $vaga . ' - ' . $mun . PHP_EOL);
    }
}

$ordem = 1;

$treinamentos = [
    ["label" => "INTRODUTÓRIO CONTRATADA", "descricao" => "Todos os funcionários", "prazo_parada" => 728, "prazo_fixo" => 728, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Introdutório de Contratada", "exibir_na_carteira" => true],
    ["label" => "RECICLAGEM NORMAS MANDATÓRIAS", "descricao" => "Todos os funcionários", "prazo_parada" => 728, "prazo_fixo" => 728, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Reciclagem de Normas Mandatórias", "exibir_na_carteira" => true],
    ["label" => "RECICLAGEM DE NORMA ALCOA 3260,3269,3270 QUALIFICADO & NR 10", "descricao" => "ENCARREGADO(A) DE ELÉTRICA", "prazo_parada" => 728, "prazo_fixo" => 728, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Reciclagem 3260/69/70 - Qualificado", "exibir_na_carteira" => true],
    ["label" => "NORMA ALCOA 3260,3269,3270 AUTORIZADO & NR 10", "descricao" => "ELETRICISTA DE MANUTENÇÃO", "prazo_parada" => 728, "prazo_fixo" => 728, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Basico Norma 3260/69/70 - Qualificado", "exibir_na_carteira" => true],
    ["label" => "RECICLAGEM DO TRABALHADOR AUTORIZADO E DO VIGIA (OBSERVADOR) DE ESPAÇOS CONFINADOS", "descricao" => "ELETRICISTA DE MÁQUINA PESADAS", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "NR 33 (Vigia e Autorizado)", "exibir_na_carteira" => true],
    ["label" => "RECICLAGEM DO SUPERVISOR DE ESPAÇOS CONFINADOS", "descricao" => "ELETRICISTA FC", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Supervisor de E. C ", "exibir_na_carteira" => true],
    ["label" => "PREVENÇÃO DE QUEDAS/NR35", "descricao" => "ELETRICISTA MONTADOR", "prazo_parada" => 728, "prazo_fixo" => 728, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "NR 35 Autorizado ", "exibir_na_carteira" => true],
    ["label" => "METAL LÍQUIDO (ESPECÍFICO)", "descricao" => "Todos os funcionários (quando solicitado)", "prazo_parada" => 728, "prazo_fixo" => 728, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Metal de Líquido (Específico)", "exibir_na_carteira" => true],
    ["label" => "ISPS CODE", "descricao" => "Todos os funcionários", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Isps de Code", "exibir_na_carteira" => true],
    ["label" => "BÁSICO DE NORMA ALCOA 3260,3269,3270 QUALIFICADO & NR 10", "descricao" => "TÉCNICO(A) DE SEGURANÇA DO TRABALHO", "prazo_parada" => 728, "prazo_fixo" => 728, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Básico 3260/69/70 - Qualificado", "exibir_na_carteira" => true],
    ["label" => "NR33 - PAPEIS E RESPONSABILIDADES DO TRABALHADOR AUTORIZADO E DO VIGIA (OBSERVADOR) DE ESPAÇOS CONFINADOS -MÓDULO COMPLEMENTAR", "descricao" => "Todos os funcionários", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "NR 33 (Vigia e Autorizado)", "exibir_na_carteira" => true],
    ["label" => "NR33 - SEGURANÇA E SAÚDE NOS TRABALHOS EM ESPAÇO CONFINADO -SUPERVISOR DE ENTRADA (MÓDULO COMPLEMENTAR)", "descricao" => "Funcionários da OS 0140105", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Supervisor de E. C ", "exibir_na_carteira" => true],
    ["label" => "BÁSICO DE DIREÇÃO DEFENSIVA - ALU", "descricao" => "Funcionários Porto", "prazo_parada" => 1092, "prazo_fixo" => 1092, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Direção Defensiva", "exibir_na_carteira" => true],
    ["label" => "RECICLAGEM DE MANOBRISTA DE PONTES ROLANTES", "descricao" => "ENCARREGADO(A) DE ELÉTRICA", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Manobrista de Ponte Rolante", "exibir_na_carteira" => true],
    ["label" => "RECICLAGEM DE MANOBRISTA DE VEÍCULOS", "descricao" => "ELETRICISTA DE MANUTENÇÃO", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Manobrista de Ponte Rolante", "exibir_na_carteira" => true],
    ["label" => "RECICLAGEM DE OPERAÇÃO DE MUNCK", "descricao" => "ELETRICISTA DE MÁQUINA PESADAS", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Operação de Munck", "exibir_na_carteira" => true],
    ["label" => "RECICLAGEM DE OPERAÇÃO DE SKY MUNCK", "descricao" => "ELETRICISTA FC", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Operação de Sky Munck", "exibir_na_carteira" => true],
    ["label" => "RECICLAGEM DE OPERACAO DE GUINDASTE", "descricao" => "ELETRICISTA MONTADOR", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Operação de Guindaste", "exibir_na_carteira" => true],
    ["label" => "RECICLAGEM DE OPERAÇÃO DE PLATAFORMA DE TRABALHO AEREO", "descricao" => "Todos os funcionários", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Operação de Plataforma móvel ", "exibir_na_carteira" => true],
    ["label" => "RECICLAGEM DE OPERAÇÃO DE POLIGUINDASTE", "descricao" => "TÉCNICO(A) DE SEGURANÇA DO TRABALHO", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Operação Poliguindastes", "exibir_na_carteira" => true],
    ["label" => "SINALEIROS / MOVIMENTACAO DE CARGAS", "descricao" => "Todos os funcionários ", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Sinaleiro / Movimentação de carga", "exibir_na_carteira" => true],
    ["label" => "RECICLAGEM DE OPERAÇÃO DE VEICULOS E EQUIPAMENTOS INDUSTRIAIS MÓVEIS- VEIM", "descricao" => "Todos os funcionários ", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "VEIM", "exibir_na_carteira" => true],
    ["label" => "RECICLAGEM DE VEÍCULOS INDUSTRIAIS PIPA, CAÇAMBA, COMBOIO", "descricao" => "Todos os funcionários (quando solicitado)", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "VEIM (caçambas, pipa)", "exibir_na_carteira" => true],
    ["label" => "RECICLAGEM DE OPERAÇÃO DE ULTRAVAC", "descricao" => "MOTORISTA DE MUNCK", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "VEIM (operação Ultravac)", "exibir_na_carteira" => true],
    ["label" => "IÇAMENTO DE CARGAS COM SEGURANÇA", "descricao" => "MOTORISTA DE MUNCK", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Operação de carga ", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DOS FORNOS DE INDUÇÃO", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Operacão de forno", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE EMPILHADEIRA", "descricao" => "Todos os funcionários (quando solicitado)", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Operação de De Empilhadeira", "exibir_na_carteira" => true],
    ["label" => "REFRATÁRIOS PARA A AREA DA REDUCAO", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Refratário ", "exibir_na_carteira" => true],
    ["label" => "EXTINÇÃO DE EFEITO ANÓDICO", "descricao" => "Todos os funcionários (quando solicitado)", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Efeito anódico", "exibir_na_carteira" => true],
    ["label" => "TROCA DE ANODOS", "descricao" => "Todos os funcionários (quando solicitado)", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Troca de De Anodos", "exibir_na_carteira" => true],
    ["label" => "MANOBRISTA DE PONTES ROLANTES", "descricao" => "MOTORISTA", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Manobrista de Ponte Rolante", "exibir_na_carteira" => true],
    ["label" => "MANOBRISTA DE VEÍCULOS", "descricao" => "MOTORISTA", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Manobrista de De Veículos", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE GAUTSHI E ROTATIVA", "descricao" => "MOTORISTA DE MUNCK", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "OPERAÇÃO DE GAUTSHI E ROTATIVA", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE GUINDASTE", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Operação de De Guindaste", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE MAQUINA RUCKER", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "OPERAÇÃO DE MAQUINA RUCKER", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE MOTONIVELADORA", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Operação de De Motoniveladora", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE PA CARREGADEIRA", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Operação de pá carregadeira", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE PLATAFORMA DE TRABALHO AEREO (TIPO LANCA)", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Oper. Plataforma Elevatória (lança)", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE PLATAFORMA DE TRABALHO AEREO (TIPO TESOURA)", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Oper. Plataforma Elevatória (Tessoura)", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE PONTES ROLANTES DEMAG E MAUSA", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Oper.Pontes Rolantes Demag e Mausa", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE PONTES ROLANTES E.C.L - SALA DE CUBAS", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Oper. Ponte Rolante E.C. L - Sala de Cuba", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE PONTES ROLANTES PEQUENO PORTE / TALHA ELETRICA", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Oper. Ponte Rolante Pequeno Porte ", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE PONTES ROLANTES STACKER", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Oper.de rolantes Stacker - Qualificado", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE PONTES ROLANTES ZANINI", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Oper. De pontes rolante  Zanini- Qualific.", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE REBOCADOR DE CADINHOS E PALLETS", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Oper. Rebocador de cadinho e pallets", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE RETROESCAVADEIRA", "descricao" => "Todos os funcionários (quando solicitado)", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Operação de De Retroescavadeira", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE SKY MUNCK", "descricao" => "Todos os funcionários (quando solicitado)", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Operação de Sky Munck ", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE STACKER DE BAUXITA", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Operação de Stacker de Bauxita", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE TRATOR DE PNEUS", "descricao" => "Funcionários da OS 0140105", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "Operação de Trator de Pneus ", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE TRIPPER DE PICHE", "descricao" => "Todos os funcionários (quando solicitado)", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "OPERAÇÃO DE TRIPPER DE PICHE", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE VARREDEIRA MECANICA", "descricao" => "Todos os funcionários (quando solicitado)", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "OPERAÇÃO DE VARREDEIRA MECANICA", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DO CARREGADOR DE NAVIOS / PORTO", "descricao" => "Todos os funcionários (quando solicitado)", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "OPERAÇÃO DO CARREGADOR DE NAVIOS / PORTO", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DO STACKER CARVAO / PORTO", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "OPERAÇÃO DO STACKER CARVAO / PORTO", "exibir_na_carteira" => true],
    ["label" => "ESCAVADEIRA HIDRÁULICA", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "ESCAVADEIRA HIDRÁULICA", "exibir_na_carteira" => true],
    ["label" => "TRATOR DE ESTEIRA", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "TRATOR DE ESTEIRA", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE POLIGUINDASTE", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "OPERAÇÃO DE POLIGUINDASTE", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE MINI ESCAVADEIRA", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "OPERAÇÃO DE MINI ESCAVADEIRA", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE ROLO COMPACTADOR", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "OPERAÇÃO DE ROLO COMPACTADOR", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE MANIPULADOR TELESCÓPICO", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "OPERAÇÃO DE MANIPULADOR TELESCÓPICO", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE MÁQUINA PERFURATRIZ", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "OPERAÇÃO DE MÁQUINA PERFURATRIZ", "exibir_na_carteira" => true],
    ["label" => "VEÍCULOS INDUSTRIAIS PIPA, CAÇAMBA, COMBOIO", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "VEÍCULOS INDUSTRIAIS PIPA, CAÇAMBA, COMBOIO", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE ULTRAVAC", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "OPERAÇÃO DE ULTRAVAC", "exibir_na_carteira" => true],
    ["label" => "PONTE ROLANTE UTILIDADES", "descricao" => "", "prazo_parada" => 364, "prazo_fixo" => 364, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "PONTE ROLANTE UTILIDADES", "exibir_na_carteira" => true],
    ["label" => "SEGURANÇA EM ELETRICIDADE SALA DE CUBAS (NORMA ALCOA 3266,3267)", "descricao" => "", "prazo_parada" => 720, "prazo_fixo" => 720, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "SEGURANÇA EM ELETRICIDADE SALA DE CUBAS (NORMA ALCOA 3266,3267)", "exibir_na_carteira" => true],
    ["label" => "POT STEP IN", "descricao" => "", "prazo_parada" => 720, "prazo_fixo" => 720, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "POT STEP IN", "exibir_na_carteira" => true],
    ["label" => "PROGRAMA DE CONTROLE DE EXPOSIÇÃO A PICHE – CTPV", "descricao" => "", "prazo_parada" => 720, "prazo_fixo" => 720, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "PROGRAMA DE CONTROLE DE EXPOSIÇÃO A PICHE – CTPV", "exibir_na_carteira" => true],
    ["label" => "FIBRAS MINERAIS", "descricao" => "", "prazo_parada" => 720, "prazo_fixo" => 720, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "FIBRAS MINERAIS", "exibir_na_carteira" => true],
    ["label" => "PROGRAMA DE PREVENÇÃO DE DOENCAS RELACIONADAS AO CALOR – HEAT STRESS", "descricao" => "", "prazo_parada" => 720, "prazo_fixo" => 720, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "PROGRAMA DE PREVENÇÃO DE DOENCAS RELACIONADAS AO CALOR – HEAT STRESS", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE MUNCK", "descricao" => "", "prazo_parada" => 720, "prazo_fixo" => 720, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "OPERAÇÃO DE MUNCK", "exibir_na_carteira" => true],
    ["label" => "RECICLAGEM DE DESEMPENHO HUMANO - ALU", "descricao" => "", "prazo_parada" => 720, "prazo_fixo" => 720, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "RECICLAGEM DE DESEMPENHO HUMANO - ALU", "exibir_na_carteira" => true],
    ["label" => "DESEMPENHO HUMANO - DH", "descricao" => "Todos os funcionários (quando solicitado)", "prazo_parada" => 720, "prazo_fixo" => 720, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "DESEMPENHO HUMANO - DH", "exibir_na_carteira" => true],
    ["label" => "PALETEIRA", "descricao" => "Funcionários da OS 0140105", "prazo_parada" => 720, "prazo_fixo" => 720, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "PALETEIRA", "exibir_na_carteira" => true],
    ["label" => "OPERAÇÃO DE TRITURADOR DE RESÍDUOS", "descricao" => "Funcionários da OS 0140105", "prazo_parada" => 720, "prazo_fixo" => 720, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "OPERAÇÃO DE TRITURADOR DE RESÍDUOS", "exibir_na_carteira" => true],
    ["label" => "POLÍTICA GLOBAL DE BARRAGENS", "descricao" => "Funcionários da OS 0140105", "prazo_parada" => 720, "prazo_fixo" => 720, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "POLÍTICA GLOBAL DE BARRAGENS", "exibir_na_carteira" => true],
    ["label" => "CIPA", "descricao" => "", "prazo_parada" => 720, "prazo_fixo" => 720, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "CIPA", "exibir_na_carteira" => true],
    ["label" => "OPERALÃO DE BOB CAT", "descricao" => "", "prazo_parada" => 720, "prazo_fixo" => 720, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "OPERAÇÃO DE BOB CAT", "exibir_na_carteira" => true],
    ["label" => "NR 20", "descricao" => "", "prazo_parada" => 72, "prazo_fixo" => 72, "ordem" => $ordem, "ativo" => true, "empresa_id" => $empresa_id, "label_reduzida" => "NR 20 - AUTORIZADO", "exibir_na_carteira" => true],
];

foreach ($treinamentos as $treinamento) {
    $treinamento['empresa_id'] = $empresa_id;
    $treinamento['ordem'] = $ordem;
    $treinamento['ativo'] = true;
    $treinamento['exibir_na_carteira'] = true;
    $ordem++;

    try {
        $tr = \App\Models\Vencimento::firstOrCreate($treinamento, $treinamento);
        print_r("Treinamento {$tr->label} criado com sucesso\n");
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
}


function firstOrCreateCargo($nome, $empresa_id, $ativo = true)
{
    $cargo = \App\Models\Vaga::firstOrCreate([
        'nome' => $nome,
        'empresa_id' => $empresa_id,
        'ativo' => $ativo
    ]);

    return $cargo;
}

function firstOrCreateVagaAberta($vaga_id, $municipio_id, $empresa_id, $titulo, $descricao = '', $ativo_sistema = true, $ativo = true)
{
    $vaga = VagasAbertas::firstOrCreate([
        'vaga_id' => $vaga_id,
        'municipio_id' => $municipio_id,
        'empresa_id' => $empresa_id,
        'titulo' => $titulo,
        'descricao' => $descricao,
        'ativo_sistema' => $ativo_sistema,
        'ativo' => $ativo
    ]);

    return $vaga;
}

function firstOrCreateCentroCusto($nome, $empresa_id, $ativo = true)
{
    $centro_custo = \App\Models\CentroCusto::firstOrCreate([
        'label' => $nome,
        'gestor_id' => null,
        'empresa_id' => $empresa_id,
        'ativo' => $ativo
    ]);

    return $centro_custo;
}

function firstOrCreateCentroCustoFilial($empresa_id, $centro_custo_id, $filial_id, $ativo = true)
{
    $centro_custo_filial = \App\Models\CentroCustoFilial::firstOrCreate([
        'empresa_id' => $empresa_id,
        'centro_custo_id' => $centro_custo_id,
        'cliente_filial_id' => $filial_id,
        'ativo' => $ativo
    ]);

    return $centro_custo_filial;
}
