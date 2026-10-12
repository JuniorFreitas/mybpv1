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
                <template v-else-if="dados.status === 'Pendente Anexo'">
                    <div class="assin-origin mb-3">
                        Anexe a carta oferta assinada em PDF para continuar.
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

                <div v-if="erro" class="alert alert-danger mt-3 mb-0" role="alert">@{{ erro }}</div>
            </div>
        </div>
    </div>
    @include('layouts.publico.carregando')
@endsection

@push('scripts')
    <script src="{{ mix('js/cartaoferta/app.js') }}"></script>
@endpush
