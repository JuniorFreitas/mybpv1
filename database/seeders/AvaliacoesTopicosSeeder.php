<?php

namespace Database\Seeders;

use App\Models\AvaliacaoTopico;
use DB;
use Exception;
use Illuminate\Database\Seeder;

class AvaliacoesTopicosSeeder extends Seeder
{
    public function run()
    {
        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => null,
            'topico' => 'COMPETÊNCIAS',
            'topico_explicacao' => 'Obs: as frases dentro das competências, ajudarão a pontuar melhor quais os pontos fortes e a desenvolver de cada funcionário',
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 1,
            'topico' => '1 - PRÁTICA DOS VALORES ORGANIZACIONAIS',
            'topico_explicacao' => 'Respeito é o princípio\nEngajamento e entusiasmo\nAgilidade com qualidade\nSentimento de dono\nAmor em cada detalhe\nFoco em resultado sustentável\nÉtica e transparência\nDesenvolvimento de pessoas',
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 1,
            'topico' => '2 - CONHECIMENTOS',
            'topico_explicacao' => 'Gestão de Qualidade\nAuditorias\nGestão de Pessoas\nGestão de Riscos\nGestão de CRM\nGestão Comercial',
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 1,
            'topico' => '3 - HABILIDADES',
            'topico_explicacao' => 'Liderança\nDefinição de prioridades\nNegociação\nPlanejamento e controle de metas\nAnálise e processamento de informações\nTrabalhar sob pressão',
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 1,
            'topico' => '4 - ATITUDES',
            'topico_explicacao' => 'Proatividade\nSenso Crítico\nDisposição para aprendizado e atualizações contínuas\nÉtica\nBusca por resultados\nComprometimento',
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 1,
            'topico' => '5 - ATRIBUIÇÕES DO CARGO',
            'topico_explicacao' => '- Elaborar e controlar os Sistemas de Gestão da BPSE, com base no mapeamento de processos;\n- Garantir o cumprimento da atuação BPSE nas áreas de qualidade e produtividade, saúde, segurança e meio ambiente, tributos e impostos e responsabilidade social para qualificação da empresa no PROCEM e ISO;\n- Realizar auditorias internas e acompanhamento de auditorias externas;\n- Acompanhamento e tratamento de Não Conformidades e Ações Corretivas;\n- Implementar ações de melhoria no SGQ;\n- Implementar e atuar no mapeamento de riscos;\n- Realizar treinamentos voltados para a Qualidade e Desenvolvimento de Pessoas;\n- Desenvolver e propor políticas organizacionais;\n- Desenvolver e propor indicadores mensais de qualidade nos processos;\n- Desenvolver e propor Plano Anual de Treinamentos para os funcionários da empresa;\n- Desenvolver e propor Programa de Qualidade de Vida e Responsabilidade Social para os funcionários da empresa;\n- Padronizar, procedimentar e estabelecer os fluxos de todos os processos e contratos da BPSE e seus clientes.',
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 1,
            'topico' => '6 - INICIATIVA',
            'topico_explicacao' => 'Identifica e busca a solução de problemas de maneira preventiva.
            Assume a responsabilidade na identificação de erros cometidos e busca a correção imediata.
            Busca ajuda para solucionar problemas.',
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 1,
            'topico' => '7 - INOVAÇÃO E CRIATIVIDADE',
            'topico_explicacao' => 'Comunica a liderança ao identificar oportunidades de melhoria.
            Apresenta sugestões que contribuem para a solução de problemas.
            Busca novas alternativas para aprimorar o seu trabalho.',
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 1,
            'topico' => '8 - RELACIONAMENTO INTERPESSOAL',
            'topico_explicacao' => 'Relaciona-se bem com os colegas de trabalho.
            É cortez, educado e estabelece empatia com o cliente.
            Recebe com maturidade (autocontrole) opiniões, críticas e sugestões.',
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 1,
            'topico' => '9 - ORIENTAÇÃO PARA RESULTADOS',
            'topico_explicacao' => 'Busca todas as alternativas possíveis de acordo com suas atribuições para atender / resolver os problemas do cliente interno e externo.
            Estabelece as prioridades organizando suas atividades de acordo com o grau de \nComunica de forma clara e imediata o problema apresentado pelo cliente ao seu superior, quando não consegue resolver.
            Realiza seu trabalho com atenção e dedicação, buscando sempre a excelência.',
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 1,
            'topico' => '10 - RACIOCÍNIO ANALÍTICO',
            'topico_explicacao' => 'Busca constantemente analisar e aperfeiçoar sua atuação de acordo com os princípios da empresa.
            Dá ideias e sugestões para aperfeiçoar a execução do trabalho.
            Durante sua atuação, consegue avaliar os impactos de um problema no resultado final (consequência).',
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => null,
            'topico' => 'DESEMPENHO',
            'topico_explicacao' => null,
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 12,
            'topico' => '1 - Cumprimento das normas e procedimentos da empresa.',
            'topico_explicacao' => null,
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 12,
            'topico' => '2 - Realização de tarefas/ atividades planejadas (Produtividade).',
            'topico_explicacao' => null,
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 12,
            'topico' => '3 - Cumprimento dos padrões exigidos para a realização das tarefas/ atividades.',
            'topico_explicacao' => null,
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 12,
            'topico' => '4 - Comprometimento com a empresa com suas atribuições e equipe.',
            'topico_explicacao' => null,
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 12,
            'topico' => '5 - Delega, acompanha e orientanta a equipe durante a execução das atividades que esta envolvido(a).',
            'topico_explicacao' => null,
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 12,
            'topico' => '6 - Possui espertise em lidar com as mais variadas situações que envolve sua área de atuação.',
            'topico_explicacao' => null,
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 12,
            'topico' => '7 - Assume a responsabilidade pelas atribuições do seu setor e entrega com eficiência os resultados solicitados.',
            'topico_explicacao' => null,
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 12,
            'topico' => '8 - Cobra os demais setores da empresa interligado nas suas atividades.',
            'topico_explicacao' => null,
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 12,
            'topico' => '9 - Cumprimento de prazos conforme metas estabelecidas (Prazo).',
            'topico_explicacao' => null,
            'ativo' => true
        ];

