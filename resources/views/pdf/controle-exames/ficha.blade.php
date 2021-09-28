@extends('layouts.pdf')
@section('title','Controle de Exames')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <br><br>
    <h5>
        A <br>
        {{ $exame->EmpresaExame->nome }} <br>
        <span>
            {{ $exame->EmpresaExame['dados']['endereco']['endereco_completo'] }} <br> FONE: {{ $exame->EmpresaExame['dados']['telefone'] }}<br />
            Estamos encaminhando   </span>{{ $exame->Feedback->Curriculo->nome }}
        , {{ $exame->Feedback->Curriculo->idade }} ANOS.<br /><br />
        <span>
            Para fazer os seguintes exames</span>:
    </h5>


    <div class="h5">
        @foreach($exame->Formulario->Setores as $setor)
            <br>
            {{ $setor->nome == "EXAME DE ORDEM" ? "EXAMES" : $setor->nome}}
            <br>
            <div style='width: 100%; padding: 0.3cm 0 0 0.3cm; border: 1px solid #555555; line-height: 0.3cm'>
                @foreach($setor->Alternativas as $alternativa)
                    @if($exame->respostas['alternativa_id_' . $alternativa->id]['valor'])
                        @if($alternativa->tipo == 'select')
                            {{ $alternativa->nome }}
                            :  {{\App\Models\RespostaAlternativas::find($exame->respostas['alternativa_id_' . $alternativa->id]['valor'])->label}}
                            <br>
                        @endif
                        @if($alternativa->tipo == 'checkbox')
                            {{$exame->respostas['alternativa_id_' . $alternativa->id]['valor'] ? "(X)" : "( )"}} {{ $alternativa->nome }}
                            <br>
                        @endif
                        @if($alternativa->tipo == 'text')
                                {{ $alternativa->nome }}: {{$exame->respostas['alternativa_id_' . $alternativa->id]['valor']}}
                            <br>
                        @endif
                        <br>
                    @endif
                @endforeach
            </div>
        @endforeach

    </div>


    <h5 style='text-align: center; padding-top: 1.5cm'>
        <hr style='border: none; height: 1px; background:#747373; width: 9cm; margin: 0 auto'>
        Assinatura autorizada
    </h5>
@endsection
