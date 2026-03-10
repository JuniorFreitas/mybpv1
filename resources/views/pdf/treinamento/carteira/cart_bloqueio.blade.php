<div class="a4" style="padding: 20px; margin-left: 0;">
    <?php $cont = 0; ?>
    @foreach($treinamentos as $treinamento)
        @php
            $segmento_config = $treinamento['segmento_config'] ?? [];
            $ramal_emergencia = $segmento_config['ramal_emergencia'] ?? '1199';
            $texto_nao_use = $segmento_config['bloqueio_texto_nao_use'] ?? 'NÃO USE, MOVA OU OPERE ENQUANTO ESTA ETIQUETA ESTIVER COLOCADA';
            $texto_demissao = $segmento_config['bloqueio_texto_demissao'] ?? 'QUEM OPERAR O EQUIPAMENTO OU REMOVER A ETIQUETA ESTÁ SUJEITO A DEMISSÃO';
            $texto_cuidado = $segmento_config['bloqueio_texto_cuidado'] ?? 'CUIDADO!';
            $texto_homens_trabalhando = $segmento_config['bloqueio_texto_homens_trabalhando'] ?? 'HOMENS TRABALHANDO NÃO OPERE ESTE EQUIPAMENTO';
        @endphp
        <div style="margin-top: 20px" class="etiqueta">
            <div class="logo"></div>
            <div class="content">
                <div class="boxBlack">
                    <div class="circuloRed">
                        <h3 class="tituloPerigo" style="font-size: 20pt;">PERIGO</h3>
                    </div>
                </div>

                <h3 class="text-center colorRed"
                    style="margin-top: 15px; font-weight: bold !important; font-size: 16pt;">
                    {{ $texto_nao_use }}
                </h3>

                <h3 class="text-center" style="margin-top: 1cm; font-weight: bold !important; font-size: 16pt;">
                    {{ $texto_demissao }}
                </h3>

                @if(strlen($empresa['logo']) > 0 )
                    <div style="display: flex; margin: 0 auto; margin-top: 0.7cm;">
                        <img
                            src="{{$empresa['logo']}}"
                            alt="Logo" title="Logo" style="width: 3cm">
                    </div>
                    <br>
                @endif
            </div>
        </div>

        <div style="margin-top: 20px" class="etiqueta">
            <div class="logo"></div>
            <div class="content">
                <div class="boxBlack">
                    <div class="circuloRed">
                        <h3 class="tituloPerigo" style="font-size: 20pt;">PERIGO</h3>
                    </div>
                </div>
                <h2 class="text-center"
                    style="margin-top: 0.3cm; color: red; text-decoration: underline; font-size: 24pt;">
                    {{ $texto_cuidado }}</h2>

                <div style="display: flex; flex-direction: row; align-items: center; margin-top: 0.3cm">
                    <div style=" width: 3.9cm; font-size: 20pt; ">
                        <h6 class="text-center" style="font-weight: bold;">
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

                <h5 style="font-size: 13pt; font-weight: bold; color: red; margin-top: 0.2cm; margin-bottom: 0.2cm;">
                    RAMAL DE EMERGÊNCIA: {{ $ramal_emergencia }}
                </h5>
                <h6 style="margin-top: 5px; font-size: 10pt;">
                    NOME: <strong>
                        {{ mb_strtoupper($treinamento['feedback_curriculo']['curriculo']['nome']) }}
                    </strong>
                </h6>
                <h6 style="margin-top: 5px; font-size: 10pt;">
                    CHAPA/ID: <strong>
                        {{ mb_strtoupper($treinamento['feedback_curriculo']['admissao'] ? $treinamento['feedback_curriculo']['admissao']['numero_cracha'] : null) }}
                    </strong>
                </h6>
                <h6 style="margin-top: 5px; font-size: 10pt;">AREA/EMPRESA:
                    <strong>
                            <span style="color: #0e6fb6 !important">
                            {{ $treinamento['feedback_curriculo']['empresa']['nome_fantasia'] }}
                            </span>
                    </strong>
                </h6>
                <h6 style="margin-top: 5px; font-size: 10pt;">
                    FONE/RAMAL:
                    <strong>{{ $treinamento['telefone'] ?? "Não Informado" }}</strong>
                </h6>
                <h6 style="margin-top: 5px;font-size: 10pt;">DATA:
                    <strong>
                                <span style=" color: #0e6fb6 !important">
                                    PERMANENTE
                                </span>
                    </strong>
                </h6>
            </div>
        </div>
            <?php $cont++ ?>
        @if ($cont==2)
                <?php $cont = 0; ?>
            <div style="page-break-after: always; margin: 0; padding: 0;"></div>
        @endif
    @endforeach
</div>
