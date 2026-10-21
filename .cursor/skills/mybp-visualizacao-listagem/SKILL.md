---
name: mybp-visualizacao-listagem
description: Aplica o padrão MyBP de visualização lista (cards) e tabela na mesma tela, com tabela como padrão e uma preferência só no localStorage para todo o sistema. Use quando o usuário pedir lista e tabela, alternar visualização, card ou tabela, mybp-visualizacao-listagem, MybpVisualizacaoListagem, ou guardar a escolha de listagem no navegador.
---

# MyBP — Visualização lista e tabela

Duas formas de ver os mesmos registros. A **tabela é o padrão** do sistema. Em Planejamento, sem escolha salva, o padrão é **lista**. A escolha do usuário é **uma só para o sistema**, não por tela.

## Antes de editar

1. Ler a seção "Visualização lista e tabela" em `docs/PADRAO_UX_LISTAGEM_CADASTROS.md`.
2. Abrir a referência: `resources/views/g/curriculos/recrutamento/index.blade.php` e `resources/js/g/curriculos/recrutamento/app.js`.
3. Não criar outra chave de `localStorage`. Usar só `mybp_visualizacao_listagem`.

## Arquivos

| Recurso | Caminho |
|---|---|
| Botões | `resources/js/components/ui/MybpVisualizacaoListagem.vue` |
| Leitura e gravação | `resources/js/utils/visualizacaoListagem.js` |
| Registro global | `mybp-visualizacao-listagem` em `resources/js/registerGlobals.js` |
| Cards | classes `mybp-cards-lista` / `mybp-card` (`docs/PADRAO_UX_LISTAGEM_CADASTROS.md`) |
| Tabela | `table.tabela` dentro de `.table-responsive` |

O componente já está no `registerGlobals`. Não registrar de novo na tela.

## Ligar na tela

Estado inicial vem do util. Sem valor salvo, retorna `tabela`. Em Planejamento, chamar `lerVisualizacaoListagem('lista')`.

```javascript
import { lerVisualizacaoListagem } from '../../../utils/visualizacaoListagem'

data() {
    return {
        visualizacao: lerVisualizacaoListagem()
    }
}
```

Ajuste o caminho do import conforme a pasta do `app.js` ou do `.vue`.

```html
<div class="d-flex justify-content-end mb-2" v-show="!controle.carregando">
    <mybp-visualizacao-listagem v-model="visualizacao"></mybp-visualizacao-listagem>
</div>

<div class="mybp-cards-lista" v-show="!controle.carregando && lista.length > 0 && visualizacao === 'lista'">
    <!-- cards mybp-card já existentes ou os mesmos campos da tabela -->
</div>

<div class="table-responsive" v-show="!controle.carregando && lista.length > 0 && visualizacao === 'tabela'">
    <table class="tabela">
        <!-- mesmas informações dos cards -->
    </table>
</div>
```

- **Lista** = cards (`visualizacao === 'lista'`).
- **Tabela** = grade (`visualizacao === 'tabela'`).
- Os dois blocos mostram os mesmos registros, filtros e ações.
- O componente grava no `localStorage` ao clicar. A tela não chama `localStorage` direto.
- Paginação, vazio e preload ficam fora dos dois blocos, visíveis nas duas visualizações.

## Regras

- Padrão do sistema: **tabela**. Planejamento, sem escolha salva: **lista**.
- Chave única: `mybp_visualizacao_listagem`. Valores: `tabela` ou `lista`.
- Não duplicar os botões Lista/Tabela na tela.
- Não mudar regra de negócio, rota, permissão ou payload da API só para trocar a visualização.
- Se a tela já tem só cards ou só tabela, acrescentar a outra forma com os mesmos campos. Não apagar a que já existe.
- Depois de alterar `resources/js`, rodar `npm run development` para o bundle enxergar o util e o componente.

## Checklist

- [ ] `visualizacao` inicia com `lerVisualizacaoListagem()`
- [ ] Controle é `<mybp-visualizacao-listagem v-model="visualizacao">`
- [ ] Lista em `mybp-cards-lista`; tabela em `table.tabela`
- [ ] `v-show` usa `visualizacao === 'lista'` e `visualizacao === 'tabela'`
- [ ] Nenhuma chave nova de `localStorage`
- [ ] Filtros, paginação e ações iguais nas duas visualizações
