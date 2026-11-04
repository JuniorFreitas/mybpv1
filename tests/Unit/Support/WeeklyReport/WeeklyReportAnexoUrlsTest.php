<?php

namespace Tests\Unit\Support\WeeklyReport;

use App\Models\Arquivo;
use App\Models\Tarefa;
use App\Support\WeeklyReport\WeeklyReportAnexoUrls;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Tests\TestCase;

class WeeklyReportAnexoUrlsTest extends TestCase
{
    private function makeArquivo(array $attrs = []): Arquivo
    {
        $arquivo = new Arquivo(array_merge([
            'nome' => 'print',
            'imagem' => true,
            'extensao' => '.png',
            'file' => 'abc123.png',
            'thumb' => 'abc123_p.png',
            'bytes' => 10,
            'temporario' => false,
            'disco' => Arquivo::DISCO_WEEKLY_REPORT,
        ], $attrs));
        $arquivo->id = (int) ($attrs['id'] ?? 1);
        $arquivo->exists = true;

        return $arquivo;
    }

    public function test_for_arquivo_gera_urls_nested_do_weekly_report(): void
    {
        $arquivo = $this->makeArquivo();

        $payload = WeeklyReportAnexoUrls::forArquivo($arquivo, 104, 19, 29, 81);

        $this->assertStringContainsString(
            '/g/weekly-report/104/quadros/19/listas/29/tarefas/81/anexo/abc123.png',
            $payload['url']
        );
        $this->assertStringContainsString(
            '/g/weekly-report/104/quadros/19/listas/29/tarefas/81/anexo/abc123_p.png',
            $payload['urlThumb']
        );
        $this->assertStringContainsString(
            '/g/weekly-report/104/quadros/19/listas/29/tarefas/81/anexoDownload/abc123.png',
            $payload['urlDownload']
        );
        $this->assertStringContainsString(
            '/g/weekly-report/104/quadros/19/listas/29/tarefas/81/anexo/abc123.png',
            $payload['urlDelete']
        );
        $this->assertStringNotContainsString('/g/weekly-report/anexo/', $payload['url']);
        $this->assertSame('', $arquivo->url); // accessor sem contexto nested
    }

    public function test_hydrate_tarefa_anexos_substitui_relacao_com_urls_corretas(): void
    {
        $tarefa = new Tarefa(['titulo' => 'Card', 'lista_id' => 29]);
        $tarefa->id = 81;
        $tarefa->exists = true;
        $tarefa->setRelation('Anexos', new EloquentCollection([
            $this->makeArquivo([
                'id' => 9,
                'nome' => 'doc',
                'imagem' => false,
                'extensao' => '.pdf',
                'file' => 'doc.pdf',
                'thumb' => null,
            ]),
        ]));

        WeeklyReportAnexoUrls::hydrateTarefaAnexos($tarefa, 104, 19, 29);

        $anexo = $tarefa->Anexos->first();
        $this->assertIsArray($anexo);
        $this->assertStringContainsString(
            '/g/weekly-report/104/quadros/19/listas/29/tarefas/81/anexo/doc.pdf',
            $anexo['url']
        );
    }
}
