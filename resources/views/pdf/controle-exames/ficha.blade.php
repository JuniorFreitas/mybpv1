@extends('layouts.pdf')
@section('title','Controle de Exames')
@section('empresa')
    @include('layouts.cabecalioPDFEmpresaFilial', ['dados' => $ExameFuncionario])
@endsection
@section('conteudo')
    <br><br>
    <h5>
        A <br>
        {{ $ExameFuncionario->EmpresaExame->nome }} <br>
        <span>
            {{ $ExameFuncionario->EmpresaExame['dados']['endereco']['endereco_completo'] }} <br> FONE: {{ $ExameFuncionario->EmpresaExame['dados']['telefone'] }}<br/>
            Estamos encaminhando   </span>{{ $ExameFuncionario->Feedback->Curriculo->nome }}
        , {{ $ExameFuncionario->Feedback->Curriculo->idade }} ANOS.<br/><br/>
        <span>
            @if($ExameFuncionario->pcmso)
                Para fazer exame de ordem {{ $ExameFuncionario->ExameTipo->label ?? '' }}, conforme
                <strong>PCMSO: {{ $ExameFuncionario->PcmsoDados->label }}</strong>
            @else
                Para fazer os seguintes exames:
            @endif
        </span>
    </h5>

    @if(!$ExameFuncionario->pcmso)
        <div class="h5">
            @foreach($ExameFuncionario->Formulario->Setores as $setor)
                <br>
                {{ $setor->nome == "EXAME DE ORDEM" ? "EXAMES" : $setor->nome}}
                <br>
                <div style='width: 100%; padding: 0.3cm 0 0 0.3cm; border: 1px solid #555555; line-height: 0.3cm'>
                    @foreach($setor->Alternativas as $alternativa)
                        @if($ExameFuncionario->respostas['alternativa_id_' . $alternativa->id]['valor'])
                            @if($alternativa->tipo == 'select')
                                {{ $alternativa->nome }}
                                :  {{\App\Models\RespostaAlternativas::find($ExameFuncionario->respostas['alternativa_id_' . $alternativa->id]['valor'])->label}}
                                <br>
                            @endif
                            @if($alternativa->tipo == 'checkbox')
                                {{$ExameFuncionario->respostas['alternativa_id_' . $alternativa->id]['valor'] ? "(X)" : "( )"}} {{ $alternativa->nome }}
                                <br>
                            @endif
                            @if($alternativa->tipo == 'text')
                                {{ $alternativa->nome }}
                                : {{$ExameFuncionario->respostas['alternativa_id_' . $alternativa->id]['valor']}}
                                <br>
                            @endif
                            <br>
                        @endif
                    @endforeach
                </div>
            @endforeach

        </div>
    @endif


    <h5 style='text-align: center; padding-top: 1.5cm'>
        <hr style='border: none; height: 1px; background:#747373; width: 9cm; margin: 0 auto'>
        Assinatura autorizada
    </h5>
    @if(auth()->guest())
        @include('layouts.rodapePdfSemSolicitante')
    @else
        @include('layouts.rodapePdf')
    @endif
@endsection
