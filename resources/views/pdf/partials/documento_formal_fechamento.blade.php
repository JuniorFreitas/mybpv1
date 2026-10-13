{{--
  Fechamento formal de contrato (local/data + assinantes + testemunhas).
  DomPDF: TABLE (não float). Use novaPagina=true para abrir em página própria
  (igual ao Termo de Prorrogação), com o bloco íntegro e distribuído.

  @param string $localData
  @param array<int, array{nome: string, papel?: string}> $assinantes
  @param int $testemunhas
  @param bool $novaPagina Se true, força página nova e distribuição vertical
--}}
@php
    $localData = trim((string) ($localData ?? ''));
    $assinantes = array_values(array_filter(
        is_array($assinantes ?? null) ? $assinantes : [],
        static fn ($item) => is_array($item) && trim((string) ($item['nome'] ?? '')) !== ''
    ));
    $testemunhas = max(0, (int) ($testemunhas ?? 2));
    $novaPagina = (bool) ($novaPagina ?? false);
    $qtdAssinantes = count($assinantes);
    $classeFechamento = 'doc-fechamento' . ($novaPagina ? ' doc-fechamento--nova-pagina' : '');
@endphp

@if($localData !== '' || $qtdAssinantes > 0 || $testemunhas > 0)
<div class="{{ $classeFechamento }}">
    @if($localData !== '')
        <p class="doc-fechamento__local">{{ $localData }}</p>
    @endif

    @if($qtdAssinantes > 0)
        <table class="doc-fechamento__assinantes {{ $qtdAssinantes === 1 ? 'doc-fechamento__assinantes--unico' : '' }}" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                @foreach($assinantes as $i => $assinante)
                    @php
                        $papel = trim((string) ($assinante['papel'] ?? ''));
                        $nome = trim((string) ($assinante['nome'] ?? ''));
                        $ehUltimo = $i === $qtdAssinantes - 1;
                        $largura = $qtdAssinantes === 1
                            ? '100%'
                            : ($qtdAssinantes === 2 ? '48%' : (floor(100 / $qtdAssinantes) . '%'));
                    @endphp
                    @php
                        $subtitulo = trim((string) ($assinante['subtitulo'] ?? ''));
                    @endphp
                    <td class="doc-fechamento__assinante" width="{{ $largura }}" valign="top" align="center">
                        <div class="doc-fechamento__espaco"></div>
                        <div class="doc-fechamento__linha"></div>
                        @if($papel !== '')
                            <div class="doc-fechamento__papel">{{ $papel }}</div>
                        @endif
                        <div class="doc-fechamento__nome">{{ $nome }}</div>
                        @if($subtitulo !== '')
                            <div class="doc-fechamento__subtitulo">{{ $subtitulo }}</div>
                        @endif
                    </td>
                    @if(!$ehUltimo && $qtdAssinantes === 2)
                        <td width="4%">&nbsp;</td>
                    @endif
                @endforeach
            </tr>
        </table>
    @endif

    @if($testemunhas > 0)
        <div class="doc-fechamento__testemunhas-titulo">TESTEMUNHAS:</div>
        <table class="doc-fechamento__testemunhas" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                @for($t = 1; $t <= $testemunhas; $t++)
                    @php
                        $ehUltima = $t === $testemunhas;
                        $larguraT = $testemunhas === 1
                            ? '100%'
                            : ($testemunhas === 2 ? '48%' : (floor(100 / $testemunhas) . '%'));
                    @endphp
                    <td class="doc-fechamento__testemunha" width="{{ $larguraT }}" valign="top">
                        <div class="doc-fechamento__espaco doc-fechamento__espaco--testemunha"></div>
                        <div class="doc-fechamento__linha"></div>
                        <div class="doc-fechamento__testemunha-meta">
                            {{ $t }}ª Testemunha<br>
                            Nome: ________________________________<br>
                            CPF: _________________________________
                        </div>
                    </td>
                    @if(!$ehUltima && $testemunhas === 2)
                        <td width="4%">&nbsp;</td>
                    @endif
                @endfor
            </tr>
        </table>
    @endif
</div>
@endif
