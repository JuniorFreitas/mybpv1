<?php

namespace App\Http\Controllers;

use App\Jobs\JobEnviaZap;
use App\Jobs\JobExportaExcel;
use App\Jobs\Recrutamento\JobDesclassificacao;
use App\Jobs\Recrutamento\JobProva;
use App\Jobs\Recrutamento\JobProximaEtapa;
use App\Models\Cliente;
use App\Models\ClienteConfig;
use App\Models\Curriculo;
use App\Models\RecrutamentoHistorico;
use App\Models\SimuladoVaga;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use App\Models\VagasAbertas;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use MasterTag\DataHora;
use PDF;

class RecrutamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $curriculos = Curriculo::count();
        return view('g.curriculos.recrutamento.index', compact('curriculos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($curriculo)
    {
        $recrutamento = Curriculo::where('id', \Crypt::decrypt($curriculo))->first();
        $pdf = PDF::loadView('pdf.recrutamento.curriculo', compact('recrutamento'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("curriculo" . Str::slug($recrutamento->nome) . ".pdf");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Curriculo
     */
    public function edit(Curriculo $recrutamento)
    {
        $recrutamento->estado_civil = $recrutamento->estado_civil ?? '';
        $recrutamento->sexo = $recrutamento->sexo ?? '';
        
        return $recrutamento->load('Atualizacao', 'Qualificacoes', 'Experiencias', 'VagaAberta.VagaSelecionada', 'Formacao', 'Telefones', 'Usuario')->load(['FeedBack' => function ($query) {
            $query->with('VagaAberta.VagaSelecionada.SimuladoVaga', 'VagaAberta.Municipio', 'Cliente', 'QuemMarcou', 'TelPrincipal');
        }]);
    }

    /**
     * @param Request $request
     * @param Curriculo $recrutamento
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function update(Request $request, Curriculo $recrutamento)
    {
        try {
            DB::beginTransaction();
            
            $requestData = $this->prepareRequestData($request->input());
            $curriculumData = $requestData['curriculos'];
            $candidato = Curriculo::find($curriculumData['id']);
            $empresa = Cliente::find(auth()->user()->empresa_id);
            
            // Capturar dados antes da atualização para o histórico
            $dadosAnteriores = $candidato->toArray();
            
            // Atualizar dados do currículo
            $this->updateCurriculumData($candidato, $curriculumData);
            
            // Registrar histórico da atualização do currículo
            RecrutamentoHistorico::registrar(
                $candidato->id,
                RecrutamentoHistorico::ACAO_ATUALIZADO,
                RecrutamentoHistorico::MODULO_CURRICULO,
                null,
                "Dados do currículo foram atualizados",
                $dadosAnteriores,
                $candidato->fresh()->toArray(),
                $request->all()
            );
            
            // Processar telefones
            $this->processPhones($candidato, $curriculumData, $requestData);
            
            // Configurar dados de envio
            $requestData = $this->configureEmailAndWhatsappData($requestData);
            
            // Processar feedback do candidato
            $this->processCandidateFeedback($recrutamento, $requestData, $curriculumData, $empresa);
            
            DB::commit();
            return response()->json([], 201);
            
        } catch (\Exception $e) {
            DB::rollback();
            $errorMessage = "error FEEDBACK: {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($errorMessage);
            return response()->json(['msg' => $errorMessage], 400);
        }
    }

    /**
     * Prepara e normaliza os dados da requisição
     */
    private function prepareRequestData(array $input): array
    {
        $input['cliente_id'] = auth()->user()->empresa_id;
        $input['contato_realizado'] = $input['contato_realizado'] == 'true';
        $input['interesse'] = $input['interesse'] == 'true';
        $input['data_entrevista'] = $input['interesse'] ? $input['data_entrevista'] : null;
        $input['tem_provas'] = $input['tem_provas'] == 'true';
        $input['envia_mail_provas'] = $input['envia_mail_provas'] == 'true';
        $input['envia_mail_proxima_etapa'] = $input['envia_mail_proxima_etapa'] == 'true';
        $input['envia_mail_desclassificacao'] = $input['envia_mail_desclassificacao'] == 'true';
        
        return $input;
    }

    /**
     * Atualiza os dados principais do currículo
     */
    private function updateCurriculumData(Curriculo $candidato, array $curriculumData): void
    {
        $updateData = [
            'nome' => $curriculumData['nome'],
            'rg' => $curriculumData['rg'],
            'orgao_expeditor' => $curriculumData['orgao_expeditor'],
            'cnh' => $curriculumData['cnh'],
            'nascimento' => $curriculumData['nascimento'],
            'filiacao_pai' => $curriculumData['filiacao_pai'],
            'filiacao_mae' => $curriculumData['filiacao_mae'],
            'email' => $curriculumData['email'],
            'cep' => $curriculumData['cep'],
            'logradouro' => $curriculumData['logradouro'],
            'bairro' => $curriculumData['bairro'],
            'municipio' => $curriculumData['municipio'],
            'uf' => $curriculumData['uf'],
            'sexo' => $curriculumData['sexo'],
            'estado_civil' => $curriculumData['estado_civil'],
        ];

        $candidato->update($updateData);
    }

    /**
     * Processa a adição, edição e remoção de telefones
     */
    private function processPhones(Curriculo $candidato, array $curriculumData, array &$requestData): void
    {
        // Remover telefones marcados para exclusão
        if (isset($curriculumData['telefonesDelete'])) {
            foreach ($curriculumData['telefonesDelete'] as $phoneId) {
                $telefone = TelefoneCurriculo::find($phoneId);
                if ($telefone) {
                    // Registrar histórico antes de deletar
                    RecrutamentoHistorico::registrar(
                        $candidato->id,
                        RecrutamentoHistorico::ACAO_TELEFONE_REMOVIDO,
                        RecrutamentoHistorico::MODULO_TELEFONE,
                        null,
                        "Telefone {$telefone->numero} foi removido",
                        $telefone->toArray(),
                        null
                    );
                    
                    $telefone->delete();
                }
            }
        }

        // Processar telefones (novos e existentes)
        if (isset($curriculumData['telefones'])) {
            foreach ($curriculumData['telefones'] as $phoneData) {
                $phoneData['principal'] = $phoneData['principal'] == 'true';
                
                if ($phoneData['id'] == 0) {
                    // Criar novo telefone
                    $newPhone = $candidato->Telefones()->create($phoneData);
                    $newPhoneId = $newPhone->id;
                    
                    // Registrar histórico da criação
                    RecrutamentoHistorico::registrar(
                        $candidato->id,
                        RecrutamentoHistorico::ACAO_TELEFONE_ADICIONADO,
                        RecrutamentoHistorico::MODULO_TELEFONE,
                        null,
                        "Telefone {$phoneData['numero']} foi adicionado",
                        null,
                        $newPhone->toArray()
                    );
                    
                    if ($phoneData['principal']) {
                        $requestData['telefone_id'] = $newPhoneId;
                    }
                } else {
                    // Atualizar telefone existente
                    $telefoneExistente = $candidato->Telefones->find($phoneData['id']);
                    $dadosAnteriores = $telefoneExistente->toArray();
                    
                    $telefoneExistente->update($phoneData);
                    
                    // Registrar histórico da atualização
                    RecrutamentoHistorico::registrar(
                        $candidato->id,
                        RecrutamentoHistorico::ACAO_TELEFONE_ATUALIZADO,
                        RecrutamentoHistorico::MODULO_TELEFONE,
                        null,
                        "Telefone {$phoneData['numero']} foi atualizado",
                        $dadosAnteriores,
                        $telefoneExistente->fresh()->toArray()
                    );
                    
                    if ($phoneData['principal']) {
                        $requestData['telefone_id'] = $phoneData['id'];
                    }
                }
            }
        }
    }

    /**
     * Configura os dados de envio de emails e WhatsApp
     */
    private function configureEmailAndWhatsappData(array $requestData): array
    {
        // Resetar dados de envio
        $requestData['data_envia_mail_desclassificacao'] = null;
        $requestData['user_envia_mail_desclassificacao'] = null;
        $requestData['data_envia_mail_proxima_etapa'] = null;
        $requestData['user_envia_mail_proxima_etapa'] = null;
        $requestData['data_envia_mail_provas'] = null;
        $requestData['user_envia_mail_provas'] = null;
        $requestData['data_envia_whatsapp'] = null;
        $requestData['user_envia_whatsapp'] = null;

        // Configurar WhatsApp se permitido
        $whatsappConfig = ClienteConfig::whereClienteId(auth()->user()->empresa_id)->first();
        $whatsappEnabled = !empty($whatsappConfig) && $whatsappConfig->envia_whatsapp;

        if ($requestData['contato_realizado'] && $whatsappEnabled) {
            $requestData['envia_whatsapp'] = $requestData['envia_whatsapp'] == 'true';
            $requestData['data_envia_whatsapp'] = $requestData['envia_whatsapp'] ? (new DataHora())->dataHoraInsert() : null;
            $requestData['user_envia_whatsapp'] = $requestData['envia_whatsapp'] ? auth()->id() : null;
        } else {
            $requestData['envia_whatsapp'] = null;
            $requestData['data_envia_whatsapp'] = null;
            $requestData['user_envia_whatsapp'] = null;
        }

        return $requestData;
    }

    /**
     * Processa o feedback do candidato (novo ou atualização)
     */
    private function processCandidateFeedback(Curriculo $curriculo, array $requestData, array $curriculumData, Cliente $empresa): void
    {
        if (is_null($curriculo->FeedBack)) {
            $this->createNewFeedback($curriculo, $requestData, $curriculumData, $empresa);
        } else {
            $this->updateExistingFeedback($curriculo, $requestData, $curriculumData, $empresa);
        }
    }

    /**
     * Cria novo feedback para o candidato
     */
    private function createNewFeedback(Curriculo $curriculo, array $requestData, array $curriculumData, Cliente $empresa): void
    {
        if ($requestData['selecionado'] == 'nao') {
            $this->processRejection($requestData, $curriculumData, $empresa);
            $feedback = $curriculo->FeedBack()->create($requestData);
            
            // Registrar histórico da rejeição
            RecrutamentoHistorico::registrar(
                $curriculo->id,
                RecrutamentoHistorico::ACAO_REJEITADO,
                RecrutamentoHistorico::MODULO_FEEDBACK,
                $feedback->id,
                "Candidato foi rejeitado",
                null,
                $feedback->toArray()
            );
        } else {
            $this->processSelection($requestData, $curriculumData, $empresa, $curriculo);
            
            $requestData['vagas_abertas_id'] = $requestData['vaga_id'];
            $requestData['vaga_id'] = VagasAbertas::find($requestData['vagas_abertas_id'])->vaga_id;
            
            $feedback = $curriculo->FeedBack()->create($requestData);
            
            // Registrar histórico da seleção
            RecrutamentoHistorico::registrar(
                $curriculo->id,
                RecrutamentoHistorico::ACAO_SELECIONADO,
                RecrutamentoHistorico::MODULO_FEEDBACK,
                $feedback->id,
                "Candidato foi selecionado",
                null,
                $feedback->toArray()
            );
        }
    }

    /**
     * Garante que sempre haja uma vaga associada ao feedback
     */
    private function ensureJobAssociation(array $requestData, Curriculo $curriculo): array
    {
        // Se vaga_id está presente na requisição, usar ela
        if (!empty($requestData['vaga_id'])) {
            $requestData['vagas_abertas_id'] = $requestData['vaga_id'];
            
            // Buscar a vaga real para garantir que vaga_id está correto
            $vagaAberta = VagasAbertas::find($requestData['vaga_id']);
            if ($vagaAberta) {
                $requestData['vaga_id'] = $vagaAberta->vaga_id;
            }
        }
        // Se não há vaga_id mas há vaga no currículo, usar a do currículo
        else if ($curriculo->VagaAberta) {
            $requestData['vagas_abertas_id'] = $curriculo->VagaAberta->id;
            $requestData['vaga_id'] = $curriculo->VagaAberta->vaga_id;
        }
        // Se ainda não há vaga, tentar buscar pelo feedback anterior
        else if ($curriculo->FeedBack && $curriculo->FeedBack->vagas_abertas_id) {
            $requestData['vagas_abertas_id'] = $curriculo->FeedBack->vagas_abertas_id;
            $requestData['vaga_id'] = $curriculo->FeedBack->vaga_id;
        }
        
        // Validar se os dados estão consistentes
        if (!empty($requestData['vagas_abertas_id']) && empty($requestData['vaga_id'])) {
            $vagaAberta = VagasAbertas::find($requestData['vagas_abertas_id']);
            if ($vagaAberta) {
                $requestData['vaga_id'] = $vagaAberta->vaga_id;
            }
        }
        
        return $requestData;
    }

    /**
     * Atualiza feedback existente do candidato
     */
    private function updateExistingFeedback(Curriculo $curriculo, array $requestData, array $curriculumData, Cliente $empresa): void
    {
        // Garantir que sempre haja uma vaga associada
        $requestData = $this->ensureJobAssociation($requestData, $curriculo);
        
        // Capturar dados antes da atualização
        $dadosAnteriores = $curriculo->FeedBack->toArray();
        
        $curriculo->FeedBack->update($requestData);
        
        // Registrar histórico da atualização do feedback
        RecrutamentoHistorico::registrar(
            $curriculo->id,
            RecrutamentoHistorico::ACAO_FEEDBACK_ATUALIZADO,
            RecrutamentoHistorico::MODULO_FEEDBACK,
            $curriculo->FeedBack->id,
            "Feedback do candidato foi atualizado",
            $dadosAnteriores,
            $curriculo->FeedBack->fresh()->toArray()
        );

        if ($requestData['selecionado'] == 'nao') {
            $this->processRejection($requestData, $curriculumData, $empresa);
        } else {
            $this->processSelectionUpdate($requestData, $curriculumData, $empresa, $curriculo);
        }
    }

    /**
     * Processa a rejeição do candidato
     */
    private function processRejection(array &$requestData, array $curriculumData, Cliente $empresa): void
    {
        if ($requestData['envia_mail_desclassificacao']) {
            $requestData['data_envia_mail_desclassificacao'] = (new DataHora())->dataHoraInsert();
            $requestData['user_envia_mail_desclassificacao'] = auth()->id();

            // Registrar histórico do envio de email de desclassificação
            RecrutamentoHistorico::registrar(
                $curriculumData['id'],
                RecrutamentoHistorico::ACAO_EMAIL_ENVIADO,
                RecrutamentoHistorico::MODULO_EMAIL,
                null,
                "Email de desclassificação enviado para {$curriculumData['nome']}"
            );

            JobDesclassificacao::dispatch([
                'nome' => $curriculumData['nome'],
                'email' => $curriculumData['email'],
                'razao_social' => $empresa->razao_social,
                'empresa_id' => $empresa->id,
            ]);
        }
    }

    /**
     * Processa a seleção do candidato (novo feedback)
     */
    private function processSelection(array &$requestData, array $curriculumData, Cliente $empresa, Curriculo $curriculo): void
    {
        if ($requestData['selecionado'] == 'sim') {
            $this->sendNextStepNotification($requestData, $curriculumData, $empresa);
            $this->sendWhatsAppSelectionMessage($requestData, $curriculumData, $empresa, $curriculo);
        }

        if ($requestData['selecionado'] == 'sim' && $requestData['tem_provas']) {
            $this->processExamNotifications($requestData, $curriculumData, $curriculo);
        }
    }

    /**
     * Processa a seleção do candidato (atualização de feedback)
     */
    private function processSelectionUpdate(array &$requestData, array $curriculumData, Cliente $empresa, Curriculo $curriculo): void
    {
        if ($requestData['selecionado'] == 'sim') {
            $this->sendNextStepNotificationUpdate($requestData, $curriculumData, $empresa);
            $vagaAbertaId = isset($requestData['vaga_aberta']['id']) ? $requestData['vaga_aberta']['id'] : $requestData['vagas_abertas_id'];
            $vagaAberta = VagasAbertas::find($vagaAbertaId);
            $this->sendWhatsAppSelectionMessageWithJob($requestData, $curriculumData, $empresa, $curriculo, $vagaAberta);
        }

        if ($requestData['selecionado'] == 'sim' && $requestData['tem_provas']) {
            $this->processExamNotificationsUpdate($requestData, $curriculumData, $curriculo);
        }
    }

    /**
     * Envia notificação da próxima etapa
     */
    private function sendNextStepNotification(array &$requestData, array $curriculumData, Cliente $empresa): void
    {
        if ($requestData['envia_mail_proxima_etapa']) {
            $requestData['data_envia_mail_proxima_etapa'] = (new DataHora())->dataHoraInsert();
            $requestData['user_envia_mail_proxima_etapa'] = auth()->id();
            
            $vagaAbertaId = isset($requestData['vaga_aberta']['id']) ? $requestData['vaga_aberta']['id'] : $requestData['vagas_abertas_id'];
            $vagaAberta = VagasAbertas::find($vagaAbertaId);

            JobProximaEtapa::dispatch([
                'nome' => $curriculumData['nome'],
                'email' => $curriculumData['email'],
                'empresa' => $empresa->razao_social,
                'logo' => env('AWS_URL') . "/public/email_" . $empresa->apelido . ".jpg",
                'vaga_selecionada' => $vagaAberta->titulo . ' -' . $vagaAberta->Municipio->nome . '/' . $vagaAberta->Municipio->uf,
                'local_entrevista' => $requestData['local_entrevista'],
                'data_entrevista' => $requestData['data_entrevista'],
            ]);
        }
    }

    /**
     * Envia notificação da próxima etapa (atualização)
     */
    private function sendNextStepNotificationUpdate(array &$requestData, array $curriculumData, Cliente $empresa): void
    {
        if ($requestData['envia_mail_proxima_etapa']) {
            $requestData['data_envia_mail_provas'] = (new DataHora())->dataHoraInsert();
            $requestData['user_envia_mail_provas'] = auth()->id();
            
            $vagaAbertaId = isset($requestData['vaga_aberta']['id']) ? $requestData['vaga_aberta']['id'] : $requestData['vagas_abertas_id'];

            $vagaAberta = VagasAbertas::find($vagaAbertaId);
            
            
            JobProximaEtapa::dispatch([
                'nome' => $curriculumData['nome'],
                'email' => $curriculumData['email'],
                'empresa' => $empresa->razao_social,
                'logo' => env('AWS_URL') . "/public/email_" . $empresa->apelido . ".jpg",
                'vaga_selecionada' => $vagaAberta->titulo . ' -' . $vagaAberta->Municipio->nome . '/' . $vagaAberta->Municipio->uf,
                'local_entrevista' => $requestData['local_entrevista'],
                'data_entrevista' => $requestData['data_entrevista'],
            ]);
        }
    }

    /**
     * Envia mensagem WhatsApp de seleção
     */
    private function sendWhatsAppSelectionMessage(array $requestData, array $curriculumData, Cliente $empresa, Curriculo $curriculo): void
    {
        $whatsappConfig = ClienteConfig::whereClienteId(auth()->user()->empresa_id)->first();
        $whatsappEnabled = !empty($whatsappConfig) && $whatsappConfig->envia_whatsapp;
        
        if ($requestData['contato_realizado'] && $requestData['envia_whatsapp'] && $whatsappEnabled) {
            $vagaAbertaId = isset($requestData['vaga_aberta']['id']) ? $requestData['vaga_aberta']['id'] : $requestData['vagas_abertas_id'];
            $vagaAberta = VagasAbertas::find($vagaAbertaId);
            $message = $this->buildSelectionWhatsAppMessage($curriculumData, $requestData, $empresa, $vagaAberta);
            
            $telefonePrincipal = TelefoneCurriculo::whereCurriculoId($curriculo->id)->wherePrincipal(true)->first();
            if ($telefonePrincipal && $telefonePrincipal->tipo == 'whatsapp') {
                JobEnviaZap::dispatch([
                    'enviado_id' => $curriculo->id,
                    'telefone' => $telefonePrincipal->sonumero,
                    'mensagem' => $message,
                ]);
            }
        }
    }

    /**
     * Envia mensagem WhatsApp de seleção com job
     */
    private function sendWhatsAppSelectionMessageWithJob(array $requestData, array $curriculumData, Cliente $empresa, Curriculo $curriculo, VagasAbertas $vagaAberta): void
    {
        $whatsappConfig = ClienteConfig::whereClienteId(auth()->user()->empresa_id)->first();
        $whatsappEnabled = !empty($whatsappConfig) && $whatsappConfig->envia_whatsapp;
        
        if ($requestData['contato_realizado'] && $requestData['envia_whatsapp'] && $whatsappEnabled) {
            $message = $this->buildSelectionWhatsAppMessage($curriculumData, $requestData, $empresa, $vagaAberta);
            
            $telefonePrincipal = TelefoneCurriculo::whereCurriculoId($curriculo->id)->wherePrincipal(true)->first();
            if ($telefonePrincipal && $telefonePrincipal->tipo == 'whatsapp') {
                JobEnviaZap::dispatch([
                    'enviado_id' => $curriculo->id,
                    'telefone' => $telefonePrincipal->sonumero,
                    'mensagem' => $message,
                ]);
            }
        }
    }

    /**
     * Constrói a mensagem WhatsApp de seleção
     */
    private function buildSelectionWhatsAppMessage(array $curriculumData, array $requestData, Cliente $empresa, VagasAbertas $vagaAberta): string
    {
        return "👏🏽👏🏽Parabéns, *{$curriculumData['nome']}*. Você foi *selecionado(a)*!\nPara a vaga *{$vagaAberta->titulo} - {$vagaAberta->Municipio->nome}/{$vagaAberta->Municipio->uf}* fique atento as próximas etapas do processo!\n\n📆 Data da entrevista: {$requestData['data_entrevista']}\n📍Local da entrevista: {$requestData['local_entrevista']}\n\nSucesso e esperamos vê-lo em breve. \n\n*☺️ Um forte abraço da equipe " . $empresa->razao_social . "*\n\n_Esta mensagem foi enviada automaticamente pela plataforma *MyBP*, por favor não responda._";
    }

    /**
     * Processa notificações de provas
     */
    private function processExamNotifications(array &$requestData, array $curriculumData, Curriculo $curriculo): void
    {
        if ($requestData['envia_mail_provas']) {
            $requestData['data_envia_mail_provas'] = (new DataHora())->dataHoraInsert();
            $requestData['user_envia_mail_provas'] = auth()->id();
            
            $vagaAbertaId = isset($requestData['vaga_aberta']['id']) ? $requestData['vaga_aberta']['id'] : $requestData['vaga_id'];
            $provas = SimuladoVaga::whereVagasAbertasId($vagaAbertaId)->whereOnline(true)->get();
            
            $this->sendExamWhatsAppMessage($requestData, $curriculumData, $curriculo, $provas);
            
            JobProva::dispatch([
                'nome' => $curriculumData['nome'],
                'email' => $curriculumData['email'],
                'vaga' => $requestData['autocomplete_label_vaga_modal'],
                'vaga_id' => $requestData['vaga_id'],
                'provas' => $provas
            ]);
        } else {
            $requestData['envia_mail_provas'] = false;
            $requestData['data_envia_mail_provas'] = (new DataHora())->dataHoraInsert();
            $requestData['user_envia_mail_provas'] = auth()->id();
        }
    }

    /**
     * Processa notificações de provas (atualização)
     */
    private function processExamNotificationsUpdate(array &$requestData, array $curriculumData, Curriculo $curriculo): void
    {
        if ($requestData['envia_mail_provas']) {
            $requestData['data_envia_mail_provas'] = (new DataHora())->dataHoraInsert();
            $requestData['user_envia_mail_provas'] = auth()->id();
            
            $provas = SimuladoVaga::whereVagasAbertasId($requestData['vagas_abertas_id'])->whereOnline(true)->get();
            
            $this->sendExamWhatsAppMessage($requestData, $curriculumData, $curriculo, $provas);
            
            JobProva::dispatch([
                'nome' => $curriculumData['nome'],
                'email' => $curriculumData['email'],
                'vaga' => $requestData['autocomplete_label_vaga_modal'],
                'vaga_id' => $requestData['vaga_id'],
                'provas' => $provas
            ]);
        }
    }

    /**
     * Envia mensagem WhatsApp sobre provas
     */
    private function sendExamWhatsAppMessage(array $requestData, array $curriculumData, Curriculo $curriculo, $provas): void
    {
        $whatsappConfig = ClienteConfig::whereClienteId(auth()->user()->empresa_id)->first();
        $whatsappEnabled = !empty($whatsappConfig) && $whatsappConfig->envia_whatsapp;
        
        if ($requestData['contato_realizado'] && $requestData['envia_whatsapp'] && $whatsappEnabled) {
            $quantidadeProvas = count($provas);
            $message = $this->buildExamWhatsAppMessage($curriculumData, $requestData, $quantidadeProvas, $provas);
            
            $telefonePrincipal = TelefoneCurriculo::whereCurriculoId($curriculo->id)->wherePrincipal(true)->first();
            if ($telefonePrincipal && $telefonePrincipal->tipo == 'whatsapp') {
                JobEnviaZap::dispatch([
                    'enviado_id' => $curriculo->id,
                    'telefone' => $telefonePrincipal->sonumero,
                    'mensagem' => $message,
                ]);
            }
        }
    }

    /**
     * Constrói a mensagem WhatsApp sobre provas
     */
    private function buildExamWhatsAppMessage(array $curriculumData, array $requestData, int $quantidadeProvas, $provas): string
    {
        if ($quantidadeProvas > 1) {
            $message = "Parabéns, *{$curriculumData['nome']}*. Você foi *selecionado(a)*!\nVocê está recebendo um convite para realizar as avaliações abaixo relacionadas ao seu processo seletivo para a vaga de *{$requestData['autocomplete_label_vaga_modal']}* através da plataforma MyBP.\nUma vez iniciado o teste não existe a possibilidade de pausar, portanto se prepare e reserve um tempo para preenchê-los.\n\n";
        } else {
            $message = "Parabéns, *{$curriculumData['nome']}*. Você foi *selecionado(a)*!\nVocê está recebendo um convite para realizar a avaliação abaixo relacionada ao seu processo seletivo para a vaga de *{$requestData['autocomplete_label_vaga_modal']}* através da plataforma MyBP.\nUma vez iniciado o teste não existe a possibilidade de pausar, portanto se prepare e reserve um tempo para preenchê-los.\n\n";
        }
        
        foreach ($provas as $prova) {
            $message .= route('provas.prova.simulado', [$prova->vaga_id, $prova->simulado_id, $prova->Simulado->slug]) . "\n";
        }
        
        $message .= "\n\nCuidado para não perder o prazo! Esperamos te ver em breve!\n\n*Equipe RH BPSE*";
        
        return $message;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Curriculo $recrutamento)
    {
        $this->authorize('curriculos_delete');
        $recrutamento->delete();
    }

    /**
     * Marca currículo como lido
     */
    public function marcaLido(Curriculo $curriculo)
    {
        if (!$curriculo->lido) {
            $curriculo->lido = !$curriculo->lido;
            $curriculo->usuario_lido = auth()->id();
            $curriculo->datalido = (new DataHora())->dataHoraInsert();
            $curriculo->save();
            $curriculo->refresh();
            
            // Registrar histórico da marcação como lido
            RecrutamentoHistorico::registrar(
                $curriculo->id,
                RecrutamentoHistorico::ACAO_MARCADO_LIDO,
                RecrutamentoHistorico::MODULO_CURRICULO,
                null,
                "Currículo foi marcado como lido"
            );
            
            return response()->json(['lido' => $curriculo->lido], 201);
        }
    }

    /**
     * Atualiza a listagem de currículos com filtros
     */
    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->pages?:300);

        $whatsappConfig = ClienteConfig::whereClienteId(auth()->user()->empresa_id)->first();
        $whatsappEnabled = !empty($whatsappConfig) ? $whatsappConfig->envia_whatsapp : false;

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'items' => collect($resultado->items())->transform(function ($item) {
                    $item->ctoken = \Crypt::encrypt($item->id);
                    return $item;
                }),
                'permite_envio_whatsapp' => $whatsappEnabled,
                'lista_sexos' => Curriculo::TIPOS_SEXOS,
                'lista_estados_civis' => Curriculo::ESTADOS_CIVIS,
            ]
        ]);
    }

    /**
     * Aplica filtros na consulta de currículos
     */
    public function filtro(Request $request)
    {
        $query = Curriculo::select([
            'id', 'cpf', 'rg', 'orgao_expeditor', 'nome', 'nascimento',
            'logradouro', 'complemento', 'bairro', 'municipio', 'email',
            'vaga_pretendida', 'pcd', 'uf_vaga', 'municipio_id', 'sexo',
            'estado_civil', 'lido', 'created_at'
        ])
        ->with('VagaAberta.VagaSelecionada',
        'FeedBack:id,curriculo_id,interesse,selecionado,contato_realizado')
        ->doesntHave('FeedBack.parecerRh');

        // Filtro por período
        if ($request->filtroPeriodo == 'true') {
            $this->applyDateFilter($query, $request->periodo);
        }

        // Filtros de busca
        $this->applySearchFilters($query, $request);

        return $query->orderByDesc('created_at');
    }

    /**
     * Aplica filtro de data
     */
    private function applyDateFilter($query, string $periodo): void
    {
        $periodoParts = explode(' até ', $periodo);
        $dataInicio = new DataHora($periodoParts[0] . ' 00:00:00');
        $dataFim = new DataHora($periodoParts[1] . ' 23:59:59');

        $query->where('updated_at', '>=', $dataInicio->dataHoraInsert())
              ->where('updated_at', '<=', $dataFim->dataHoraInsert());
    }

    /**
     * Aplica filtros de busca
     */
    private function applySearchFilters($query, Request $request): void
    {
        if ($request->filled('campoBusca')) {
            $query->where('nome', 'like', '%' . $request->campoBusca . '%');
        }

        if ($request->filled('campoCPF')) {
            $query->where('cpf', 'like', '%' . $request->campoCPF . '%');
        }

        if ($request->filled('campoVaga')) {
            $query->whereHas('VagaAberta', function ($subQuery) use ($request) {
                $subQuery->whereId($request->campoVaga);
            });
        }

        if ($request->filled('campoLido')) {
            $query->whereLido($request->campoLido == 'true');
        }

        if ($request->filled('campoUf')) {
            $query->whereUfVaga($request->campoUf);
        }

        if ($request->filled('campoPcd')) {
            $query->wherePcd($request->campoPcd == 'true');
        }
    }

    /**
     * Exporta dados para Excel
     */
    public function export(Request $request)
    {
        $resultado = $this->filtro($request)->select([
            'id', 'cpf', 'rg', 'orgao_expeditor', 'nome', 'nascimento',
            'logradouro', 'complemento', 'bairro', 'municipio', 'email',
            'vaga_pretendida', 'pcd', 'uf_vaga', 'municipio_id', 'lido', 'created_at'
        ])->get();

        $headers = [
            "Nome", "CPF", "Nascimento", "PCD", "CNH", "E-mail",
            "Endereço", "Vaga", "Telefones", "Data Cadastro"
        ];

        $rows = $this->prepareExportRows($resultado);

        $filename = "recrutamento" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        
        // Registrar histórico da exportação
        RecrutamentoHistorico::registrar(
            0, // ID genérico para exportação
            RecrutamentoHistorico::ACAO_EXPORTADO,
            RecrutamentoHistorico::MODULO_EXPORT,
            null,
            "Exportação de dados de recrutamento realizada - {$resultado->count()} registros",
            null,
            null,
            $request->all()
        );
        
        JobExportaExcel::dispatch(auth()->id(), "Recrutamento", $headers, $rows, $filename);
        
        return response()->json([
            'msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.'
        ]);
    }

    /**
     * Prepara as linhas para exportação
     */
    private function prepareExportRows($curriculos): array
    {
        $rows = [];

        foreach ($curriculos as $curriculo) {
            $telefones = "";
            foreach ($curriculo->Telefones as $telefone) {
                $telefones .= $telefone->numero . " ($telefone->tipoText) | ";
            }

            $rows[] = [
                $curriculo->nome,
                $curriculo->cpf,
                $curriculo->nascimento,
                !$curriculo->pcd ? "Não" : "Sim - {$curriculo->cid}",
                $curriculo->cnh ?: "",
                mb_strtolower($curriculo->email),
                $curriculo->endereco_completo,
                $curriculo->VagaAberta->VagaSelecionada->nome . " - " . $curriculo->VagaAberta->Municipio->nome . " - " . $curriculo->VagaAberta->Municipio->uf,
                substr($telefones, 0, -3),
                (new DataHora($curriculo->created_at))->dataCompleta() . ' ' . substr((new DataHora($curriculo->created_at))->horaCompleta(), 0, 5),
            ];
        }

        return $rows;
    }

    /**
     * Busca o histórico de mudanças de um currículo
     */
    public function historico(Curriculo $curriculo)
    {
        $historico = RecrutamentoHistorico::porCurriculo($curriculo->id);
        
        return response()->json([
            'curriculo' => $curriculo->load('Usuario'),
            'historico' => $historico->map(function ($item) {
                return [
                    'id' => $item->id,
                    'acao' => $item->acao,
                    'modulo' => $item->modulo,
                    'descricao' => $item->descricao ?? $item->gerarDescricao(),
                    'usuario' => $item->usuario->nome ?? 'Usuário desconhecido',
                    'data_acao' => $item->data_acao,
                    'dados_anteriores' => $item->dados_anteriores,
                    'dados_novos' => $item->dados_novos,
                    'ip_address' => $item->ip_address,
                ];
            })
        ]);
    }

    /**
     * Busca o histórico geral da empresa
     */
    public function historicoGeral(Request $request)
    {
        $query = RecrutamentoHistorico::where('empresa_id', auth()->user()->empresa_id)
            ->with(['curriculo', 'usuario', 'feedback']);

        // Filtros
        if ($request->filled('acao')) {
            $query->where('acao', $request->acao);
        }

        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->filled('data_inicio')) {
            $query->where('data_acao', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->where('data_acao', '<=', $request->data_fim);
        }

        if ($request->filled('usuario_id')) {
            $query->where('user_id', $request->usuario_id);
        }

        $historico = $query->orderBy('data_acao', 'desc')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'historico' => $historico->map(function ($item) {
                return [
                    'id' => $item->id,
                    'acao' => $item->acao,
                    'modulo' => $item->modulo,
                    'descricao' => $item->descricao ?? $item->gerarDescricao(),
                    'curriculo' => $item->curriculo ? [
                        'id' => $item->curriculo->id,
                        'nome' => $item->curriculo->nome,
                        'cpf' => $item->curriculo->cpf,
                    ] : null,
                    'usuario' => $item->usuario->nome ?? 'Usuário desconhecido',
                    'data_acao' => $item->data_acao,
                    'ip_address' => $item->ip_address,
                ];
            }),
            'pagination' => [
                'current_page' => $historico->currentPage(),
                'last_page' => $historico->lastPage(),
                'total' => $historico->total(),
                'per_page' => $historico->perPage(),
            ]
        ]);
    }
}