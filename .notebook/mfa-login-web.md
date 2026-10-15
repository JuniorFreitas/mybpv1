# MFA Login Web

Entry: `Auth\LoginController` + `Services\Auth\MfaLoginService`

Fluxo: senha OK → se `cliente_configs.mfa_login_habilitado` → se `mfa_login_periodo_dias > 0` e cookie `mfa_device_trust` válido (user+empresa+verified_at dentro do período atual) → login direto; senão challenge em sessão (`mfa_login_pending`) sem `Auth::login` → código 8 chars (SHA-256 no cache, TTL 10 min) → e-mail (`users.login`) e/ou WhatsApp (`usuarios_telefone`) → rotas `login.mfa.*` → após código OK, grava cookie HttpOnly (Laravel EncryptCookies) com TTL = período.

Config por empresa: `mfa_login_habilitado`, `mfa_login_email`, `mfa_login_whatsapp`, `mfa_login_periodo_dias` (padrão 30 dias; 0 = a cada login; UI presets 0/30/90/custom). WhatsApp MFA exige `envia_whatsapp`; gate especial `tipo=mfa_login` em `ZapNotificacao` / `WhatsappNotificationGateService::podeEnviarMfaLogin()`.

Gotcha: não carregar `User::Empresa` no challenge — `Cliente` tem global scope que usa `auth()->user()`. Usar `Cliente::withoutGlobalScopes()`.

Gotcha teste: usar `withCookie()` (criptografia padrão do Laravel); `withUnencryptedCookie`/`disableCookieEncryption` zeram o valor no EncryptCookies.

Updated: 2026-10-15
