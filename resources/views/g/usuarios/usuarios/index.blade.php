@extends('layouts.sistema')
@section('content_header', 'Usuários do sistema')
@section('breadcrumb')
    <li class="breadcrumb-item active">Usuários - Usuários do sistema</li>
@endsection
@section('content')
    <div id="app">
        <usuarios
            :url-atualizar="'{{ $urlAtualizar }}'"
            :lista-empresas='@json($listaEmpresas)'
            :empresa-id="{{ json_encode($empresaId) }}"
            :is-mybp-empresa="{{ $isMybpEmpresa ? 'true' : 'false' }}"
            :can-insert="{{ $canInsert ? 'true' : 'false' }}"
            :can-update="{{ $canUpdate ? 'true' : 'false' }}"
            :can-delete="{{ $canDelete ? 'true' : 'false' }}"
            :pode-simular="{{ $podeSimular ? 'true' : 'false' }}"
        />
    </div>
@stop

@push('js')
    <script src="{{ mix('js/g/usuarios/app.js') }}"></script>
@endpush
