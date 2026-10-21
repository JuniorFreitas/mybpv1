import { lerVisualizacaoListagem } from '../utils/visualizacaoListagem'

export default {
    props: {
        tela: { type: String, required: true }
    },
    data() {
        return {
            visualizacao: lerVisualizacaoListagem(this.tela, 'lista')
        }
    },
    methods: {
        posicionarMenuAcoes(event) {
            const botao = event && event.currentTarget
            const menu = botao && botao.parentElement ? botao.parentElement.querySelector('.dropdown-menu') : null
            if (!botao || !menu) {
                return
            }

            const aplicar = () => {
                const rect = botao.getBoundingClientRect()
                const largura = 240
                let left = rect.right - largura
                if (left < 8) {
                    left = 8
                }
                if (left + largura > window.innerWidth - 8) {
                    left = Math.max(8, window.innerWidth - largura - 8)
                }

                const altura = menu.offsetHeight || 0
                let top = rect.bottom + 4
                if (altura && top + altura > window.innerHeight - 8) {
                    top = Math.max(8, rect.top - altura - 4)
                }

                menu.style.setProperty('position', 'fixed', 'important')
                menu.style.setProperty('top', `${Math.round(top)}px`, 'important')
                menu.style.setProperty('left', `${Math.round(left)}px`, 'important')
                menu.style.setProperty('right', 'auto', 'important')
                menu.style.setProperty('bottom', 'auto', 'important')
                menu.style.setProperty('transform', 'none', 'important')
                menu.style.setProperty('z-index', '2000', 'important')
                menu.style.setProperty('min-width', `${largura}px`, 'important')
                menu.style.setProperty('margin', '0', 'important')
            }

            aplicar()
            this.$nextTick(aplicar)
        }
    }
}
