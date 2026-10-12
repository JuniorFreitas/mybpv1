# Design — Carta Oferta interna

## Components

| Component | Responsibility |
| --- | --- |
| `CartaOfertaContextoBuilder` | Monta array de placeholders (fake ou a partir da carta) |
| `CartaOfertaInternaService` | Cria carta MyBPIN + dispara envio (transaction) |
| `CartaOfertaTemplate::htmlPadrao()` | HTML padrão compartilhado com seeder |
| `CartaOfertaGerencialController@criar` | Endpoint autenticado |
| `JobProcessarEnvioAssinatura` | Usa ContextoBuilder no PDF |
| Front listagem | FiltroListagem + cards + modal Nova carta |
| Front template | Placeholders clicáveis, preview, restaurar padrão |

## Data

Migration em `curriculo_carta_oferta`: `setor` (string null), `salario` (string null), `data_inicio` (date null).

## Flow

1. RH → POST criar → Internaservice → Carta Pendente Anexo + local MyBPIN
2. Se assinatura ON → Job assinatura (PDF template)
3. Se OFF → JobEnvioCartaOferta (link público)
4. Candidato assina/anexa → Aguardando RH
5. RH responde → se MyBPIN, só status + notificações
