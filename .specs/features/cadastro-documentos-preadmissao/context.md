# Cadastro de Documentos da Pré-admissão — Context

**Gathered:** 2026-08-31
**Spec:** `.specs/features/cadastro-documentos-preadmissao/spec.md`
**Status:** Implemented and independently validated

---

## Feature Boundary

Tela de cadastro no padrão Tipos de Dossiê para o cliente administrar **documentos** da pré-admissão da própria empresa (`documentos_curriculos_adm_empresa`), com categoria como campo/filtro. Categorias novas nascem no modal. Sem exclusão física, sem editar `tipo` depois de criado.

---

## Implementation Decisions

### Escopo da tela

- Uma listagem de documentos (não duas telas, não abas)
- Categoria é filtro na listagem e campo no modal
- Criar categoria nova no próprio modal, se precisar

### CRUD

- Cadastrar, editar, ativar/desativar
- Sem exclusão física

### Formulário

- Nome, descrição (TinyMCE), categoria, ordem, ativo
- Todas as flags de `configuracoes`: obrigatório, apenas imagem, apenas PDF, apenas PDF/imagem, múltiplos, min/max, só gestão (`sogestao`)
- Referência visual: `DossieTipos.vue` (FiltroListagem, cards `mybp-*`, modal, `bt-ativo`)

### Identificador `tipo`

- Gerado do nome (`snake_case`) na criação
- Não editável depois (anexos do candidato usam `tipo`)

### Descrição

- Editor rico TinyMCE self-host (`tiny-mce-editor`, preset `simples`)
- HTML com links (já existe no seeder, ex. antecedente criminal)

### Agent's Discretion

- Permissão `cadastro_documentos_preadmissao` + `_insert` + `_update`
- Menu Cadastro > Documentos da Pré-admissão
- `foto3x4` é padrão do sistema: sem edição/inativação na UI; `garantirPadraoSistema()` cria ou reativa por empresa
- Isolar sempre por `empresa_id` do usuário
- Invalidar cache `docAdmEmpresa_{empresa_id}` nas mutações; corrigir o getter que dá forget a cada leitura
- Combobox de categorias da empresa + ação “Nova categoria” (campo de rótulo) no modal
- Flags de tipo de arquivo mutuamente exclusivas

### Declined / Undiscussed Gray Areas → Assumptions

- Permissão, menu, `metodo` oculto, isolamento multi-tenant e cache — defaults acima, registrados na spec como não confirmados pelo cliente (padrão do projeto / DossieTipos)

---

## Specific References

- “baseie no DossieTipos”
- Tabelas: `documentos_curriculos_cat_adm_empresa`, `documentos_curriculos_adm_empresa`
- UI do candidato: `resources/js/components/documento/Documento.vue` lê `label`, `descricao`, `tipo`, `configuracoes`

---

## Deferred Ideas

None — discussion stayed within feature scope
