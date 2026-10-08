# E-mail — plus addressing

## Sintoma
`usuario+tag@dominio.com` era rejeitado como "E-mail inválido".

## Causa
Regex local `[\w-]+` (sem `+`) em:
- `resources/js/funcoes.js` (`validaEmail` / `validaEmailVazio`)
- `resources/js/mixins/Validacoes.js`
- `Sistema::validaEmail`

## Fix
Parte local: `[\w+-]+(?:\.[\w+-]+)*` — mantém o restante do domínio igual.

## Nota
`testaEmail()` e helpers com `/^[^\s@]+@.../` já aceitavam `+`.
