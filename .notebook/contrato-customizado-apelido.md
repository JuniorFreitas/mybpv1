# Contrato customizado por apelido
> DossiePdfService resolve Blade por `cliente.apelido`

Entry: `app/Services/Dossie/DossiePdfService.php:resolverView()`

Flow: dossiê colaborador → `tipo_modelo` → tenta customizado:

- contrato: `pdf.historico.dossie.customizado.{apelido}.contratos.contratotrabalhoassinado`
- demais (ex. checklist): `pdf.historico.dossie.customizado.{apelido}.{tipo_modelo}`
- senão: default / `pdf.historico.dossie.{tipo_modelo}`

Clientes:
- `embralote` → contrato
- `coimbraalves` (empresa `111969`) → contrato + `docchecklist` (FR.RH.03.00)
- `iluminar` (empresa `57861`) → override `PlanoSaudeAssinado` com modelo `declaracaocienciaplanosaude` (seeder `DossieTipoIluminarPlanoSaudeSeeder`)

Gotcha: pasta/view deve bater exatamente com `clientes.apelido`. Carta oferta ainda usa PDF estático S3 `checklist_{apelido}.pdf` (`CartaOferta::checklistArquivo`). Docs Iluminar de plano de saúde usam path global `pdf.historico.dossie.{tipo_modelo}` + linha em `dossie_tipos` com `empresa_id`.

Updated: 2026-09-24
