# MFA Login Web

Entry: `Auth\LoginController` + `Services\Auth\MfaLoginService`

Fluxo: senha OK → se `cliente_configs.mfa_login_habilitado` → challenge em sessão (`mfa_login_pending`) sem `Auth::login` → código 8 chars (SHA-256 no cache, TTL 10 min) → e-mail (`users.login`) e/ou WhatsApp (`usuarios_telefone`) → rotas `login.mfa.*`.

Config por empresa: `mfa_login_habilitado`, `mfa_login_email`, `mfa_login_whatsapp` (UI em cadastro de clientes). WhatsApp MFA exige `envia_whatsapp`; gate especial `tipo=mfa_login` em `ZapNotificacao` / `WhatsappNotificationGateService::podeEnviarMfaLogin()`.

Gotcha: não carregar `User::Empresa` no challenge — `Cliente` tem global scope que usa `auth()->user()`. Usar `Cliente::withoutGlobalScopes()`.

Updated: 2026-10-08
