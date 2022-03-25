@extends('layouts.sistema')
@section('title', 'Alterar senha de acesso')
@section('content_header', 'Alterar minha senha de acesso')
@section('breadcrumb')
    <li class="breadcrumb-item active">Alterar minha senha de acesso</li>
@endsection
@section('content')

    {{--    <kanban></kanban>--}}

    <form>
        <div class="row ">
            <div class="col-sm-6">

                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" class="form-control form-control-sm" v-model="form.password" placeholder="Senha" autocomplete="off"
                           :disabled="preloadAjax" onblur="valida_campo_vazio(this,3)">
                </div>

                <div class="form-group">
                    <label>Redigitar senha</label>
                    <input type="password" class="form-control form-control-sm" v-model="form.password_confirmation" placeholder="Redigitar senha"
                           autocomplete="off" :disabled="preloadAjax" onblur="valida_campo_vazio(this,3)">
                </div>

                <div class="form-group">
                    <button type="button" class="btn btn-sm btn-primary" :disabled="preloadAjax" @click="alterar()">
                        Alterar senha
                    </button>
                </div>

            </div>


        </div>

    </form>
@stop

@push('js')
    <script src="{{mix('js/g/alterar-senha/app.js')}}"></script>
@endpush
