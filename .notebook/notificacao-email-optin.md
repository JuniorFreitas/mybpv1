# Notificação e-mail opt-in

Entry: `User::scopeParaNotificacaoEmail()`

Check do usuário: pivot `user_recebe_email.ativo` + `tipo_recebe_email.nome` (`TipoRecebeEmail::LISTA_TIPOS`).

Quem recebe: `users.ativo` + `deleted_at` null + check do tipo + login fora de `mail.suppress_recipients` / `Sistema::EMAILPADRAO`.

Remetente `sistema@`: `SuppressConfiguredMailRecipients` troca From por `naoresponda@mybp.com.br`. To/Cc/Bcc desse endereço são removidos.

Canais:
- Avaliação 90: `mybp:avaliacao-experiencia` (`Aval90dias`) + diário `AvaliacaoNoventaVencimentoJob`. Antes o comando ia para `scopeUsuariosPrivilegioRh()`, sem o check.
- ASO: `VencimentoAsoJob` → `JobMailVencimentoAso` (revalida na fila)
- Férias: `VerificaVencimentoFeriasJob` (mensal). Saída usa o mesmo check: `VerificaSaidaFeriasJob`
- Treinamento: `TreinamentoVencimento::buscarUsuariosEmail()`

Contexto de consulta (não é destinatário): `User::usuarioContextoEmpresa()`

Updated: 2026-09-27
