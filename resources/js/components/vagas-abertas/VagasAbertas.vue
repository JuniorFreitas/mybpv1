<template>
    <div>
        <p class="mt-2 text-center" v-if="preload"><i class="fa fa-spinner fa-pulse"></i>Carregando...</p>
        <main class="py-5 my-5" v-if="!preload && dados_vaga">
            <div class="container">
                <header class="section-header py-5">
                    <h3>VAGA {{ dados_vaga.titulo }}</h3>
                </header>

                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">{{ dados_vaga.vaga.nome }}</h5>
                        <p class="card-text">{{ dados_vaga.descricao }}</p>
                        <p class="card-subtitle mb-2 text-muted">
                            <i class="fas fa-map-marker-alt"></i> {{ dados_vaga.municipio.nome }} - {{ dados_vaga.municipio.uf }}
                        </p>
                        <div class="float-md-left">
                            <a href="javascript://" class="btn mr-1 mb-2 btn-primary" target="_blank"
                                 @click="$refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()">CANDIDATAR-SE</a
                            >
                        </div>

                        <!--                        href="https://api.whatsapp.com/send?text=Vaga aberta no site da BPSE para {{dados_vaga.vaga.nome}} -  {{ dados_vaga.municipio.nome }} - {{ dados_vaga.municipio.uf }} é só acessar https://bpse.com.br/vaga-aberta/{{dados_vaga.id}}/{{dados_vaga.Vaga.slug}}"-->
                        <!---->
                        <!--                        href="https://www.facebook.com/sharer/sharer.php?u=https://bpse.com.br/vaga-aberta/{{dados_vaga.id}}/"-->

                        <div class="float-md-right">
                            <a class="btn mr-1 mb-2 btn-outline-primary" target="_blank"><i class="fab fa-whatsapp"></i> COMPARTILHAR</a>
                            <a class="btn mr-1 mb-2 btn-outline-primary" target="_blank"><i class="fab fa-facebook"></i> COMPARTILHAR</a>
                        </div>
                    </div>
                </div>
            </div>

            <modal id="janelaConfirmar" modal-pai="janelaCadastrar" :fechar="false" titulo="Confirmação" ref="modal_janelaConfirmar">
                <template #conteudo>
                    <div>
                        <h5>Você tem certeza que deseja cadastrar seu curriculo SEM EXPERIÊNCIA?</h5>
                        <div class="text-center">
                            <button class="btn btn-danger" data-dismiss="modal" @click="addLIExperiencia">Não</button>
                            <button class="btn btn-success" @click="semexperienciaSim">SIM</button>
                        </div>
                    </div>
                </template>
            </modal>

            <modal id="janelaCadastrar" modal-pai="janelaVagas" titulo="Cadastro" :fechar="!preload" :size="90" ref="modal_janelaCadastrar">
                <template #conteudo>
                    <fieldset v-if="!exibiFormulario && !preload">
                        <legend>Informe</legend>
                        <form @submit.prevent="buscaCurriculo">
                            <div class="form-group">
                                <label>CPF</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="form.cpf"
                                    placeholder="CPF"
                                    autocomplete="mastertag"
                                    v-mascara:cpf
                                    onblur="valida_cpf_vazio(this)"
                                />
                            </div>
                            <div class="form-group">
                                <label>Nascimento</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="form.nascimento"
                                    placeholder="Ex: 10/10/2010"
                                    v-mascara:data
                                    autocomplete="mastertag"
                                    onblur="valida_data_vazio(this)"
                                />
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Enviar</button>
                        </form>
                    </fieldset>

                    <span v-show="preload"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</span>

                    <div v-if="exibiFormulario">
                        <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                            <p style="font-size: 1.2rem" class="text-center">
                                <i class="icon fa fa-check"></i>
                                A confirmação do seu cadastro foi enviada por e-mail, verifique sua CAIXA DE ENTRADA ou LIXO ELETRÔNICO/SPAM.
                                <br />
                                Caso esteja no LIXO ELETRÔNICO/SPAM marque que não é SPAM, pois todas as etapas serão enviadas dessa forma por e-mail.
                            </p>
                            <br />
                            <img src="https://bpse.com.br/imagens/bepinhas/branca_2.png" alt="bepinha" class="img-fluid mx-auto d-flex" />
                        </div>
                        <div class="alert alert-success alert-dismissible" v-show="atualizado">
                            <p style="font-size: 1.2rem" class="text-center">
                                <i class="icon fa fa-check"></i>
                                A confirmação do seu cadastro foi enviada por e-mail, verifique sua CAIXA DE ENTRADA ou LIXO ELETRÔNICO/SPAM.
                                <br />
                                Caso esteja no LIXO ELETRÔNICO/SPAM marque que não é SPAM, pois todas as etapas serão enviadas dessa forma por e-mail.
                            </p>
                            <br />
                            <img src="https://bpse.com.br/imagens/bepinhas/branca_2.png" alt="bepinha" class="img-fluid mx-auto d-flex" />
                        </div>

                        <form v-if="!preloadAjax && !cadastrado && !atualizado" id="form">
                            <p class="alert alert-warning">
                                ATENÇÃO: Cadastre um E-mail válido, pois e-mail inválido ou inexistente desclassificará o candidato.
                            </p>
                            <fieldset>
                                <legend>Dados Pessoais</legend>
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Nome</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="form.nome"
                                                placeholder="Nome"
                                                autocomplete="mastertag"
                                                onblur="valida_campo_vazio(this, 3)"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>CPF</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="form.cpf"
                                                v-if="!possuiCadastro"
                                                placeholder="CPF"
                                                @blur="buscaCpf"
                                                autocomplete="mastertag"
                                                v-mascara:cpf
                                                onblur="valida_cpf_vazio(this)"
                                            />

                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="form.cpf"
                                                v-if="possuiCadastro"
                                                placeholder="CPF"
                                                :disabled="possuiCadastro"
                                                autocomplete="mastertag"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>RG</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="form.rg"
                                                placeholder="RG"
                                                autocomplete="mastertag"
                                                v-mascara:numero
                                                onblur="valida_campo_vazio(this, 3)"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Data Emissão RG</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="form.rg_data_emissao"
                                                placeholder="Ex: 10/10/2010"
                                                autocomplete="mastertag"
                                                v-mascara:data
                                                onblur="valida_data_vazio(this)"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Orgão Expeditor (RG)</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="form.orgao_expeditor"
                                                placeholder="Orgão"
                                                autocomplete="mastertag"
                                                onblur="valida_campo_vazio(this, 1)"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Naturalidade</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="form.naturalidade"
                                                placeholder="Naturalidade"
                                                autocomplete="mastertag"
                                                onblur="valida_campo_vazio(this, 1)"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>CNH</label>
                                            <input type="text" class="form-control" v-model="form.cnh" placeholder="Tipo da CNH" autocomplete="mastertag" />
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Nascimento</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="form.nascimento"
                                                placeholder="Ex: 10/10/2010"
                                                :disabled="possuiCadastro"
                                                v-mascara:data
                                                autocomplete="mastertag"
                                                onblur="valida_data_vazio(this)"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Sexo</label>
                                            <select
                                                class="form-control"
                                                onchange="valida_campo_vazio(this, 1)"
                                                onblur="valida_campo_vazio(this, 1)"
                                                v-model="form.sexo"
                                            >
                                                <option value="">Selecione</option>
                                                <option value="Masculino">Masculino</option>
                                                <option value="Feminino">Feminino</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12"></div>

                                    <div class="col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Nome do pai</label>
                                            <input type="text" class="form-control" v-model="form.filiacao_pai" placeholder="Nome" autocomplete="mastertag" />
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Nome da mãe</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="form.filiacao_mae"
                                                onblur="valida_campo_vazio(this, 3)"
                                                placeholder="Nome"
                                                autocomplete="mastertag"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>E-mail</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="form.email"
                                                placeholder="Informe seu melhor e-mail"
                                                autocomplete="mastertag"
                                                onblur="validaEmailVazio(this)"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend>Endereço</legend>
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-lg-12 col-xl-12">
                                        <endereco :model="form"></endereco>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="table-warning">
                                <legend>Contato</legend>
                                <label class="text-danger">Adicionar no minimo DOIS contatos, pelo menos UM sendo WhatsApp</label>
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-lg-12 col-xl-12">
                                        <telefones :model="form.telefones" :model-delete="form.telefonesDelete" :pais="false" :ramal="false"></telefones>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend>Formação</legend>
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Formação</label>
                                            <select name="formacao" class="form-control" v-model="form.formacao">
                                                <option value="">Selecione</option>
                                                <option v-for="(item, index) in escolaridades" :value="item.id" :key="item.id || index">{{ item.tipo }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Instituição</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="form.formacao_instituicao"
                                                placeholder="Ex: USP"
                                                autocomplete="mastertag"
                                                onblur="valida_campo_vazio(this, 3)"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6" v-show="form.formacao >= 8">
                                        <div class="form-group">
                                            <label>Curso</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="form.formacao_curso"
                                                placeholder="Ex: Administração"
                                                autocomplete="mastertag"
                                                onblur="valida_campo_vazio(this, 3)"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select class="form-control" v-model="form.formacao_status">
                                                <option value="Concluido">Concluido</option>
                                                <option value="Cursando">Cursando</option>
                                                <option value="Trancado">Trancado</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group table-warning py-2 px-2">
                                        <label>Possuí Experiências?</label>
                                        <select class="form-control" v-model="form.temexperiencia">
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <fieldset v-if="form.temexperiencia">
                                <legend>Experiências</legend>
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                        <button class="btn btn-success mb-2" @click="addLIExperiencia($event.target)">
                                            <span class="fas fa-plus" aria-hidden="true"></span>
                                            ADICIONAR EXPERIÊNCIA
                                        </button>
                                    </div>
                                </div>
                                <div
                                    class="row"
                                    v-show="form.experiencias.length > 0"
                                    v-for="(obj, index) in form.experiencias"
                                    :key="obj.id"
                                    style="margin-bottom: 13px"
                                >
                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Empresa</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="obj.empresa"
                                                placeholder="Nome da Empresa"
                                                autocomplete="mastertag"
                                                onblur="valida_campo_vazio(this, 3)"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Cargo</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="obj.cargo"
                                                placeholder="Cargo"
                                                autocomplete="mastertag"
                                                onblur="valida_campo_vazio(this, 3)"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Nome Referência</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="obj.referencia_nome"
                                                placeholder="Ex: Ana"
                                                autocomplete="mastertag"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Telefone Referência</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="obj.referencia_telefone"
                                                placeholder="Ex: (98) 9 9999-9999"
                                                autocomplete="mastertag"
                                                v-mascara:telefone
                                            />
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-12 col-lg-12 col-xl-12">
                                        <div class="form-group">
                                            <label>Principais Atividades</label>
                                            <textarea
                                                class="form-control"
                                                v-model="obj.principais_atv"
                                                placeholder="Ex: Recepcionista atendimento ao cliente"
                                                autocomplete="mastertag"
                                                onblur="valida_campo_vazio(this, 3)"
                                            >
                                            </textarea>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-2 col-lg-2 col-xl-2">
                                        <div class="form-group">
                                            <label>Data Inicio</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="obj.data_inicio"
                                                placeholder="Ex: 02/04/2012"
                                                autocomplete="mastertag"
                                                v-mascara:data
                                                onblur="valida_data_vazio(this)"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-2 col-lg-2 col-xl-2">
                                        <div class="form-group">
                                            <label>Data Fim </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="obj.data_fim"
                                                placeholder="Se for atual deixe em branco"
                                                autocomplete="mastertag"
                                                v-mascara:data
                                                onblur="valida_data(this)"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-1">
                                        <button class="btn btn-danger" style="margin-top: 24px" @click="removerLIExperiencia(index)">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>

                                    <div class="col-12">
                                        <hr />
                                    </div>
                                </div>
                            </fieldset>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group table-warning py-2 px-2">
                                        <label>Possuí Qualificações?</label>
                                        <select class="form-control" v-model="form.temqualificacao">
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <fieldset v-if="form.temqualificacao">
                                <legend>Qualificações</legend>
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                        <button class="btn btn-success mb-2" @click="addLIQualificacao($event.target)">
                                            <span class="fas fa-plus" aria-hidden="true"></span>
                                            ADCIONAR QUALIFICAÇÃO
                                        </button>
                                    </div>
                                </div>
                                <div
                                    class="row"
                                    v-show="form.qualificacoes.length > 0"
                                    v-for="(obj, index) in form.qualificacoes"
                                    :key="obj.id"
                                    style="margin-bottom: 13px"
                                >
                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Curso</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="obj.nome"
                                                placeholder="Ex: Tecnico em Contabilidade"
                                                autocomplete="mastertag"
                                                onblur="valida_campo_vazio(this, 3)"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Instituição</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="obj.instituicao"
                                                placeholder="Ex: SEBRAE"
                                                autocomplete="mastertag"
                                                onblur="valida_campo_vazio(this, 3)"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-2 col-lg-2 col-xl-2">
                                        <div class="form-group">
                                            <label>Mês Conclusão</label>
                                            <select class="form-control" v-model="obj.mes_conclusao">
                                                <option value="">Selecione</option>
                                                <option value="01">Janeiro</option>
                                                <option value="02">Fevereiro</option>
                                                <option value="03">Março</option>
                                                <option value="04">Abril</option>
                                                <option value="05">Maio</option>
                                                <option value="06">Junho</option>
                                                <option value="07">Julho</option>
                                                <option value="08">Agosto</option>
                                                <option value="09">Setembro</option>
                                                <option value="10">Outubro</option>
                                                <option value="11">Novembro</option>
                                                <option value="12">Dezembro</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-2 col-lg-2 col-xl-2">
                                        <div class="form-group">
                                            <label>Ano Conclusão</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="obj.ano_conclusao"
                                                placeholder="Ex: Concluido"
                                                autocomplete="mastertag"
                                                v-mascara:numero
                                                onblur="valida_campo_vazio(this, 4)"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-1">
                                        <button class="btn btn-danger" style="margin-top: 24px" @click="removerLIQualificacao(index)">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="col-12">
                                        <hr />
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <legend>Outras Informações</legend>
                                <div class="row">
                                    <div class="col-12 col-sm-3">
                                        <div class="form-group">
                                            <label>Cota pessoa com deficiência (Lei nº 8.213/91)</label>
                                            <select
                                                class="form-control"
                                                onchange="valida_campo_vazio(this, 1)"
                                                onblur="valida_campo_vazio(this, 1)"
                                                v-model="form.pcd"
                                            >
                                                <option value="">Selecione</option>
                                                <option :value="true">Sim</option>
                                                <option :value="false">Não</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-3" v-if="form.pcd">
                                        <div class="form-group">
                                            <label>CID (Código Internacional de Doenças)</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                onblur="valida_campo_vazio(this, 1)"
                                                placeholder="Informe o CID"
                                                v-model="form.cid"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-3">
                                        <div class="form-group">
                                            <label>Disponibilidade para viajar</label>
                                            <select
                                                class="form-control"
                                                v-model="form.viajar"
                                                onchange="valida_campo_vazio(this, 1)"
                                                onblur="valida_campo_vazio(this, 1)"
                                            >
                                                <option value="">Selecione</option>
                                                <option :value="true">Sim</option>
                                                <option :value="false">Não</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-3">
                                        <div class="form-group">
                                            <label>Disponibilidade para trabalhar aos Sábados</label>
                                            <select
                                                class="form-control"
                                                v-model="form.disponibilidade_sabado"
                                                onchange="valida_campo_vazio(this, 1)"
                                                onblur="valida_campo_vazio(this, 1)"
                                            >
                                                <option value="">Selecione</option>
                                                <option :value="true">Sim</option>
                                                <option :value="false">Não</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-3">
                                        <div class="form-group">
                                            <label>Disponibilidade para trabalhar aos Domingos</label>
                                            <select class="form-control" v-model="form.disponibilidade_domingo">
                                                <option value="">Selecione</option>
                                                <option :value="true">Sim</option>
                                                <option :value="false">Não</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </template>
                <template #rodape>
                    <div v-show="exibiFormulario">
                        <button type="button" class="btn btn-primary" v-show="!editando && !cadastrado && !atualizado && !preloadAjax" @click="cadastrar">
                            <i class="fa fa-save"></i> Salvar
                        </button>
                    </div>
                </template>
            </modal>
        </main>
    </div>
</template>
<script>
import telefones from '../Telefones'
import endereco from '../Endereco'

export default {
    components: {
        telefones,
        endereco
    },
    props: {
        empresa_id: {
            type: Number,
            required: true
        },
        vaga_aberta_id: {
            type: Number,
            required: true
        },
        slug: {
            type: String,
            required: false
        }
    },

    data() {
        return {
            hash: String(Math.random()).substr(2),
            tituloJanela: 'Recrutamento',
            preloadAjax: true,
            editando: false,
            existe: false,

            possuiCadastro: false,
            cadastroNovo: false,

            exibiFormulario: false,

            ativo: 'home',

            semexperiencia: false,

            preloadVagas: true,

            preload: false,
            cadastrado: false,
            exibindo: false,
            mensagem: false,
            atualizado: false,

            dados_vaga: null,
            escolaridades: [],

            form: {
                empresa_id: '',
                vaga_aberta_id: '',
                temqualificacao: true,
                temexperiencia: true,
                id: '',
                cpf: '',
                nome: '',
                cnh: '',
                nascimento: '',
                logradouro: '',
                complemento: '',
                bairro: '',
                municipio: '',
                uf: '',
                cep: '',
                email: '',
                filiacao_pai: '',
                filiacao_mae: '',
                formacao: 7,
                formacao_instituicao: '',
                formacao_curso: '',
                formacao_status: 'Concluido',
                vaga_pretendida: '',
                uf_vaga: '',
                municipio_id: '',
                rg: '',
                rg_data_emissao: '',
                orgao_expeditor: '',
                naturalidade: '',

                autocomplete_label_municipio_modal: '',
                autocomplete_label_municipio_modal_anterior: '',

                pcd: '',
                cid: '',
                viajar: '',
                disponibilidade_sabado: '',
                disponibilidade_domingo: '',
                sexo: '',

                qualificacoes: [
                    {
                        nova: true,
                        nome: '',
                        instituicao: '',
                        mes_conclusao: '01',
                        ano_conclusao: '2019'
                    }
                ],
                qualificacoesDelete: [],

                experiencias: [
                    {
                        nova: true,
                        empresa: '',
                        cargo: '',
                        principais_atv: '',
                        data_inicio: '',
                        data_fim: '',
                        referencia_nome: '',
                        referencia_telefone: ''
                    }
                ],
                experienciasDelete: [],

                telefones: [
                    {
                        detalhe: '',
                        novo: true,
                        numero: '',
                        pais: '55',
                        ramal: '',
                        tipo: 'whatsapp'
                    }
                ],
                telefonesDelete: []
            },
            formDefault: null
        }
    },

    mounted() {
        this.preload = true
        this.atualizar()
        this.formDefault = _.cloneDeep(this.form)
    },

    methods: {
        async atualizar() {
            this.preload = true
            try {
                const response = await axios.post(`${URL_SITE}/api/${this.empresa_id}/vaga-aberta/${this.vaga_aberta_id}`)
                if (response.status === 200) {
                    this.dados_vaga = response.data.dados
                    this.form.empresa_id = this.empresa_id
                    this.form.vaga_aberta_id = this.vaga_aberta_id
                }
            } finally {
                this.preload = false
            }
        },
        buscaCurriculo() {
            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            //BUSCA POR CPF
            if (this.form.cpf.length === 14 && this.form.nascimento.length === 10) {
                this.preloadAjax = true
                axios
                    .post(`${URL_SITE}/api/busca-curriculo`, {
                        cpf: this.form.cpf,
                        nascimento: this.form.nascimento
                    })
                    .then((response) => {
                        let data = response.data
                        if (response.status === 200) {
                            if (data === 'error_nascimento') {
                                mostraErro('', 'CPF encontrado, porém data de nascimento não confere')
                                this.preloadAjax = false
                            } else {
                                Object.assign(this.form, data.curriculo)
                                Object.assign(this.escolaridades, data.escolaridades)
                                this.possuiCadastro = !!data.possuiCadastro
                                this.exibiFormulario = true
                                this.preloadAjax = false
                            }
                        }
                    })
                    .catch((error) => {
                        mostraErro('', error)
                        this.preloadAjax = false
                    })
            }
        },

        addLIQualificacao() {
            let obj = {}
            obj.nova = true
            obj.nome = ''
            obj.instituicao = ''
            obj.mes_conclusao = '01'
            obj.ano_conclusao = '2019'
            this.form.qualificacoes.unshift(obj)
        },
        removerLIQualificacao(index) {
            if (this.possuiCadastro) {
                this.form.qualificacoesDelete.push(this.form.qualificacoes[index].id)
            }
            this.form.qualificacoes.splice(index, 1)
        },
        addLIExperiencia() {
            let obj = {}
            obj.nova = true
            obj.empresa = ''
            obj.cargo = ''
            obj.principais_atv = ''
            obj.data_inicio = ''
            obj.data_fim = ''
            obj.referencia_nome = ''
            obj.referencia_telefone = ''
            this.form.experiencias.unshift(obj)
        },

        removerLIExperiencia(index) {
            if (this.possuiCadastro) {
                this.form.experienciasDelete.push(this.form.experiencias[index].id)
            }
            this.form.experiencias.splice(index, 1)
        },
        semexperienciaSim() {
            this.semexperiencia = true
            this.$refs.modal_janelaConfirmar && this.$refs.modal_janelaConfirmar.fecharModal()
            this.cadastrar()
        },
        cadastrar() {
            // if (this.form.municipio_id === '') {
            //     valida_campo_vazio($('#mun_' + this.hash), 1);
            //     $('#janelaCadastrar #mun_' + this.hash).focus().trigger('blur');
            //     mostraErro('Erro', 'O Campo Município não pode ficar vazio');
            //     return false;
            // }

            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                mostraErro('Erro', 'Verifique os campos.')
                return false
            }

            if (this.form.experiencias.length === 0 && !this.semexperiencia) {
                this.$refs.modal_janelaConfirmar && this.$refs.modal_janelaConfirmar.abrirModal()
                return false
            }
            this.preloadAjax = true

            axios
                .post(`${URL_SITE}/api/cadastra-curriculo`, this.form)
                .then((response) => {
                    this.preloadAjax = false
                    if (this.possuiCadastro) {
                        this.atualizado = true
                    } else {
                        this.cadastrado = true
                    }

                    if (response.status === 201) {
                        mostraSucesso('Sua inscrição foi feita com sucesso! Verifique seu e-mail.')
                    } else {
                        mostraErro(response.data.msg)
                    }

                    this.form = _.cloneDeep(this.formDefault)
                })
                .catch((error) => {
                    this.preloadAjax = false
                })
        },
        buscaCpf() {
            if (this.form.cpf.length === 14) {
                axios
                    .post(`${URL_SITE}/api/busca-cpf/`, { cpf: this.form.cpf })
                    .then((response) => {
                        let data = response.data
                        if (data.length > 0) {
                            this.form.cpf = ''
                            let dataFormatada = data[0].created_at.substring(0, 10).split('-').reverse().join('/')
                            let Hora = data[0].created_at.substring(11, 16)
                            mostraErro(
                                '',
                                `CPF ${data[0].cpf} já cadastrado em nossa base de dados com outro e-mail ou data de nascimento em ${dataFormatada} às ${Hora}h,
                                por favor entre em contato com o atendimento para melhor resolução.`
                            )
                        }
                    })
                    .catch((error) => console.log(error))
            }
        },

        formNovo() {
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.exibiFormulario = false
            this.possuiCadastro = false

            this.tituloJanela = 'Cadastrando a uma Vaga'

            formReset()
            setupCampo()
            this.form = _.cloneDeep(this.formDefault) //copia
        }
    }
}
</script>

<style scoped>
.card {
    border: none;
    background: transparent;
}

ul.timeline {
    list-style-type: none;
    position: relative;
}

ul.timeline:before {
    content: ' ';
    background: #d4d9df;
    display: inline-block;
    position: absolute;
    left: 29px;
    width: 2px;
    height: 100%;
    z-index: 400;
}

ul.timeline > li {
    margin: 20px 0;
    padding-left: 20px;
}

ul.timeline > li:before {
    content: ' ';
    background: white;
    display: inline-block;
    position: absolute;
    border-radius: 50%;
    border: 3px solid #653132;
    left: 20px;
    width: 20px;
    height: 20px;
    z-index: 400;
}

.trackind {
    padding: 0.5rem 0.8rem;
    background-color: #f4f4f4;
    border-radius: 0.5rem;
}
</style>
