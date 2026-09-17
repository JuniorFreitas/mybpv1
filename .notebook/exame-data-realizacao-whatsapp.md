# Exame — data realização vs envio (WhatsApp)

> Form datepicker ≠ payload enviado; ConvertEmptyStringsToNull + DataHora(null) = hoje

Entry: `resources/js/components/controle-exames/ControleExames.vue:salvarUpdate()`

## Fluxo

1. UI "DATA PARA REALIZAÇÃO" → `form.encaminhado_exame_data`
2. Save deve mandar esse valor como `encaminhamento_data` (contrato backend)
3. Backend: `ControleExameController::salvaUpdate()` — `data_encaminhamento` = agora; `data_realizacao` = data informada
4. WhatsApp/e-mail: placeholders `{{data_encaminhamento}}` / `{{data_realizacao}}` em `config/whatsapp_templates.php`

## Gotcha

- Campo vazio no request → middleware `ConvertEmptyStringsToNull` → `null`
- `MasterTag\DataHora(null)` → `atual()` (data de hoje)
- Sintoma: preview OK (usa `encaminhado_exame_data`); WhatsApp real com realização = data do envio

## Pré-admissão

- Form: `formFinalizar.encaminhado_exame_data` (`resources/views/g/admissao/preadmissao/index.blade.php`)
- Backend: `PreAdmissaoController::finalizarEncaminhar()` lê `encaminhado_exame_data ?? encaminhamento_data`

## Defesa backend

`ControleExameController` aceita ambos: `encaminhado_exame_data ?? encaminhamento_data`

Updated: 2026-09-17
