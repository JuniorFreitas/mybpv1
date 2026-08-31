<?php

namespace App\Http\Controllers;

use App\Models\AniversarianteMensagemTemplate;
use App\Services\Aniversariante\AniversarianteMensagemRenderer;
use App\Services\Aniversariante\AniversarianteMensagemResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AniversarianteMensagemController extends Controller
{
    public function dados(AniversarianteMensagemResolver $resolver)
    {
        $this->authorize('administracao_aniversariantes');

        $empresaId = (int) auth()->user()->empresa_id;
        $template = AniversarianteMensagemTemplate::where('empresa_id', $empresaId)->first();
        $htmlSalvo = (string) ($template->conteudo_html ?? '');
        $temCustom = $resolver->temConteudoUtil($htmlSalvo);

        return response()->json([
            'template' => [
                'conteudo_html' => $temCustom ? $htmlSalvo : $resolver->conteudoHtml($empresaId),
                'is_padrao' => ! $temCustom,
            ],
            'placeholders' => ['{{nome}}', '{{empresa}}'],
        ]);
    }

    public function salvar(Request $request, AniversarianteMensagemResolver $resolver)
    {
        $this->authorize('administracao_aniversariantes');

        $request->validate([
            'conteudo_html' => 'required|string',
        ]);

        $html = trim((string) $request->input('conteudo_html'));
        if ($html === '') {
            return response()->json([
                'msg' => 'Informe o conteúdo da mensagem.',
                'erros' => ['conteudo_html' => ['O conteúdo da mensagem é obrigatório.']],
            ], 422);
        }

        $empresaId = (int) auth()->user()->empresa_id;

        try {
            DB::beginTransaction();

            $existente = AniversarianteMensagemTemplate::where('empresa_id', $empresaId)->first();
            $template = AniversarianteMensagemTemplate::updateOrCreate(
                ['empresa_id' => $empresaId],
                [
                    'conteudo_html' => $html,
                    'atualizado_por' => auth()->id(),
                    'criado_por' => $existente->criado_por ?? auth()->id(),
                ]
            );

            $resolver->forgetCache($empresaId);

            DB::commit();

            return response()->json([
                'success' => true,
                'template' => $template,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao salvar mensagem de aniversariante', [
                'empresa_id' => $empresaId,
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'msg' => 'Não foi possível salvar a mensagem. Tente novamente.',
            ], 500);
        }
    }

    public function preview(Request $request, AniversarianteMensagemRenderer $renderer)
    {
        $this->authorize('administracao_aniversariantes');

        $request->validate([
            'conteudo_html' => 'required|string',
        ]);

        $empresaNome = auth()->user()->nome ?? '';

        $html = $renderer->render($request->input('conteudo_html'), [
            'nome' => 'João da Silva',
            'empresa' => $empresaNome,
        ]);

        return response()->json([
            'html' => $html,
        ]);
    }
}
