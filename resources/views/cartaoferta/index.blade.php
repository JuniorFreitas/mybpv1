@extends('assinatura.layout')

@section('title', 'Carta Oferta')
@section('selo', 'Carta Oferta')

@push('css')
    @include('layouts.favicon')
    @include('layouts.publico.estilos')
    @include('layouts.publico.tema-assinatura')
@endpush

@section('content')
    <div id="app" v-cloak>
        <preload v-if="preload" :msg="msgPreload"></preload>
        <input type="hidden" id="dd" value="{{ json_encode($payload) }}">

        {{-- Gate CPF + código --}}
        <acesso-por-codigo
            v-if="!dados.autenticado"
            :url-base="urlBase"
            :inicial="dados"
            titulo="Validação de segurança"
            @autenticado="aoAutenticar"
        >
            <template #origem>
                <div class="assin-origin mb-3">
                    A empresa <strong>{{ $marca['nome'] ?: 'contratante' }}</strong> enviou uma
                    <strong>Carta Oferta</strong> para você.
                </div>
            </template>
        </acesso-por-codigo>

        {{-- Conteúdo autenticado (dados mínimos) --}}
        <div v-else class="card assin-card">
            <div class="card-header">
                <h5 class="mb-0">Carta Oferta</h5>
                <p class="assin-subtitle">@{{ dados.empresa?.nome }}</p>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="assin-chip">@{{ dados.candidato?.nome }}</span>
                    <span class="assin-chip">Cargo: @{{ dados.cargo }}</span>
                </div>

                <div v-if="dados.status === 'Aceito pelo RH'" class="alert alert-success mb-0">
                    <i class="fa fa-check-circle"></i> Carta Oferta aceita com sucesso!
                </div>
                <div v-else-if="dados.status === 'Aguardando RH'" class="alert assin-status-warn mb-0">
                    <i class="fa fa-hourglass-half"></i>
                    Agradecemos o envio! Carta Oferta aguardando verificação pelo RH.
                    Fique atento(a) ao seu e-mail.
                </div>
                <div v-else-if="dados.status === 'Recusado pelo RH'" class="alert alert-danger mb-0">
                    <i class="fa fa-times-circle"></i> Carta Oferta recusada!
                </div>
                <div v-else-if="dados.status === 'Expirado'" class="alert alert-danger mb-0">
                    <i class="fa fa-clock"></i> Prazo expirado!
                </div>
                <div v-else-if="dados.status === 'Recusado pelo candidato'" class="alert alert-danger mb-0">
                    <i class="fa fa-times-circle"></i>
                    Você recusou esta Carta Oferta em @{{ dados.aceite?.data }}.
                </div>

                {{-- Sem assinatura digital: ler a proposta e aceitar ou recusar eletronicamente --}}
                <template v-else-if="aguardandoDecisao">
                    <div class="assin-section-title">Leia a proposta</div>
                    <div class="assin-pdf-wrap mb-3">
                        <visualizador-pdf :url="urlProposta" :urldownload="urlProposta"></visualizador-pdf>
                    </div>

                    <div v-if="!recusando" class="assin-section">
                        <div class="assin-section-title">Sua resposta</div>
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="co-consentimento" v-model="consentimento" :disabled="respondendo">
                            <label class="form-check-label" for="co-consentimento">@{{ dados.textos_aceite?.aceitar }}</label>
                        </div>
                        <p class="text-muted small mb-3">
                            Ao responder, registramos data, hora, endereço IP, navegador e localização aproximada,
                            como evidência da sua decisão. Você receberá o comprovante por e-mail.
                        </p>
                        <button type="button" class="btn btn-success mr-2" :disabled="respondendo || !consentimento" @click="responder('aceitar')">
                            <i class="fa" :class="respondendo ? 'fa-spinner fa-spin' : 'fa-check'"></i> Aceitar proposta
                        </button>
                        <button type="button" class="btn btn-outline-secondary" :disabled="respondendo" @click="recusando = true">
                            Recusar
                        </button>
                    </div>

                    <div v-else class="assin-section">
                        <div class="assin-section-title">Recusar proposta</div>
                        <p class="mb-2">@{{ dados.textos_aceite?.recusar }}</p>
                        <label class="form-label" for="co-motivo">Motivo (opcional)</label>
                        <textarea id="co-motivo" class="form-control mb-3" rows="3" maxlength="1000" v-model="motivoRecusa" :disabled="respondendo"></textarea>
                        <button type="button" class="btn btn-danger mr-2" :disabled="respondendo" @click="responder('recusar')">
                            <i class="fa" :class="respondendo ? 'fa-spinner fa-spin' : 'fa-times'"></i> Confirmar recusa
                        </button>
                        <button type="button" class="btn btn-link" :disabled="respondendo" @click="recusando = false">Voltar</button>
                    </div>
                </template>

                <template v-else-if="podeAnexar">
                    <div v-if="aceitou" class="alert alert-success">
                        <i class="fa fa-check-circle"></i>
                        Aceite registrado em @{{ dados.aceite.data }}. O comprovante foi enviado para o seu e-mail.
                    </div>
                    <div class="assin-origin mb-3">
                        <template v-if="aceitou">Para concluir, anexe a carta oferta assinada em PDF.</template>
                        <template v-else>Anexe a carta oferta assinada em PDF para continuar.</template>
                    </div>
                    <div class="assin-section">
                        <div class="assin-section-title">Anexar Carta Oferta</div>
                        <upload
                            label="Anexar"
                            :dados-ajax="{ tipo: 'carta_oferta', curriculo_id: dados.curriculo_id }"
                            :model="anexos"
                            :model-delete="anexosDel"
                            :url="urlAnexoUpload"
                            :apenas-pdf="true"
                            @onprogresso="anexoUploadAndamento = true"
                            @onfinalizado="anexoUploadAndamento = false"
                            :quantidade="1"
                            :multi="false"
                        ></upload>
                        <button
                            type="button"
                            class="btn btn-primary mt-3"
                            v-if="anexos.length && !anexos[0].falhou && !anexoUploadAndamento"
                            @click.prevent="salvar"
                        >Salvar e enviar</button>
                    </div>
                </template>

                <div v-if="dados.aceite" class="assin-section mt-3">
                    <div class="assin-section-title">Comprovante da sua resposta</div>
                    <p class="mb-2 small" style="word-break: break-all;">
                        Código de verificação: <strong>@{{ dados.aceite.codigo_verificacao }}</strong>
                    </p>
                    <a class="btn btn-sm btn-outline-secondary mr-2" :href="urlProposta" target="_blank" rel="noopener">
                        <i class="fa fa-file-pdf"></i> Ver carta oferta
                    </a>
                    <a class="btn btn-sm btn-outline-secondary mr-2" :href="urlComprovante" target="_blank" rel="noopener">
                        <i class="fa fa-certificate"></i> Baixar comprovante
                    </a>
                    <a class="btn btn-sm btn-link" :href="dados.aceite.url_verificacao" target="_blank" rel="noopener">Verificar autenticidade</a>
                </div>

                <div v-if="erro" class="alert alert-danger mt-3 mb-0" role="alert">@{{ erro }}</div>
            </div>
        </div>
    </div>
    @include('layouts.publico.carregando')
@endsection

@push('scripts')
    <script src="{{ mix('js/cartaoferta/app.js') }}"></script>
@endpush
