<div class="a4" style="padding: 20px; margin-left: 0;">
    <?php $cont = 0; ?>
    @foreach($treinamentos as $treinamento)
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
                    NÃO USE, MOVA OU OPERE ENQUANTO ESTA ETIQUETA ESTIVER COLOCADA
                </h3>

                <h3 class="text-center" style="margin-top: 1cm; font-weight: bold !important; font-size: 16pt;">
                    QUEM OPERAR O EQUIPAMENTO OU REMOVER A ETIQUETA ESTÁ SUJEITO A DEMISSÃO
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
                    CUIDADO!</h2>

                <div style="display: flex; flex-direction: row; align-items: center; margin-top: 0.3cm">
                    <div style=" width: 3.9cm; font-size: 20pt; ">
                        <h6 class="text-center" style="font-weight: bold;">
                            HOMENS TRABALHANDO NÃO OPERE ESTE EQUIPAMENTO
                        </h6>
                    </div>

                    <div class="fotoTres"
                         style="object-fit: cover;
                                background-size: cover;
                                background-image: url(
                                {{ count($treinamento['feedback_curriculo']['curriculo']['foto_tres']) > 0 ?$treinamento['feedback_curriculo']['curriculo']['foto_tres'][0]['url']: asset('sem_foto.png')}}
                                )">
                    </div>
                </div>

                <h5 style="font-size: 13pt; font-weight: bold; color: red; margin-top: 0.2cm; margin-bottom: 0.2cm;">
                    RAMAL DE EMERGÊNCIA: 1199
                </h5>
                <h6 style="margin-top: 5px; font-size: 10.5pt;">
                    NOME: <strong>
                        {{ mb_strtoupper($treinamento['feedback_curriculo']['curriculo']['nome']) }}
                    </strong>
                </h6>
                <h6 style="margin-top: 5px; font-size: 10.5pt;">
                    CHAPA/ID: <strong>
                        {{ mb_strtoupper($treinamento['feedback_curriculo']['admissao'] ? $treinamento['feedback_curriculo']['admissao']['numero_cracha'] : null) }}
                    </strong>
                </h6>
                <h6 style="margin-top: 5px; font-size: 10.5pt;">AREA/EMPRESA:
                    <strong>
                            <span style="color: #0e6fb6 !important">
                            {{ $treinamento['feedback_curriculo']['empresa']['nome_fantasia'] }}
                            </span>
                    </strong>
                </h6>
                <h6 style="margin-top: 5px; font-size: 10.5pt;">
                    FONE/RAMAL:
                    <strong>{{ $treinamento['telefone'] ?? "Não Informado" }}</strong>
                </h6>
                <h6 style="margin-top: 5px;font-size: 10.5pt;">DATA:
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
