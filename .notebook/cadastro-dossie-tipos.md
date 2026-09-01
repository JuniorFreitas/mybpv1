# Cadastro tipos de dossiê
> Catálogo `dossie_tipos` com override por empresa

Entry: `app/Http/Controllers/DossieTipoController.php`
UI: `resources/js/components/cadastros/dossietipos/DossieTipos.vue` (`<script setup>`)
Domain: `app/Services/Dossie/DossieTipoCadastroService.php`

Flow: menu Cadastro → `/g/cadastro/dossietipos` → listagem mescla global + override da empresa (`DossieTipo::ativosOrdenados()` no dossiê do colaborador).

Regras:
- Cliente nunca altera linha global (`empresa_id` null)
- Edit/ativa-desativa de global → replica com `empresa_id` do usuário
- `tipo` e `chave` gerados do nome (`StudlyCase` / `snake_case`); cadastro recusa duplicado no catálogo efetivo
- Permissões: `cadastro_dossie_tipos`, `_insert`, `_update` (seeder; papel 1 ganha todas)
- `permite_assinatura` só vale se `Sistema::assinaturaDigitalHabilitada(empresa)` (`cliente_configs.assinatura_digital_habilitada`)
- Modelo: PDF em `modelo_arquivo_id` (override) ou Blade `pdf.historico.dossie.{tipo_modelo}`

Updated: 2026-08-31
