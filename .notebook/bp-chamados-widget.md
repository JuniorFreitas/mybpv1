# BP Chamados widget

Integração do widget portal de chamados em página dedicada do MyBP.

## Flow

1. Menu **CHAMADOS** (visível se `BpChamadosWidgetTokenService::isEnabled()`)
2. Página `g/bp-chamados` → `resources/views/g/bp-chamados/index.blade.php`
3. CDN: `{api_base_url}/vendor/bp-chamados/bp-tickets.js`
4. Front chama `GET g/bp-chamados/widget-token` (sessão) → JWT HS256
5. `BpTickets.mount('#bp-support')` + renew no `token-expired`

## Backend

- Config: `config/services.php` → `bp_chamados`
- Mint: `app/Services/BpChamados/BpChamadosWidgetTokenService.php`
- Página: `BpChamadosController@index` / rota `g.bp-chamados.index`
- Token: `BpChamadosWidgetTokenController` / rota `bp-chamados.widget-token`
- Claims: `iss`=application_id, `sub`=user.id, `name`=nome, `email`=login, `company`={id,name,alias} da `Empresa`, `iat`/`exp`

## Gotcha

- **Não** usar path `/api/widget-token`: o catch-all `api/{empresa_slug}` (VagaAberta) engole a rota.
- Token path: `/g/bp-chamados/widget-token`
- Secret `ws_…` só no `.env` (nunca commit)
- Widget **não** fica no layout global — só na página dedicada
- Página **precisa** de `Vue.createApp` + `registerGlobals` + `mount('#app')` (como dashboard), senão `[v-cloak]` fica no spinner infinito
- Portal API (tickets) deve enviar `Cross-Origin-Resource-Policy: cross-origin` nas rotas do widget

## Local

- Application MyBP: `01M24DYWKX5XKEDHBNWJ6QFZFX`
- API: `http://localhost:8100`
