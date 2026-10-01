# Admissão em processo — filtros compactos

- UI: `resources/views/g/admissao/processo/index.blade.php` + `resources/js/g/admissao/processo/app.js`
- `FiltroListagem` + `mybp-filtros-compactos`
- Primários (todos visíveis): períodos, Lotação, CC, **Colaborador/CPF** (máscara como movimentações), Cargo, UF, Status, Tipo, Demitido, Exibir
- Busca unificada: `campoBusca` ou `campoCPF` via `onInputBuscaUnificada` / `parecePadraoCpf` / `formatarCpfDigitos`
- `DateRangeFilter` com `@change` → `atualizar`
- Gotcha Blade: tags Vue custom **não** podem ser self-closing (`/>`); usar `</date-range-filter>` / `</combobox-auto-complete>` senão o HTML engole os campos seguintes.
