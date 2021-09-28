<table>
    <thead>
    <tr>
        <td>COLABORADOR</td>
        @if(auth()->user()->cliente_id == 1)
            <td>CLIENTE</td>
        @endif
        <td>ÁREA</td>
        <td>DATA OCORRÊNCIA</td>
        <td>OCORRÊNCIA</td>
        <td>RESPONSÁVEL LANÇAMENTO</td>
        <td>AÇÃO</td>
        <td>OBS LANÇAMENTO</td>
        <td>STATUS</td>
        <td>DATA STATUS</td>
        <td>RESPONSÁVEL STATUS</td>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $cih)
        <tr>
            <td>{{$cih->Colaborador->Curriculo->nome}}</td>
            @if(auth()->user()->cliente_id == 1)
                <td>{{$cih->Cliente->razao_social}}</td>
            @endif
            <td>{{$cih->Area ? $cih->Area->label : $cih->outra_area}}</td>
            <td>{{$cih->data_aprovacao}}</td>
            <td>{{$cih->Tag ? $cih->Tag->label : $cih->outra_tag}}</td>
            <td>{{$cih->ResponsavelLancamento->nome}}</td>
            <td>{{$cih->acao}}</td>
            <td>{{$cih->obs_lancamento}}</td>
            <td>{{$cih->status}}</td>
            <td>{{$cih->data_aprovacao}}</td>
            <td>{{$cih->ResponsavelAprovacao->nome}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
