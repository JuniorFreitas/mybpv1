@extends('layouts.sistema')
@section('title', 'Planejamento - Movimentação')
@section('content_header','Planejamento - Movimentação')
@section('content')

    <nav class="mt-3 tabbable">
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <a class="nav-item nav-link" :class="aba_ativa === 'demissao' ? 'active' : ''" id="nav-solicitacao-demissao-tab" data-toggle="tab"
               v-if="permissoes_abas.demissao"
               @click="trocaAba('demissao')"
               href="#nav-solicitacao-demissao" role="tab"
               aria-controls="nav-solicitacao-demissao" aria-selected="true">Demissão</a>
            <a class="nav-item nav-link" :class="aba_ativa === 'ferias' ? 'active' : ''" id="nav-ferias-tab" data-toggle="tab" href="#nav-ferias" role="tab"
               v-if="permissoes_abas.ferias"
               @click="trocaAba('ferias')"
               aria-controls="nav-ferias" aria-selected="false">Férias</a>
            <a class="nav-item nav-link" :class="aba_ativa === 'admissao' ? 'active' : ''" id="nav-admissao-tab" data-toggle="tab" href="#nav-admissao" role="tab"
               @click="trocaAba('admissao')"
               v-if="permissoes_abas.admissao"
               aria-controls="nav-admissao" aria-selected="false">Admissão</a>
            <a class="nav-item nav-link" :class="aba_ativa === 'valorextra' ? 'active' : ''" id="nav-valorextra-tab" data-toggle="tab" href="#nav-valorextra" role="tab"
               v-if="permissoes_abas.valorextra"
               @click="trocaAba('valorextra')"
               aria-controls="nav-valorextra" aria-selected="false">Liderança de Pessoal e Valor Extra</a>
            <a class="nav-item nav-link" :class="aba_ativa === 'mudacargo' ? 'active' : ''" id="nav-mudacargo-tab" data-toggle="tab" href="#nav-mudacargo" role="tab"
               v-if="permissoes_abas.mudacargo"
               @click="trocaAba('mudacargo')"
               aria-controls="nav-mudacargo" aria-selected="false">Mudança de Cargo</a>
            <a class="nav-item nav-link" :class="aba_ativa === 'intermitente' ? 'active' : ''" id="nav-intermitente-tab" data-toggle="tab" href="#nav-intermitente" role="tab"
               v-if="permissoes_abas.intermitente"
               @click="trocaAba('intermitente')"
               aria-controls="nav-intermitente" aria-selected="false">Mudança de Interminte p/ Fixo</a>
            <a class="nav-item nav-link" :class="aba_ativa === 'transferencia' ? 'active' : ''" id="nav-transferencia-tab" data-toggle="tab" href="#nav-transferencia" role="tab"
               v-if="permissoes_abas.transferencia"
               @click="trocaAba('transferencia')"
               aria-controls="nav-transferencia" aria-selected="false">Transferência</a>
        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade p-3" :class="aba_ativa === 'demissao' ? 'show active' : ''" id="nav-solicitacao-demissao" role="tabpanel"
             v-if="abas.demissao"
             aria-labelledby="nav-solicitacao-demissao-tab">
            <solicitacao-demissao :cliente_id="cliente_id"></solicitacao-demissao>
        </div>
        <div class="tab-pane fade p-3" :class="aba_ativa === 'ferias' ? 'show active' : ''" id="nav-ferias" role="tabpanel" aria-labelledby="nav-ferias-tab"
             v-if="abas.ferias">
            <solicitacao-ferias :cliente_id="cliente_id"></solicitacao-ferias>
        </div>
        <div class="tab-pane fade p-3" :class="aba_ativa === 'admissao' ? 'show active' : ''" id="nav-admissao" role="tabpanel" aria-labelledby="nav-admissao-tab"
             v-if="abas.admissao">
            <solicitacao-admissao :cliente_id="cliente_id"></solicitacao-admissao>
        </div>
        <div class="tab-pane fade p-3" :class="aba_ativa === 'valorextra' ? 'show active' : ''" id="nav-valorextra" role="tabpanel" aria-labelledby="nav-valorextra-tab"
             v-if="abas.valorextra">
            <solicitacao-valor-extra :cliente_id="cliente_id"></solicitacao-valor-extra>
        </div>
        <div class="tab-pane fade p-3" :class="aba_ativa === 'mudacargo' ? 'show active' : ''" id="nav-mudacargo" role="tabpanel" aria-labelledby="nav-mudacargo-tab"
             v-if="abas.mudacargo">
            <solicitacao-muda-cargo :cliente_id="cliente_id"></solicitacao-muda-cargo>
        </div>
        <div class="tab-pane fade p-3" :class="aba_ativa === 'intermitente' ? 'show active' : ''" id="nav-intermitente" role="tabpanel" aria-labelledby="nav-intermitente-tab"
             v-if="abas.intermitente">
            <solicitacao-intermitente-fixo :cliente_id="cliente_id"></solicitacao-intermitente-fixo>
        </div>
        <div class="tab-pane fade p-3" :class="aba_ativa === 'transferencia' ? 'show active' : ''" id="nav-transferencia" role="tabpanel" aria-labelledby="nav-transferencia-tab"
             v-if="abas.transferencia">
            <solicitacao-transferencia :cliente_id="cliente_id"></solicitacao-transferencia>
        </div>
    </div>

@stop
@push('js')
    <script src="{{mix('js/g/planejamento/movimentacao/app.js')}}"></script>
@endpush
@push('css')
    <style>
        .tabbable .nav-tabs {
            overflow-x: auto;
            overflow-y: hidden;
            flex-wrap: nowrap;
        }

        .tabbable .nav-tabs .nav-link {
            white-space: nowrap;
        }

    </style>
@endpush
