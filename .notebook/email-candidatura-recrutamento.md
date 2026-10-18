# E-mail de candidatura (recrutamento)

Entry: `JobRecrutamento` → `RecrutamentoMail` → `email.recrutamento.cadastro`

Disparo: portal/API de vagas abertas após salvar currículo (`VagaAbertaController` / `IntegracaoVagaAbertaController`).

Payload do job: `nome`, `email`, `empresa_id`, `vaga_aberta_id`.

Mailable resolve `Cliente` + `VagasAbertas.Municipio`, monta assunto `Candidatura recebida — {titulo}` e From com `razao_social`.

Template usa `layouts.mail.layout` (mesmo padrão de recupera-senha / desclassificação). Sem branch por empresa.

Updated: 2026-10-17
