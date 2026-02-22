<?php

namespace App\Http\Controllers;

use App\Models\AvaliacaoNoventaDias;
use App\Models\AvaliacaoNoventaFeedback;
use App\Models\AvaliacaoNoventaFeedbackQuantidade;
use App\Models\AvaliacaoNoventaVencimento;
use App\Models\Sistema;
use App\Services\AvaliacaoNoventaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;

class AvaliacaoPublicaController extends Controller
{
    protected $avaliacaoService;

    public function __construct(AvaliacaoNoventaService $avaliacaoService)
    {
        $this->avaliacaoService = $avaliacaoService;
    }

    /**
     * Exibe formulário de avaliação via token (requer autenticação)
     *
     * @param string $token
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function mostrarFormulario(string $token)
    {
        $validacao = $this->avaliacaoService->validarTokenAvaliacao($token, auth()->user()->empresa_id);
        

        if (!$validacao['valid']) {
            return view('public.avaliacao90dias.erro', [
                'mensagem' => $validacao['mensagem'],
                'token' => $token
            ]);
        }

        $vencimento = $validacao['vencimento'];
        // Autorização: somente o gestor do Centro de Custo ou RH com privilégio podem acessar
        $user = auth()->user();
        $temPermissaoGestaoRh = in_array('privilegio_gestao_rh', $user->listaDeHabilidades());
        $gestorIdCentroCusto = optional(optional(optional($vencimento->FeedbackCurriculo)->Admissao)->CentroCusto)->gestor_id;
        $ehGestorCentroCusto = $user && $gestorIdCentroCusto && ($user->id === $gestorIdCentroCusto);

        // Se não tem permissão RH e (não tem gestor definido OU não é o gestor), bloqueia
        if (!$temPermissaoGestaoRh && (!$gestorIdCentroCusto || !$ehGestorCentroCusto)) {
            $mensagemErro = !$gestorIdCentroCusto 
                ? 'Centro de custo sem gestor definido. Somente RH pode realizar esta avaliação.'
                : 'Acesso não permitido. Apenas o gestor do Centro de Custo ou RH com privilégio podem realizar esta avaliação.';
            
            Log::warning('Acesso negado ao formulário de Avaliação 90 dias', [
                'user_id' => $user ? $user->id : null,
                'feedback_id' => $vencimento->feedback_id,
                'gestor_centro_custo' => $gestorIdCentroCusto,
                'motivo' => !$gestorIdCentroCusto ? 'gestor_null' : 'nao_eh_gestor'
            ]);
            return view('public.avaliacao90dias.erro', [
                'mensagem' => $mensagemErro,
                'token' => $token
            ]);
        }
        $feedback = $vencimento->FeedbackCurriculo;
        $admissao = $feedback->Admissao;
        $colaborador = $feedback->Curriculo;

        // Busca perguntas do formulário (mesma estrutura do HistoricoController)
        $perguntas = AvaliacaoNoventaDias::get()->transform(function ($item) {
            $item->nota = '';
            return $item;
        });

        // Verifica qual avaliação está sendo feita (1ª ou 2ª)
        $qntAvaliacoes = $vencimento->qntFeedback()->count();
        $numeroAvaliacao = $qntAvaliacoes + 1;

        // Logo da empresa do usuário logado
        $empresaId = auth()->user()->empresa_id ?? null;
        $dadosEmpresa = $empresaId ? Sistema::getEmpresa($empresaId) : [];
        $logo = $dadosEmpresa['logo'] ?? null;

        return view('public.avaliacao90dias.formulario', [
            'token' => $token,
            'colaborador' => $colaborador,
            'admissao' => $admissao,
            'centro_custo' => $admissao->CentroCusto->label ?? 'Não informado',
            'perguntas' => $perguntas,
            'numero_avaliacao' => $numeroAvaliacao,
            'prazo_inicial' => $vencimento->prazo_dia_inicial,
            'prazo_final' => $vencimento->prazo_dia_final,
            'expiracao' => $vencimento->token_expiracao,
            'ehGestorCentroCusto' => $ehGestorCentroCusto,
            'logo' => $logo,
        ]);
    }

    /**
     * Processa submissão do formulário de avaliação
     *
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function salvarAvaliacao(Request $request, string $token)
    {
    // Valida token novamente (sem filtrar por empresa para evitar falso negativo; autorização é conferida abaixo)
    $validacao = $this->avaliacaoService->validarTokenAvaliacao($token);

        if (!$validacao['valid']) {
            // Log detalhado para diagnosticar causa da invalidação
            try {
                $raw = AvaliacaoNoventaVencimento::where('token_avaliacao', $token)
                    ->with(['FeedbackCurriculo:id,empresa_id'])
                    ->first();
                Log::warning('Token inválido ao salvar Avaliação 90 dias', [
                    'token_prefix' => substr($token, 0, 10) . '...',
                    'empresa_request' => auth()->user()->empresa_id ?? null,
                    'existe' => (bool) $raw,
                    'empresa_token' => $raw && $raw->FeedbackCurriculo ? $raw->FeedbackCurriculo->empresa_id : null,
                    'expirado' => $raw && $raw->token_expiracao ? \Carbon\Carbon::parse($raw->token_expiracao)->isPast() : null,
                    'avaliacao_realizada' => $raw ? (bool) $raw->avaliacao_realizada : null,
                    'mensagem' => $validacao['mensagem'] ?? null,
                ]);
            } catch (\Throwable $e) {
                Log::error('Falha ao inspecionar token inválido', [
                    'token_prefix' => substr($token, 0, 10) . '...',
                    'error' => $e->getMessage(),
                ]);
            }

            return redirect()->route('avaliacao.publica.erro', ['token' => $token])
                ->with('error', $validacao['mensagem']);
        }

        // Autorização novamente no momento do POST
        $vencimento = $validacao['vencimento'];
        $user = auth()->user();
        $temPermissaoGestaoRh = in_array('privilegio_gestao_rh', $user->listaDeHabilidades());
        $gestorIdCentroCusto = optional(optional(optional($vencimento->FeedbackCurriculo)->Admissao)->CentroCusto)->gestor_id;
        $ehGestorCentroCusto = $user && $gestorIdCentroCusto && ($user->id === $gestorIdCentroCusto);

        // Se não tem permissão RH e (não tem gestor definido OU não é o gestor), bloqueia
        if (!$temPermissaoGestaoRh && (!$gestorIdCentroCusto || !$ehGestorCentroCusto)) {
            $mensagemErro = !$gestorIdCentroCusto 
                ? 'Centro de custo sem gestor definido. Somente RH pode realizar esta avaliação.'
                : 'Acesso não permitido. Apenas o gestor do Centro de Custo ou RH com privilégio podem realizar esta avaliação.';
            
            Log::warning('Tentativa não autorizada de salvar Avaliação 90 dias', [
                'user_id' => $user ? $user->id : null,
                'feedback_id' => $vencimento->feedback_id,
                'gestor_centro_custo' => $gestorIdCentroCusto,
                'motivo' => !$gestorIdCentroCusto ? 'gestor_null' : 'nao_eh_gestor'
            ]);
            return redirect()->route('avaliacao.publica.erro', ['token' => $token])
                ->with('error', $mensagemErro);
        }
        $request['gestor_imediato'] = $ehGestorCentroCusto ? $user->nome : $request['gestor_imediato'];

        // Validação dos dados do formulário
        $validator = Validator::make($request->all(), [
            'gestor_imediato' => 'required|string|max:255',
            'observacao' => 'nullable|string|max:5000',
            'definicao_contrato' => 'required|in:prorroga,finaliza',
            'perguntas' => 'required|array',
            'perguntas.*.id' => 'required|integer',
            'perguntas.*.nota' => 'required|integer|min:1|max:5'
        ], [
            'gestor_imediato.required' => 'O campo Gestor Imediato é obrigatório',
            'definicao_contrato.required' => 'Selecione a definição sobre o colaborador (Prorroga ou Finaliza o contrato).',
            'definicao_contrato.in' => 'Definição inválida.',
            'perguntas.required' => 'Todas as perguntas devem ser respondidas',
            'perguntas.*.nota.required' => 'Todas as perguntas devem ter uma nota',
            'perguntas.*.nota.min' => 'A nota mínima é 1',
            'perguntas.*.nota.max' => 'A nota máxima é 5'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $vencimento = $validacao['vencimento'];
            $feedback = $vencimento->FeedbackCurriculo;

            // Cria o registro da avaliação
            $avaliacaoData = [
                'gestor_imediato' => $request->input('gestor_imediato'),
                'observacao' => $request->input('observacao'),
                'definicao_contrato' => $request->input('definicao_contrato'),
                'feedback_id' => $feedback->id,
                'perguntas' => $request->input('perguntas'),
                'gestor_id' => auth()->id()
            ];

            // Salva a avaliação (reutiliza lógica existente do sistema)
            $resultado = $this->salvarAvaliacaoNoventa($avaliacaoData);

            if ($resultado) {
                // Marca token como utilizado
                $this->avaliacaoService->marcarAvaliacaoRealizada($token);

                Log::info('Avaliação 90 dias realizada via token com autenticação', [
                    'token' => substr($token, 0, 10) . '...',
                    'feedback_id' => $feedback->id,
                    'colaborador' => $feedback->Curriculo->nome ?? 'N/A',
                    'gestor' => $request->input('gestor_imediato'),
                    'user_id' => auth()->id()
                ]);

                return view('public.avaliacao90dias.sucesso', [
                    'colaborador' => $feedback->Curriculo,
                    'numero_avaliacao' => $vencimento->qntFeedback()->count()
                ]);
            } else {
                throw new \Exception('Falha ao salvar avaliação');
            }

        } catch (\Exception $e) {
            Log::error('Erro ao processar avaliação pública', [
                'token' => substr($token, 0, 10) . '...',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao salvar avaliação. Por favor, tente novamente.')
                ->withInput();
        }
    }

    /**
     * Salva avaliação de noventa dias no banco (mesma lógica do HistoricoController)
     *
     * @param array $dados
     * @return bool
     */
    private function salvarAvaliacaoNoventa(array $dados): bool
    {
        try {
            DB::beginTransaction();

            // Calcula número da avaliação (1ª ou 2ª)
            $total = AvaliacaoNoventaFeedbackQuantidade::where('feedback_id', $dados['feedback_id'])
                ->sum('quantidade_avaliacao');
            $qntAvaliacao = $total > 0 ? intval($total) + 1 : 1;

            // Cria registro de quantidade (inclui definição: prorroga/finaliza)
            AvaliacaoNoventaFeedbackQuantidade::create([
                'feedback_id' => $dados['feedback_id'],
                'quantidade_avaliacao' => $qntAvaliacao,
                'definicao_contrato' => $dados['definicao_contrato'] ?? null,
            ]);

            // Salva cada resposta de pergunta
            foreach ($dados['perguntas'] as $form) {
                AvaliacaoNoventaFeedback::create([
                    'feedback_id' => $dados['feedback_id'],
                    'pergunta_id' => $form['id'],
                    'gestor_id' => $dados['gestor_id'] ?? null, // ID do usuário autenticado
                    'nota' => $form['nota'],
                    'quantidade_avaliacao' => $qntAvaliacao,
                    'gestor_imediato' => $dados['gestor_imediato'],
                    'observacao' => $dados['observacao'] ?? null,
                ]);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao salvar avaliação noventa dias via token público', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'dados' => $dados
            ]);
            
            return false;
        }
    }

    /**
     * Página de erro para token inválido
     *
     * @param string $token
     * @return \Illuminate\View\View
     */
    public function exibirErro(string $token)
    {
        $mensagem = session('error') ?: 'Token inválido ou expirado';
        return view('public.avaliacao90dias.erro', [
            'mensagem' => $mensagem,
            'token' => $token
        ]);
    }
}
