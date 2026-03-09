# 08. Integracoes

> Convenções usadas neste documento
> - Confirmado no código: integração explicitamente implementada.
> - Inferido: integração sugerida pelo código, mas sem contrato completo local.

## 1. Storage local/S3 compatível com Flysystem

### Confirmado no código

O sistema usa muitos discos nomeados por domínio funcional, com fallback local ou endpoint S3 compatível.

**Principais discos observados**

- `disco-cloud`
- `disco-exportacao`
- `disco-documento-assinatura`
- `disco-cliente`
- `disco-fornecedor`
- `disco-documentospreadmissao`
- `disco-exames`
- `disco-ponto-eletronico`
- `disco-movimentacao`

**Arquivos-base**

- `config/filesystems.php`
- `app/Models/Arquivo.php`

## 2. WhatsApp / Dynamus

### Confirmado no código

- Envio de mensagens e mídia por `ZapDynamusService`.
- Uso em recrutamento, exames, intermitente e endpoint API dedicado.
- Disparo normalmente via job `JobSendNotificacaoWhatsApp`.

**Arquivos-base**

- `app/Classes/ZapNotificacao.php`
- `app/Services/Dynamus/ZapDynamusService.php`
- `routes/api.php`
- `app/Http/Controllers/ControleExameController.php`
- `app/Http/Controllers/IntermitenteController.php`

### Riscos confirmados

- `apikey` da integração está hardcoded no service.

## 3. E-mail

### Confirmado no código

- O sistema usa `Mail` do Laravel e várias classes `Mailable`.
- Fluxos relevantes enfileiram envio em background.
- Templates HTML próprios existem para e-mails operacionais, weekly report, intermitente, assinatura e outros.

**Arquivos-base**

- `app/Mail/*`
- `resources/views/email/*`
- `app/Jobs/AssinaturaDigital/*`
- `app/Jobs/ControleExames/JobExame.php`

## 4. Broadcasting em tempo real

### Confirmado no código

- Backend suporta Reverb e Pusher.
- Frontend usa Echo e seleciona Reverb ou Pusher conforme variáveis `MIX_*`.
- Há canais privados/presence para weekly report, chat e notificações.

**Arquivos-base**

- `config/broadcasting.php`
- `resources/js/bootstrap.js`
- `routes/channels.php`
- `app/Events/WeeklyReport/*`
- `app/Events/Chat/MensagemChatEvent.php`
- `app/Events/Notificacoes/NotificacaoEvent.php`

## 5. Redis / Horizon

### Confirmado no código

- Horizon supervisiona filas com conexão Redis.
- Locks distribuídos são usados em exportações.
- Snapshot do Horizon roda a cada 5 minutos no scheduler.

**Arquivos-base**

- `config/horizon.php`
- `config/queue.php`
- `app/Console/Kernel.php`
- `app/Jobs/JobExportaCihCsvFinal.php`
- `app/Jobs/JobExportaRequisicaoVaga.php`

## 6. Google reCAPTCHA

### Confirmado no código

- O login web em ambiente não local exige `g-recaptcha-response` validado por rule própria.

**Arquivos-base**

- `app/Http/Controllers/Auth/LoginController.php`
- `app/Rules/Recaptcha.php`
- `resources/views/auth/login.blade.php`

### Riscos confirmados

- A rule usa `curl_setopt(... CURLOPT_SSL_VERIFYPEER, false)`.

## 7. Telegram

### Confirmado no código

- Há canal de log para Telegram e helper utilitário `Sistema::telegram()`.

**Arquivos-base**

- `config/logging.php`
- `app/Services/Log/LogTelegram.php`
- `app/Models/Sistema.php`

## 8. SGI

### Confirmado no código

- Existe integração para vagas/currículos e aceite de carta oferta.
- A integração escreve usuário, currículo, feedback, entrevistas, resultado integrado e admissão no MyBP.

**Arquivos-base**

- `app/Http/Controllers/Api/IntegraSgiMybpController.php`
- `app/Http/Controllers/Api/IntegracaoVagaAbertaController.php`
- `routes/api.php`

## 9. Geolocalização

### Confirmado no código

- Assinatura digital tenta obter geolocalização aproximada por IP com `ipinfo.io` e fallback `ip-api.com`.
- Ponto eletrônico usa geolocalização do navegador e Leaflet para perímetros.

**Arquivos-base**

- `app/Services/AssinaturaDigital/AssinaturaDigitalService.php`
- `resources/js/g/controle-ponto/ponto-eletronico/app.js`

## 10. Firebase Cloud Messaging

### Confirmado no código

- Há uma tela `resources/views/home.blade.php` com configuração explícita de Firebase Messaging e salvamento de token.

### Inferido

Esse trecho parece legado ou secundário, pois não apareceu como fluxo central no restante do sistema analisado.

## 11. Excel/PDF

### Confirmado no código

- Relatórios e evidências são gerados em Excel, CSV e PDF.
- Há jobs dedicados para geração e posterior notificação do usuário.

**Arquivos-base**

- `app/Jobs/JobExportaExcel.php`
- `app/Jobs/JobExportaPdf.php`
- `app/Jobs/JobExportaCihCsvFinal.php`
- `app/Jobs/JobExportaRequisicaoVaga.php`
- `app/Http/Controllers/DocumentoAssinaturaController.php`

## 12. Observabilidade e operação

### Confirmado no código

- Há comandos artisan customizados para dump, deploy, sincronizações e rotinas técnicas.
- Parte dessa operação ainda está embutida em closures de rota console.

**Arquivos-base**

- `routes/MybpCommand.php`
- `app/Console/Kernel.php`
- `app/Console/Commands/*`
