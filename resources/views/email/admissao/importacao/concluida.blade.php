@extends('layouts.mail.layout')
@section('titulo', $comErro ? 'Importação de Admissões – Concluída com erros' : 'Importação de Admissões – Concluída')
@section('conteudo')
    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">
                Olá, <strong>{{ $nomeUsuario }}</strong>,<br><br>
                A importação de admissões foi finalizada. Resumo do processamento:<br><br>

                <table border="1" cellpadding="8" cellspacing="0" width="100%" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="background: #f3f3f3; font-weight: bold; color: #555;">Total processadas</td>
                        <td>{{ $totalProcessadas }}</td>
                    </tr>
                    <tr>
                        <td style="background: #f3f3f3; font-weight: bold; color: #555;">Com sucesso</td>
                        <td>{{ $totalSucesso }}</td>
                    </tr>
                    <tr>
                        <td style="background: #f3f3f3; font-weight: bold; color: #555;">Com erro</td>
                        <td>{{ $totalErros }}</td>
                    </tr>
                    <tr>
                        <td style="background: #f3f3f3; font-weight: bold; color: #555;">Data do processamento</td>
                        <td>{{ $dataProcessamento }}</td>
                    </tr>
                </table>

                <br>

                @if($comErro)
                    <p style="margin: 15px 0;">Verifique o relatório em anexo (arquivo CSV) para ver os detalhes dos erros por linha e como corrigir.</p>
                    <p style="margin: 15px 0;">O arquivo <strong>relatorio_importacao_admissoes.csv</strong> está anexado a este e-mail com o detalhamento (linha, campo, mensagem, como corrigir).</p>
                @else
                    <p style="margin: 15px 0;">Todas as <strong>{{ $totalSucesso }}</strong> linha(s) foram importadas com sucesso.</p>
                @endif

                <br><br>
            </td>
        </tr>
    </table>
@endsection
