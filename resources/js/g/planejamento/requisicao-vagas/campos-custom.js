const app = new Vue({
    el: '#app',
    data: {
        tituloJanela: 'Campos personalizados - Requisição de Vaga',
        campos: [],
        carregando: true,
        editando: false,
        salvando: false,
        opcoesTexto: '',
        form: {
            id: null,
            label: '',
            tipo: 'sim_nao',
            opcoes: null,
            obrigatorio: false,
            ordem: 0
        }
    },
    mounted() {
        this.listar();
    },
    methods: {
        tipoLabel(tipo) {
            const map = { sim_nao: 'Sim/Não', texto: 'Texto', select: 'Select' };
            return map[tipo] || tipo;
        },
        listar() {
            this.carregando = true;
            axios.get(`${URL_ADMIN}/planejamento/requisicao-vaga/campos-custom`)
                .then((r) => {
                    this.campos = r.data || [];
                })
                .catch(() => {
                    this.campos = [];
                })
                .finally(() => {
                    this.carregando = false;
                });
        },
        abrirModal(campo) {
            this.editando = !!campo;
            this.tituloJanela = campo ? 'Editar campo' : 'Novo campo';
            this.form = {
                id: campo ? campo.id : null,
                label: campo ? campo.label : '',
                tipo: campo ? campo.tipo : 'sim_nao',
                opcoes: campo && campo.opcoes ? campo.opcoes : null,
                obrigatorio: campo ? !!campo.obrigatorio : false,
                ordem: campo != null && campo.ordem !== undefined ? campo.ordem : this.campos.length
            };
            this.opcoesTexto = (this.form.opcoes && Array.isArray(this.form.opcoes)) ? this.form.opcoes.join('\n') : '';
            if (!campo) {
                $('#janelaCadastrar').modal('show');
            }
        },
        opcoesParaArray() {
            const lines = (this.opcoesTexto || '').split(/\r?\n/).map(s => s.trim()).filter(Boolean);
            return lines.length ? lines : null;
        },
        salvar() {
            if (!this.form.label.trim()) {
                mostraErro('', 'Informe o nome do campo.');
                return;
            }
            if (!this.form.tipo) {
                mostraErro('', 'Selecione o tipo do campo.');
                return;
            }
            const opcoesArray = this.form.tipo === 'select' ? this.opcoesParaArray() : null;
            if (this.form.tipo === 'select' && (!opcoesArray || !opcoesArray.length)) {
                mostraErro('', 'Para tipo Select, informe ao menos uma opção.');
                return;
            }
            this.salvando = true;
            const payload = {
                label: this.form.label.trim(),
                tipo: this.form.tipo,
                opcoes: opcoesArray,
                obrigatorio: this.form.obrigatorio,
                ordem: parseInt(this.form.ordem, 10) || 0
            };
            const req = this.editando
                ? axios.put(`${URL_ADMIN}/planejamento/requisicao-vaga/campos-custom/${this.form.id}`, payload)
                : axios.post(`${URL_ADMIN}/planejamento/requisicao-vaga/campos-custom`, payload);
            req
                .then(() => {
                    mostraSucesso('', 'Salvo com sucesso.');
                    $('#janelaCadastrar').modal('hide');
                    this.listar();
                })
                .catch((err) => {
                    const msg = (err.response && err.response.data && err.response.data.msg) || 'Erro ao salvar.';
                    mostraErro('', msg);
                })
                .finally(() => {
                    this.salvando = false;
                });
        },
        excluir(campo) {
            $swal({
                title: 'Excluir?',
                text: `Excluir o campo "${campo.label}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, excluir'
            }).then((result) => {
                if (result && result.value) this.executarExcluir(campo.id);
            });
        },
        executarExcluir(id) {
            axios.delete(`${URL_ADMIN}/planejamento/requisicao-vaga/campos-custom/${id}`)
                .then(() => {
                    mostraSucesso('', 'Campo excluído.');
                    this.listar();
                })
                .catch(() => {
                    mostraErro('', 'Erro ao excluir.');
                });
        }
    }
});
