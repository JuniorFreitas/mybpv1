<table>
    <thead>
    <tr>
        <td>Número do crachá</td>
        <td>NOME</td>
        <td>CPF</td>
        <td>RG/EMITENTE</td>
        <td>Pai</td>
        <td>Mãe</td>
        <td>PCD</td>
        <td>Nascimento</td>
        <td>Idade</td>
        <td>Contato</td>
        <td>E-mail</td>
        <td>Empresa</td>
        <td>Vaga</td>
        <td>Cargo</td>
        <td>Função</td>
        <td>Data Admissão</td>
        @foreach(\App\Models\Vencimento::whereAtivo(true)->orderBy('ordem')->get() as $vencimentos)
            <td>{{ $vencimentos->label }}</td>
            <td>Data Treinamento</td>
            <td>Data Vencimento</td>
            <td>FAT</td>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($data as $row)
        <tr>
            <td>{{$row->Admissao ? $row->Admissao->numero_cracha : null}}</td>
            <td>{{$row->Curriculo->nome}}</td>
            <td>{{$row->Curriculo->cpf}}</td>
            <td>{{$row->Curriculo->rg != '' ? $row->Curriculo->rg.' | '.$row->Curriculo->orgao_expeditor : ''}}</td>
            <td>{{$row->Curriculo->filiacao_pai}}</td>
            <td>{{$row->Curriculo->filiacao_mae}}</td>
            <td>{{$row->Curriculo->pcd = $row->Curriculo->pcd ? 'Sim' : 'Não'}}</td>
            <td>{{$row->Curriculo->nascimento}}</td>
            <td>{{$row->Curriculo->idade}}</td>
            <td>{{$row->FeedbackCurriculo->TelPrincipal ? $row->FeedbackCurriculo->TelPrincipal->numero : 'não informado'}}</td>
            <td>{{$row->Curriculo->email}}</td>
            <td>{{$row->FeedbackCurriculo->Cliente->cnpj ? $row->FeedbackCurriculo->Cliente->nome_fantasia : $row->FeedbackCurriculo->Cliente->nome}}</td>
            <td>{{$row->FeedbackCurriculo->VagaSelecionada->nome}}</td>
            <td>{{$row->Admissao ? $row->Admissao->cargo : null}}</td>
            <td>{{$row->Admissao ? $row->Admissao->funcao : null}}</td>
            <td>{{$row->Admissao ? $row->Admissao->data_admissao : null}}</td>
            @if ($row->Admissao)
                @if ($row->Admissao->nr_trinta_tres == 'NÃO SE APLICA')
                    <td>NÃO SE APLICA</td>
                    <td>NÃO SE APLICA</td>
                    <td>NÃO SE APLICA</td>
                    <td>NÃO SE APLICA</td>
                @endif
                @if ($row->Admissao->nr_trinta_tres != 'NÃO SE APLICA')
                    <td>{{$row->Treinamento ? $row->Treinamento->Vencimentos()->whereId(7)->count() > 0 ? 'REALIZADO' : 'AGUARDANDO' : 'AGUARDANDO'}}</td>
                    <td>{{$row->Treinamento ? $row->Treinamento->Vencimentos()->whereId(7)->count() > 0 ? $row->Treinamento->Vencimentos()->whereId(7)->first()->pivot->data_treinamento : 'AGUARDANDO' : 'AGUARDANDO'}}</td>
                    <td>{{$row->Treinamento ? $row->Treinamento->Vencimentos()->whereId(7)->count() > 0 ? $row->Treinamento->Vencimentos()->whereId(7)->first()->pivot->data_vencimento : 'AGUARDANDO' : 'AGUARDANDO'}}</td>
                    <td>{{$row->Treinamento ? $row->Treinamento->Vencimentos()->whereId(7)->count() > 0 ? $row->Treinamento->Vencimentos()->whereId(7)->first()->pivot->numero_fat : 'AGUARDANDO' : 'AGUARDANDO'}}</td>
                @endif

                @if ($row->Admissao->nr_trinta_cinco == 'NÃO SE APLICA')
                    <td>NÃO SE APLICA</td>
                    <td>NÃO SE APLICA</td>
                    <td>NÃO SE APLICA</td>
                    <td>NÃO SE APLICA</td>
                @endif
                @if ($row->Admissao->nr_trinta_cinco != 'NÃO SE APLICA')
                    <td>{{$row->Treinamento ? $row->Treinamento->Vencimentos()->whereId(6)->count() > 0 ? 'REALIZADO' : 'AGUARDANDO': 'Aguardando'}}</td>
                    <td>{{$row->Treinamento ? $row->Treinamento->Vencimentos()->whereId(6)->count() > 0 ? $row->Treinamento->Vencimentos()->whereId(6)->first()->pivot->data_treinamento : 'AGUARDANDO': 'Aguardando'}}</td>
                    <td>{{$row->Treinamento ? $row->Treinamento->Vencimentos()->whereId(6)->count() > 0 ? $row->Treinamento->Vencimentos()->whereId(6)->first()->pivot->data_vencimento : 'AGUARDANDO': 'Aguardando'}}</td>
                    <td>{{$row->Treinamento ? $row->Treinamento->Vencimentos()->whereId(6)->count() > 0 ? $row->Treinamento->Vencimentos()->whereId(6)->first()->pivot->numero_fat : 'AGUARDANDO': 'Aguardando'}}</td>
                @endif
            @else
                <td>{{$row->Treinamento ? $row->Treinamento->Vencimentos()->whereId(7)->count() > 0 ? 'REALIZADO' : 'AGUARDANDO': 'AGUARDANDO'}}</td>
                <td>{{$row->Treinamento ? $row->Treinamento->Vencimentos()->whereId(7)->count() > 0 ? $row->Treinamento->Vencimentos()->whereId(7)->first()->pivot->data_treinamento : 'AGUARDANDO': 'AGUARDANDO'}}</td>
                <td>{{$row->Treinamento ? $row->Treinamento->Vencimentos()->whereId(7)->count() > 0 ? $row->Treinamento->Vencimentos()->whereId(7)->first()->pivot->data_vencimento : 'AGUARDANDO': 'AGUARDANDO'}}</td>
                <td>{{$row->Treinamento ? $row->Treinamento->Vencimentos()->whereId(7)->count() > 0 ? $row->Treinamento->Vencimentos()->whereId(7)->first()->pivot->numero_fat : 'AGUARDANDO': 'AGUARDANDO'}}</td>

                <td>{{$row->Treinamento ? $row->Treinamento->Vencimentos()->whereId(6)->count() > 0 ? 'REALIZADO' : 'AGUARDANDO': 'AGUARDANDO'}}</td>
                <td>{{$row->Treinamento ? $row->Treinamento->Vencimentos()->whereId(6)->count() > 0 ? $row->Treinamento->Vencimentos()->whereId(6)->first()->pivot->data_treinamento : 'AGUARDANDO': 'AGUARDANDO'}}</td>
                <td>{{$row->Treinamento ? $row->Treinamento->Vencimentos()->whereId(6)->count() > 0 ? $row->Treinamento->Vencimentos()->whereId(6)->first()->pivot->data_vencimento : 'AGUARDANDO': 'AGUARDANDO'}}</td>
                <td>{{$row->Treinamento ? $row->Treinamento->Vencimentos()->whereId(6)->count() > 0 ? $row->Treinamento->Vencimentos()->whereId(6)->first()->pivot->numero_fat : 'AGUARDANDO': 'AGUARDANDO'}}</td>
            @endif
            @foreach(\App\Models\Vencimento::whereAtivo(true)->whereNotIn('id',[7,6])->orderBy('ordem')->get() as $vencimentos)
                <td>{{ $row->Treinamento ? $row->Treinamento->Vencimentos()->whereId($vencimentos->id)->count() > 0 ? 'REALIZADO' : null: null}}</td>
                <td>{{ $row->Treinamento ? $row->Treinamento->Vencimentos()->whereId($vencimentos->id)->count() > 0 ? $row->Treinamento->Vencimentos()->whereId($vencimentos->id)->first()->pivot->data_treinamento : null : ''}}</td>
                <td>{{ $row->Treinamento ? $row->Treinamento->Vencimentos()->whereId($vencimentos->id)->count() > 0 ? $row->Treinamento->Vencimentos()->whereId($vencimentos->id)->first()->pivot->data_vencimento : null : ''}}</td>
                <td>{{ $row->Treinamento ? $row->Treinamento->Vencimentos()->whereId($vencimentos->id)->count() > 0 ? $row->Treinamento->Vencimentos()->whereId($vencimentos->id)->first()->pivot->numero_fat : null : ''}}</td>
            @endforeach

        </tr>
    @endforeach
    </tbody>
</table>