        $lista[] = [
            'empresa_id' => 104,
            'avaliacao_tipo_id' => 1,
            'topico_pai_id' => 12,
            'topico' => '10 - Assiduidade e cumprimento do horário de trabalho.',
            'topico_explicacao' => null,
            'ativo' => true
        ];

        try {
            DB::beginTransaction();

            foreach ($lista as $avaliacao_topico) {
                if (AvaliacaoTopico::whereTopico($avaliacao_topico['topico'])->whereEmpresaId($avaliacao_topico['empresa_id'])->whereAvaliacaoTipoId($avaliacao_topico['avaliacao_tipo_id'])->whereTopicoExplicacao($avaliacao_topico['topico_explicacao'])->count() == 0) {
                    echo "\e[032mCriando tópico de avaliação: " . $avaliacao_topico['topico'] . " - " . $avaliacao_topico['topico_explicacao']. " - " . $avaliacao_topico['avaliacao_tipo_id']. " - " . $avaliacao_topico['topico_pai_id']. " - " . $avaliacao_topico['empresa_id'] . "\n";
                    AvaliacaoTopico::create($avaliacao_topico);
                    DB::commit();
                } else {
                    echo "\e[34mTópico de avaliação já existe: " . $avaliacao_topico['topico'] . " - " . $avaliacao_topico['topico_explicacao']. " - " . $avaliacao_topico['avaliacao_tipo_id']. " - ". $avaliacao_topico['topico_pai_id']. " - " . $avaliacao_topico['empresa_id'] . "\n";
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            print_r($e->getMessage());
//            return $e->getTrace() . ' - ' . $e->getCode() . ' - ' . $e->getCode() . ' - ' . $e->getLine();
//            return $e->getFile() . ' - ' . $e->getMessage() . ' - ' . $e->getCode() . ' - ' . $e->getLine();
        }
    }
}
