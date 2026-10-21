import { posicionarMenuAcoes } from '../utils/posicionarMenuAcoes'
import { periodoBrDeIso } from '../utils/periodoEntrevista'

const UFS = [
    'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
    'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
    'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
]

function preenchido(valor) {
    return valor !== '' && valor !== null && valor !== undefined
}

export default {
    data() {
        return {
            filtrosAvancadosAbertos: false,
            dropdownAbertoId: null
        }
    },
    computed: {
        opcoesUfFiltro() {
            return [{ value: '', label: 'Todos os estados' }, ...UFS.map((uf) => ({ value: uf, label: uf }))]
        },
        opcoesPagesFiltro() {
            return [
                { value: 20, label: '20' },
                { value: 50, label: '50' },
                { value: 100, label: '100' }
            ]
        },
        opcoesRotaBooleana() {
            return [
                { value: '', label: 'Geral' },
                { value: true, label: 'Tem Rota' },
                { value: false, label: 'Não tem Rota' }
            ]
        },
        opcoesRotaParecer() {
            return [
                { value: '', label: 'Geral' },
                { value: 'sem_parecer', label: 'Sem parecer' },
                { value: 'sim', label: 'Tem Rota' },
                { value: 'não', label: 'Não tem Rota' }
            ]
        },
        opcoesNotaFaixa() {
            return [
                { value: '', label: 'Sem filtro' },
                { value: '0', label: '0' },
                { value: '1-5', label: '1 à 5' },
                { value: '5-7', label: '5 à 7' },
                { value: '8-10', label: '8 à 10' }
            ]
        },
        opcoesNotaRhUmADez() {
            const notas = [
                { value: '', label: 'Geral' },
                { value: 'realizado', label: 'Realizado' }
            ]
            for (let i = 1; i <= 10; i++) {
                notas.push({ value: String(i), label: `Nota ${i}` })
            }
            return notas
        },
        opcoesFinalRh() {
            return [
                { value: '', label: 'Todos' },
                { value: 'favoravel', label: 'Favorável' },
                { value: 'restricao', label: 'Restrição' },
                { value: 'desfavoravel', label: 'Desfavorável' }
            ]
        },
        opcoesTecnicaFiltro() {
            return [
                { value: '', label: 'Geral' },
                { value: 'realizado', label: 'Realizado' }
            ]
        },
        opcoesTesteFiltro() {
            return [
                { value: '', label: 'Geral' },
                { value: 'realizado', label: 'Realizado' },
                { value: '0', label: 'Não se Aplica' }
            ]
        },
        opcoesClassIndividual() {
            return [
                { value: '', label: 'Sem filtro' },
                { value: 'favoravel', label: 'Favorável' },
                { value: 'destaque', label: 'Destaque' }
            ]
        },
        opcoesClassRh() {
            return [
                { value: '', label: 'Sem filtro' },
                { value: 'entrevistado', label: 'Entrevistados' },
                { value: 'nao_entrevistado', label: 'Não Entrevistados' },
                { value: 'favoravel', label: 'Favorável' },
                { value: 'destaque', label: 'Destaque' },
                { value: 'stand_by', label: 'Stand By' },
                { value: 'desfavoravel', label: 'Desfavorável' }
            ]
        },
        temFiltrosEntrevista() {
            const d = this.controle?.dados || {}
            const chaves = [
                'campoBusca', 'campoCPF', 'campoVaga', 'campoUf', 'campoRh', 'campoFinalRh',
                'campoTecnica', 'campoTeste', 'parecer_individual', 'entrevista_rh', 'entrevista_rh_nota'
            ]
            if (chaves.some((chave) => preenchido(d[chave]))) {
                return true
            }
            if (d.campoRota === true || d.campoRota === false || (typeof d.campoRota === 'string' && d.campoRota !== '')) {
                return true
            }
            return !!d.filtroPeriodo
        },
        totalFiltrosAvancadosEntrevista() {
            const d = this.controle?.dados || {}
            const chaves = [
                'campoUf', 'campoRh', 'campoFinalRh', 'campoTecnica', 'campoTeste',
                'parecer_individual', 'entrevista_rh', 'entrevista_rh_nota'
            ]
            let total = chaves.filter((chave) => preenchido(d[chave])).length
            if (d.campoRota === true || d.campoRota === false || (typeof d.campoRota === 'string' && d.campoRota !== '')) {
                total += 1
            }
            if (preenchido(d.pages) && Number(d.pages) !== 20) {
                total += 1
            }
            return total
        }
    },
    mounted() {
        this._fecharMenuEntrevista = (event) => {
            if (event?.target?.closest?.('.dropdown')) {
                return
            }
            if (typeof this.fecharDropdown === 'function') {
                this.fecharDropdown()
            }
        }
        document.addEventListener('click', this._fecharMenuEntrevista)
    },
    beforeUnmount() {
        document.removeEventListener('click', this._fecharMenuEntrevista)
    },
    methods: {
        posicionarMenuAcoes,
        onPeriodoEntrevista() {
            const dados = this.controle.dados
            const periodo = dados.filtroPeriodo ? periodoBrDeIso(dados.dataInicio, dados.dataFim) : ''
            if (dados.filtroPeriodo && !periodo) {
                return
            }
            dados.periodo = periodo
            this.atualizar()
        },
        limparFiltrosEntrevista() {
            const dados = this.controle.dados
            dados.campoBusca = ''
            dados.campoCPF = ''
            dados.campoVaga = ''
            dados.autocomplete_label = ''
            dados.autocomplete_label_anterior = ''
            dados.campoUf = ''
            dados.campoRh = ''
            dados.campoFinalRh = ''
            dados.campoRota = ''
            dados.campoTecnica = ''
            dados.campoTeste = ''
            dados.parecer_individual = ''
            dados.entrevista_rh = ''
            dados.entrevista_rh_nota = ''
            dados.filtroPeriodo = false
            dados.dataInicio = ''
            dados.dataFim = ''
            dados.periodo = ''
            this.filtrosAvancadosAbertos = false
            this.atualizar()
        },
        fecharCombosEntrevista(excetoId) {
            Object.keys(this.$refs).forEach((key) => {
                if (!key.startsWith('comboFiltro')) {
                    return
                }
                const ref = this.$refs[key]
                if (!ref || ref.inputId === excetoId || typeof ref.fechar !== 'function') {
                    return
                }
                ref.fechar()
            })
        },
        toggleDropdown(itemId) {
            this.dropdownAbertoId = this.dropdownAbertoId === itemId ? null : itemId
        },
        isDropdownOpen(itemId) {
            return this.dropdownAbertoId === itemId
        },
        fecharDropdown() {
            this.dropdownAbertoId = null
        }
    }
}
