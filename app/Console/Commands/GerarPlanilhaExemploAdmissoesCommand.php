<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GerarPlanilhaExemploAdmissoesCommand extends Command
{
    protected $signature = 'mybp:gerar-planilha-exemplo-admissoes
                            {--saida= : Caminho do arquivo .xlsx (default: storage/app/importacao_admissoes/planilha_exemplo.xlsx)}
                            {--com-erros : Gera linhas com erros de validação (para testes)}';

    protected $description = 'Gera planilha Excel de exemplo para importação de admissões (aba Dados)';

    private const COLUNAS = [
        'cpf', 'nome', 'naturalidade', 'email', 'cnh', 'cnh_vencimento', 'estado_civil', 'rg', 'rg_emissao',
        'nascimento', 'sexo', 'pai', 'mae', 'pcd', 'cid', 'cep', 'endereco', 'numero', 'complemento', 'bairro',
        'municipio', 'uf', 'telefone_numero', 'whatsapp', 'cod_vaga', 'cod_area', 'centro_custo', 'tipo_admissao',
        'prazo_experiencia', 'admissao_encerramento', 'data_admissao', 'data_aso', 'data_entrega_area', 'salario',
        'pis', 'ctps_numero', 'ctps_serie', 'ctps_data_emissao', 'titulo_eleitor_numero', 'titulo_eleitor_sessao',
        'titulo_eleitor_zona', 'banco', 'agencia', 'conta', 'pix', 'pix_tipo_chave', 'pix_chave',
        'encaminhado_documento', 'encaminhado_documento_data', 'encaminhado_exame', 'encaminhado_exame_data',
        'encaminhado_treinamento', 'encaminhado_treinamento_data', 'numero_cracha', 'matricula',
    ];

    public function handle(): int
    {
        $saida = $this->option('saida') ?: storage_path('app/importacao_admissoes/planilha_exemplo.xlsx');
        $dir = dirname($saida);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dados');

        $col = 'A';
        foreach (self::COLUNAS as $nome) {
            $sheet->setCellValue($col . '1', $nome);
            $col++;
        }

        $linhasExemplo = $this->option('com-erros') ? $this->linhasComErros() : $this->linhasExemplo();
        $row = 2;
        foreach ($linhasExemplo as $linha) {
            $col = 'A';
            foreach (self::COLUNAS as $nome) {
                $sheet->setCellValue($col . $row, $linha[$nome] ?? '');
                $col++;
            }
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($saida);

        $this->info("Planilha gerada: {$saida}");
        return self::SUCCESS;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function linhasExemplo(): array
    {
        return [
            [
                'cpf' => '123.456.789-09',
                'nome' => 'Exemplo Silva Santos',
                'naturalidade' => 'São Paulo',
                'email' => 'exemplo1@email.com',
                'cnh' => 'NAO',
                'cnh_vencimento' => '',
                'estado_civil' => 'Solteiro',
                'rg' => '123456789',
                'rg_emissao' => '01/01/2020',
                'nascimento' => '15/05/1990',
                'sexo' => 'MASCULINO',
                'pai' => 'João Silva',
                'mae' => 'Maria Silva',
                'pcd' => 'NAO',
                'cid' => '',
                'cep' => '01310-100',
                'endereco' => 'Av Paulista',
                'numero' => '1000',
                'complemento' => 'Sala 1',
                'bairro' => 'Bela Vista',
                'municipio' => 'São Paulo',
                'uf' => 'SP',
                'telefone_numero' => '(11) 98765-4321',
                'whatsapp' => 'SIM',
                'cod_vaga' => '1',
                'cod_area' => '1',
                'centro_custo' => '1',
                'tipo_admissao' => 'FIXO',
                'prazo_experiencia' => '30+15',
                'admissao_encerramento' => '',
                'data_admissao' => '01/04/2025',
                'data_aso' => '25/03/2025',
                'data_entrega_area' => '01/04/2025',
                'salario' => '3500,00',
                'pis' => '',
                'ctps_numero' => '',
                'ctps_serie' => '',
                'ctps_data_emissao' => '',
                'titulo_eleitor_numero' => '',
                'titulo_eleitor_sessao' => '',
                'titulo_eleitor_zona' => '',
                'banco' => 'Banco Exemplo',
                'agencia' => '0001',
                'conta' => '12345-6',
                'pix' => 'SIM',
                'pix_tipo_chave' => 'CPF',
                'pix_chave' => '12345678909',
                'encaminhado_documento' => 'NAO',
                'encaminhado_documento_data' => '',
                'encaminhado_exame' => 'SIM',
                'encaminhado_exame_data' => '20/03/2025',
                'encaminhado_treinamento' => 'NAO',
                'encaminhado_treinamento_data' => '',
                'numero_cracha' => '',
                'matricula' => '',
            ],
            [
                'cpf' => '529.982.247-25',
                'nome' => 'Exemplo Oliveira Costa',
                'naturalidade' => 'Rio de Janeiro',
                'email' => 'exemplo2@email.com',
                'cnh' => 'SIM',
                'cnh_vencimento' => '10/12/2028',
                'estado_civil' => 'Casado',
                'rg' => '987654321',
                'rg_emissao' => '15/06/2019',
                'nascimento' => '20/08/1985',
                'sexo' => 'FEMININO',
                'pai' => 'José Oliveira',
                'mae' => 'Ana Oliveira',
                'pcd' => 'NAO',
                'cid' => '',
                'cep' => '20040-020',
                'endereco' => 'Rua do Ouvidor',
                'numero' => '50',
                'complemento' => '',
                'bairro' => 'Centro',
                'municipio' => 'Rio de Janeiro',
                'uf' => 'RJ',
                'telefone_numero' => '(21) 99876-5432',
                'whatsapp' => 'SIM',
                'cod_vaga' => '1',
                'cod_area' => '',
                'centro_custo' => '1',
                'tipo_admissao' => 'TEMPORARIO',
                'prazo_experiencia' => '',
                'admissao_encerramento' => '31/12/2025',
                'data_admissao' => '15/04/2025',
                'data_aso' => '10/04/2025',
                'data_entrega_area' => '',
                'salario' => '2800,00',
                'pis' => '12345678901',
                'ctps_numero' => '',
                'ctps_serie' => '',
                'ctps_data_emissao' => '',
                'titulo_eleitor_numero' => '',
                'titulo_eleitor_sessao' => '',
                'titulo_eleitor_zona' => '',
                'banco' => '',
                'agencia' => '',
                'conta' => '',
                'pix' => 'NAO',
                'pix_tipo_chave' => '',
                'pix_chave' => '',
                'encaminhado_documento' => 'NAO',
                'encaminhado_documento_data' => '',
                'encaminhado_exame' => 'NAO',
                'encaminhado_exame_data' => '',
                'encaminhado_treinamento' => 'NAO',
                'encaminhado_treinamento_data' => '',
                'numero_cracha' => '',
                'matricula' => '',
            ],
        ];
    }

    /**
     * Linhas com erros de validação (CPF inválido, data inválida, FIXO sem prazo, etc.).
     *
     * @return array<int, array<string, string>>
     */
    private function linhasComErros(): array
    {
        return [
            [
                'cpf' => '00000000000',
                'nome' => '',
                'cep' => '01310100',
                'endereco' => 'Rua Teste',
                'numero' => '1',
                'bairro' => 'Centro',
                'municipio' => 'São Paulo',
                'uf' => 'SP',
                'telefone_numero' => '11999998888',
                'cod_vaga' => '1',
                'centro_custo' => '1',
                'tipo_admissao' => 'FIXO',
                'prazo_experiencia' => '',
                'data_admissao' => '32/13/2025',
                'data_aso' => '01/01/2025',
            ],
        ];
    }
}
