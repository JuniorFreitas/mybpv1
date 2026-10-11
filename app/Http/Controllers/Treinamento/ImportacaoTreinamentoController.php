<?php

namespace App\Http\Controllers\Treinamento;

use App\Http\Controllers\Controller;
use App\Jobs\Treinamento\Importacao\ImportacaoTreinamentoJob;
use App\Models\Arquivo;
use App\Models\Treinamento\ImportacaoTreinamento;
use App\Services\Treinamento\Importacao\GeradorModeloImportacaoTreinamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportacaoTreinamentoController extends Controller
{
    public function index()
    {
        return view('g.treinamentos.import.index');
    }

    public function downloadModelo(GeradorModeloImportacaoTreinamento $gerador): StreamedResponse
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);
        if ($empresaId < 1) {
            abort(403, 'Empresa não autorizada.');
        }

        $modelo = $gerador->gerar($empresaId);

        return response()->streamDownload(function () use ($modelo) {
            echo $modelo['binario'];
        }, $modelo['nome_arquivo'], [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'planilha' => ['required', 'file', 'mimes:xlsx', 'max:20480'],
            'empresa_id' => ['required', 'integer', 'min:1'],
        ]);

        $empresaId = (int) $request->input('empresa_id');
        $user = auth()->user();
        if ($user && $user->empresa_id !== null && (int) $user->empresa_id !== $empresaId) {
            throw ValidationException::withMessages(['empresa_id' => ['Empresa não autorizada.']]);
        }

        $file = $request->file('planilha');
        $uuid = Str::uuid()->toString();
        $dir = 'importacao_treinamentos/' . $empresaId;
        $path = $file->storeAs($dir, $uuid . '.xlsx', 'local');
        if ($path === false) {
            return response()->json(['msg' => 'Erro ao salvar o arquivo.', 'status' => 'error'], 500);
        }

        $caminhoS3 = $dir . '/' . $uuid . '.xlsx';
        try {
            $conteudo = Storage::disk('local')->get($path);
            Storage::disk(Arquivo::DISCO_EXPORTACAO)->put($caminhoS3, $conteudo);
        } catch (\Throwable $e) {
            \Log::warning('Importação treinamentos: falha ao enviar planilha para S3 no upload', [
                'path' => $path,
                'caminho_s3' => $caminhoS3,
                'erro' => $e->getMessage(),
            ]);

            return response()->json(['msg' => 'Erro ao enviar arquivo para o storage.', 'status' => 'error'], 500);
        }

        ImportacaoTreinamento::create([
            'empresa_id' => $empresaId,
            'user_id' => $user ? (int) $user->id : null,
            'uuid' => $uuid,
            'status' => ImportacaoTreinamento::STATUS_PROCESSANDO,
        ]);

        ImportacaoTreinamentoJob::dispatch(
            $path,
            $empresaId,
            $user ? (int) $user->id : null,
            $uuid,
            $caminhoS3
        );

        return response()->json([
            'msg' => 'Importação enfileirada. Ao concluir, o resultado fica na aba Importados (CSV de sucesso e erro), no sino e por e-mail.',
            'status' => 'success',
            'uuid' => $uuid,
        ], 202);
    }

    public function historico(Request $request): JsonResponse
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);
        if ($empresaId < 1) {
            abort(403, 'Empresa não autorizada.');
        }

        $itens = ImportacaoTreinamento::query()
            ->where('empresa_id', $empresaId)
            ->orderByDesc('id')
            ->limit(50)
            ->get([
                'id',
                'uuid',
                'status',
                'total',
                'sucesso',
                'erros',
                'arquivo_sucesso',
                'arquivo_erro',
                'mensagem_erro',
                'created_at',
            ]);

        $lista = $itens->map(function (ImportacaoTreinamento $item) {
            return [
                'id' => $item->id,
                'uuid' => $item->uuid,
                'status' => $item->status,
                'total' => $item->total,
                'sucesso' => $item->sucesso,
                'erros' => $item->erros,
                'tem_csv_sucesso' => !empty($item->arquivo_sucesso),
                'tem_csv_erro' => !empty($item->arquivo_erro),
                'mensagem_erro' => $item->mensagem_erro,
                'created_at_br' => $item->created_at_br,
            ];
        })->values();

        return response()->json(['data' => $lista]);
    }

    public function downloadCsv(int $importacao, string $tipo)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);
        if ($empresaId < 1) {
            abort(403, 'Empresa não autorizada.');
        }

        if (!in_array($tipo, ['sucesso', 'erro'], true)) {
            abort(404);
        }

        $registro = ImportacaoTreinamento::query()
            ->where('id', $importacao)
            ->where('empresa_id', $empresaId)
            ->firstOrFail();

        $caminho = $tipo === 'sucesso' ? $registro->arquivo_sucesso : $registro->arquivo_erro;
        if (empty($caminho) || !Storage::disk(Arquivo::DISCO_EXPORTACAO)->exists($caminho)) {
            abort(404, 'Arquivo não encontrado.');
        }

        $nome = sprintf(
            'importacao_treinamentos_%s_%s.csv',
            $registro->uuid,
            $tipo
        );

        return Storage::disk(Arquivo::DISCO_EXPORTACAO)->download($caminho, $nome, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
