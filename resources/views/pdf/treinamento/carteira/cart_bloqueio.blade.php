@php
    $bloqueio_altura_linha_par_mm = $bloqueio_altura_linha_par_mm ?? \App\Services\Treinamento\CarteiraEtiquetaBloqueioLayout::alturaLinhaParMm();
@endphp
<div
    class="a4 carteira-a4-wrap carteira-a4-wrap--bloqueio @if(!empty($aposTreinamento)) carteira-bloqueio-nova-pagina @endif"
    @if(!empty($forPdf))
        style="--bloqueio-altura-linha-par: {{ number_format($bloqueio_altura_linha_par_mm, 2, '.', '') }}mm;"
    @endif
>
    @foreach(collect($treinamentos ?? [])->chunk(2) as $duplaTreinamentos)
        <div class="etiqueta-bloqueio-dupla-folha @if($duplaTreinamentos->count() === 2) etiqueta-bloqueio-dupla-folha--duas-linhas @endif">
            @foreach($duplaTreinamentos as $treinamento)
                @php
                    $segmento_config = $treinamento['segmento_config'] ?? [];
                    $ramal_emergencia = $segmento_config['ramal_emergencia'] ?? '1199';
                    $texto_nao_use = $segmento_config['bloqueio_texto_nao_use'] ?? 'NÃO USE, MOVA OU OPERE ENQUANTO ESTA ETIQUETA ESTIVER COLOCADA';
                    $texto_demissao = $segmento_config['bloqueio_texto_demissao'] ?? 'QUEM OPERAR O EQUIPAMENTO OU REMOVER A ETIQUETA ESTÁ SUJEITO A DEMISSÃO';
                    $texto_cuidado = $segmento_config['bloqueio_texto_cuidado'] ?? 'CUIDADO!';
                    $texto_homens_trabalhando = $segmento_config['bloqueio_texto_homens_trabalhando'] ?? 'HOMENS TRABALHANDO NÃO OPERE ESTE EQUIPAMENTO';
                @endphp
                <div class="etiqueta-bloqueio-par" role="presentation">
                    <div class="etiqueta-bloqueio-celula">
                        <div class="etiqueta etiqueta-frente">
                            <div class="logo"></div>
                            <div class="content">
                                <div class="boxBlack">
                                    <div class="circuloRed">
                                        <h3 class="tituloPerigo">PERIGO</h3>
                                    </div>
                                </div>

                                <h3 class="text-center colorRed etiqueta-texto-aviso">
                                    {{ $texto_nao_use }}
                                </h3>

                                <h3 class="text-center etiqueta-texto-aviso-espacado">
                                    {{ $texto_demissao }}
                                </h3>

                                @if(strlen($empresa['logo']) > 0 )
                                    <div class="etiqueta-logos-row">
                                        <img
                                            src="{{$empresa['logo']}}"
                                            alt="Logo" title="Logo" style="width: 3cm">
                                    </div>
                                    <br>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="etiqueta-bloqueio-celula">
                        <div class="etiqueta etiqueta-costa">
                            <div class="logo"></div>
                            <div class="content">
                                <div class="boxBlack">
                                    <div class="circuloRed">
                                        <h3 class="tituloPerigo">PERIGO</h3>
                                    </div>
                                </div>
                                <h2 class="text-center etiqueta-titulo-cuidado">
                                    {{ $texto_cuidado }}</h2>

                                <div class="etiqueta-lateral-com-foto">
                                    <div class="etiqueta-texto-lateral">
                                        <h6 class="text-center">
                                            {{ $texto_homens_trabalhando }}
                                        </h6>
                                    </div>

                                    <div class="fotoTres"
                                         style="object-fit: cover;
                                                background-size: cover;
                                                background-image: url(
                                                @if(count($treinamento['feedback_curriculo']['curriculo']['foto_tres']) > 0)
                                                    {{ $treinamento['feedback_curriculo']['curriculo']['foto_tres'][0]['url_base64'] ?? $treinamento['feedback_curriculo']['curriculo']['foto_tres'][0]['url'] ?? asset('sem_foto.png') }}
                                                @else
                                                    {{ asset('sem_foto.png') }}
                                                @endif
                                                )">
                                    </div>
                                </div>

                                <h5 class="etiqueta-ramal">
                                    RAMAL DE EMERGÊNCIA: {{ $ramal_emergencia }}
                                </h5>
                                <h6 class="etiqueta-meta-linha">
                                    NOME: <strong>
                                        {{ mb_strtoupper($treinamento['feedback_curriculo']['curriculo']['nome']) }}
                                    </strong>
                                </h6>
                                <h6 class="etiqueta-meta-linha">
                                    CHAPA/ID: <strong>
                                        {{ mb_strtoupper($treinamento['feedback_curriculo']['admissao'] ? $treinamento['feedback_curriculo']['admissao']['numero_cracha'] : null) }}
                                    </strong>
                                </h6>
                                <h6 class="etiqueta-meta-linha">AREA/EMPRESA:
                                    <strong>
                                            <span style="color: #0e6fb6 !important">
                                            {{ $treinamento['feedback_curriculo']['empresa']['nome_fantasia'] }}
                                            </span>
                                    </strong>
                                </h6>
                                <h6 class="etiqueta-meta-linha">
                                    FONE/RAMAL:
                                    <strong>{{ $treinamento['telefone'] ?? "Não Informado" }}</strong>
                                </h6>
                                <h6 class="etiqueta-meta-linha">DATA:
                                    <strong>
                                                <span style=" color: #0e6fb6 !important">
                                                    PERMANENTE
                                                </span>
                                    </strong>
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @unless($loop->last)
            <div class="etiqueta-bloqueio-page-break" aria-hidden="true"></div>
        @endunless
    @endforeach
</div>
