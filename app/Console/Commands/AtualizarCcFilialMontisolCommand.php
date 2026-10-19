<?php

namespace App\Console\Commands;

use App\Models\Sistema;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Atualiza centro de custo e flag filial das admissões Montisol a partir da planilha de cruzamento.
 *
 * Colunas esperadas (aba Planilha1): centro_custo_id | filial (S/N) | nome | cpf
 * Atualiza apenas: admissoes.centro_custo_id e admissoes.filial (S→true, N→false).
 *
 * Uso:
 *   php artisan mybp:atualizar-cc-filial-montisol
 *   php artisan mybp:atualizar-cc-filial-montisol --dry-run
 *   docker compose exec mybpdp php artisan mybp:atualizar-cc-filial-montisol --dry-run
 */
class AtualizarCcFilialMontisolCommand extends Command
{
    protected $signature = 'mybp:atualizar-cc-filial-montisol
                            {--arquivo=scripts/cruzamento_montisol.xlsx : Caminho relativo à raiz do projeto ou absoluto}
                            {--empresa-id=63122 : ID da empresa (feedback_curriculos.empresa_id)}
                            {--dry-run : Simula sem gravar no banco}
                            {--relatorio= : Caminho do CSV de resultado (default storage/app/relatorios)}';

    protected $description = 'Atualiza centro_custo_id e filial das admissões Montisol a partir da planilha de cruzamento';

    public function handle(): int
    {
        $empresaId = (int) $this->option('empresa-id');
        $dryRun = (bool) $this->option('dry-run');
        $arquivo = $this->resolverPath((string) $this->option('arquivo'));

        if ($empresaId <= 0) {
            $this->error('empresa-id inválido.');

            return self::FAILURE;
        }

        if (!is_readable($arquivo)) {
            $this->error("Arquivo não encontrado ou não legível: {$arquivo}");

            return self::FAILURE;
        }

        $this->info('Atualizando CC/filial Montisol...');
        $this->line("  Arquivo: {$arquivo}");
        $this->line("  Empresa ID: {$empresaId}");
        $this->line('  Modo: '.($dryRun ? 'DRY-RUN (sem gravar)' : 'GRAVAÇÃO'));
        $this->newLine();

        $linhas = $this->lerPlanilha($arquivo);
        if ($linhas === []) {
            $this->error('Nenhuma linha válida na planilha (esperado: centro_custo_id, filial, nome, cpf).');

            return self::FAILURE;
        }

        $this->info('Linhas na planilha: '.count($linhas));

        $ccsValidos = $this->carregarCentrosCusto($empresaId);
        $admissoesPorCpf = $this->carregarAdmissoesPorCpf(
            $empresaId,
            array_column($linhas, 'cpf')
        );

        $relatorio = [];
        $ok = 0;
        $semAlteracao = 0;
        $erros = 0;

        foreach ($linhas as $linha) {
            $resultado = $this->processarLinha(
                $linha,
                $empresaId,
                $ccsValidos,
                $admissoesPorCpf,
                $dryRun
            );
            $relatorio[] = $resultado;

            if ($resultado['status'] === 'atualizado' || $resultado['status'] === 'dry_run') {
                $ok++;
            } elseif ($resultado['status'] === 'sem_alteracao') {
                $semAlteracao++;
            } else {
                $erros++;
            }
        }

        $relatorioPath = $this->gravarRelatorio($relatorio, $empresaId);
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Processadas', count($linhas)],
                [$dryRun ? 'Seriam atualizadas' : 'Atualizadas', $ok],
                ['Sem alteração', $semAlteracao],
                ['Erros / não encontrados', $erros],
                ['Relatório', $relatorioPath],
            ]
        );

        if ($erros > 0) {
            $this->warn("Há {$erros} linha(s) com erro. Veja o relatório CSV.");
        }

        return $erros > 0 && $ok === 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return list<array{linha:int,centro_custo_id:int,filial:bool,nome:string,cpf:string}>
     */
    private function lerPlanilha(string $arquivo): array
    {
        $spreadsheet = IOFactory::load($arquivo);
        $sheet = $spreadsheet->getSheetByName('Planilha1') ?? $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if ($rows === []) {
            return [];
        }

        $header = array_map(static fn ($v) => mb_strtolower(trim((string) $v)), $rows[0] ?? []);
        $idxCc = array_search('centro_custo_id', $header, true);
        $idxFilial = array_search('filial', $header, true);
        $idxNome = array_search('nome', $header, true);
        $idxCpf = array_search('cpf', $header, true);

        if ($idxCc === false || $idxFilial === false || $idxCpf === false) {
            return [];
        }

        $out = [];
        foreach (array_slice($rows, 1) as $i => $row) {
            $cpfRaw = trim((string) ($row[$idxCpf] ?? ''));
            if ($cpfRaw === '') {
                continue;
            }

            $cpf = Sistema::mascaraCpf($cpfRaw);
            if ($cpf === '..-' || strlen(preg_replace('/\D/', '', $cpfRaw)) !== 11) {
                continue;
            }

            $filialRaw = mb_strtoupper(trim((string) ($row[$idxFilial] ?? 'N')));
            $out[] = [
                'linha' => $i + 2,
                'centro_custo_id' => (int) ($row[$idxCc] ?? 0),
                'filial' => in_array($filialRaw, ['S', 'SIM', '1', 'TRUE'], true),
                'nome' => trim((string) ($row[$idxNome] ?? '')),
                'cpf' => $cpf,
            ];
        }

        return $out;
    }

    /**
     * @return array<int, true>
     */
    private function carregarCentrosCusto(int $empresaId): array
    {
        $ids = DB::table('centro_custos')
            ->where('empresa_id', $empresaId)
            ->pluck('id')
            ->all();

        return array_fill_keys(array_map('intval', $ids), true);
    }

    /**
     * @param  list<string>  $cpfs
     * @return array<string, object{admissao_id:int,centro_custo_id:?int,filial:mixed}>
     */
    private function carregarAdmissoesPorCpf(int $empresaId, array $cpfs): array
    {
        $cpfs = array_values(array_unique($cpfs));
        $map = [];

        foreach (array_chunk($cpfs, 500) as $chunk) {
            $rows = DB::table('admissoes as a')
                ->join('feedback_curriculos as f', 'f.id', '=', 'a.feedback_id')
                ->join('curriculos as c', 'c.id', '=', 'f.curriculo_id')
                ->where('f.empresa_id', $empresaId)
                ->whereIn('c.cpf', $chunk)
                ->whereNull('a.deleted_at')
                ->whereNull('f.deleted_at')
                ->whereNull('c.deleted_at')
                ->orderByDesc('a.id')
                ->select([
                    'c.cpf',
                    'a.id as admissao_id',
                    'a.centro_custo_id',
                    'a.filial',
                ])
                ->get();

            foreach ($rows as $row) {
                // Mantém a admissão mais recente por CPF (orderByDesc + first-write).
                if (!isset($map[$row->cpf])) {
                    $map[$row->cpf] = $row;
                }
            }
        }

        return $map;
    }

    /**
     * @param  array{linha:int,centro_custo_id:int,filial:bool,nome:string,cpf:string}  $linha
     * @param  array<int, true>  $ccsValidos
     * @param  array<string, object>  $admissoesPorCpf
     * @return array<string, mixed>
     */
    private function processarLinha(
        array $linha,
        int $empresaId,
        array $ccsValidos,
        array $admissoesPorCpf,
        bool $dryRun
    ): array {
        $base = [
            'linha' => $linha['linha'],
            'cpf' => $this->mascararCpf($linha['cpf']),
            'nome' => $linha['nome'],
            'centro_custo_id' => $linha['centro_custo_id'],
            'filial' => $linha['filial'] ? 'S' : 'N',
            'admissao_id' => null,
            'status' => 'erro',
            'mensagem' => '',
        ];

        if ($linha['centro_custo_id'] <= 0) {
            $base['mensagem'] = 'centro_custo_id inválido';

            return $base;
        }

        if (!isset($ccsValidos[$linha['centro_custo_id']])) {
            $base['mensagem'] = "Centro de custo {$linha['centro_custo_id']} não pertence à empresa {$empresaId}";

            return $base;
        }

        $admissao = $admissoesPorCpf[$linha['cpf']] ?? null;

        if (!$admissao) {
            $base['mensagem'] = 'Admissão não encontrada para o CPF na empresa';

            return $base;
        }

        $base['admissao_id'] = (int) $admissao->admissao_id;

        $filialAtual = (bool) $admissao->filial;
        $ccAtual = $admissao->centro_custo_id !== null ? (int) $admissao->centro_custo_id : null;

        if ($ccAtual === $linha['centro_custo_id'] && $filialAtual === $linha['filial']) {
            $base['status'] = 'sem_alteracao';
            $base['mensagem'] = 'Já está com os valores da planilha';

            return $base;
        }

        $payload = [
            'centro_custo_id' => $linha['centro_custo_id'],
            'filial' => $linha['filial'],
            'updated_at' => now(),
        ];

        if ($dryRun) {
            $base['status'] = 'dry_run';
            $base['mensagem'] = sprintf(
                'Seria atualizado: cc %s→%s | filial %s→%s',
                $ccAtual ?? 'null',
                $linha['centro_custo_id'],
                $filialAtual ? 'S' : 'N',
                $linha['filial'] ? 'S' : 'N'
            );

            return $base;
        }

        DB::table('admissoes')
            ->where('id', $admissao->admissao_id)
            ->update($payload);

        $base['status'] = 'atualizado';
        $base['mensagem'] = sprintf(
            'Atualizado: cc %s→%s | filial %s→%s',
            $ccAtual ?? 'null',
            $linha['centro_custo_id'],
            $filialAtual ? 'S' : 'N',
            $linha['filial'] ? 'S' : 'N'
        );

        return $base;
    }

    /**
     * @param  list<array<string, mixed>>  $relatorio
     */
    private function gravarRelatorio(array $relatorio, int $empresaId): string
    {
        $custom = $this->option('relatorio');
        if ($custom) {
            $path = $this->resolverPath((string) $custom);
        } else {
            $dir = storage_path('app/relatorios');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $path = $dir.'/atualizar_cc_filial_montisol_'.$empresaId.'_'.now()->format('Ymd_His').'.csv';
        }

        $fh = fopen($path, 'w');
        if ($fh === false) {
            throw new \RuntimeException("Não foi possível gravar o relatório: {$path}");
        }

        fputcsv($fh, ['linha', 'cpf', 'nome', 'centro_custo_id', 'filial', 'admissao_id', 'status', 'mensagem'], ';');
        foreach ($relatorio as $row) {
            fputcsv($fh, [
                $row['linha'],
                $row['cpf'],
                $row['nome'],
                $row['centro_custo_id'],
                $row['filial'],
                $row['admissao_id'],
                $row['status'],
                $row['mensagem'],
            ], ';');
        }
        fclose($fh);

        return $path;
    }

    private function resolverPath(string $path): string
    {
        if ($path === '') {
            return base_path();
        }
        if ($path[0] === '/' || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1) {
            return $path;
        }

        return base_path($path);
    }

    private function mascararCpf(string $cpf): string
    {
        $digits = preg_replace('/\D/', '', $cpf) ?? '';
        if (strlen($digits) !== 11) {
            return '***.***.***-**';
        }

        return '***'.substr($digits, 3, 3).'***-'.substr($digits, 9, 2);
    }
}
