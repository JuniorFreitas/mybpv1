# Assinatura Digital - Avaliacao de Conformidade (Brasil)

## Escopo

Avaliacao tecnica do fluxo de assinatura digital implementado no MyBP, com base nas evidencias coletadas e nos requisitos praticos derivados da Lei 14.063/2020 e da MP 2.200-2/2001 (ICP-Brasil). Este documento nao substitui parecer juridico.

## Base legal considerada

-   Lei 14.063/2020: define assinatura eletronica simples, avancada e qualificada.
-   MP 2.200-2/2001: ICP-Brasil (assinatura qualificada via certificado).

## Evidencias tecnicas coletadas pelo sistema

Fontes principais:

-   `app/Services/AssinaturaDigital/AssinaturaDigitalService.php`
-   `app/Http/Controllers/AssinaturaPublicaController.php`
-   `app/Models/DocumentoParaAssinatura.php`
-   `app/Models/DocumentoAssinaturaSignatario.php`
-   `app/Models/DocumentoAssinaturaEvento.php`
-   Migrations em `database/migrations/2026_02_24_00000*_create_documento_*`

### Evidencias persistidas

-   Hash do PDF original: `documento_para_assinatura.hash_sha256`.
-   Hash de evidencia da assinatura: `documento_assinatura_signatarios.hash_evidencia`.
-   Dados do signatario: email, nome, CPF (quando informado), token.
-   Dados de contexto: IP, user agent, data_assinatura_utc, geolocalizacao.
-   Eventos registram payload completo com dados de evidencia (assinatura e recusa).
-   Consentimento: `consentimento_assinatura` e `consentimento_em` no signatario.
-   Ultimo consentimento no documento: `consentimento_ultimo_em` e `consentimento_ultimo_signatario_id`.
-   Eventos de auditoria: enviado, visualizado, assinado, recusado, download.
-   PDF com marca d'agua "ASSINADO DIGITALMENTE" ao concluir.

### Evidencias nao persistidas (observacao)

-   Codigo de verificacao e validacao ficam em cache (TTL), nao em banco.

## Fluxo tecnico resumido

1. Cria documento e calcula hash SHA-256 do PDF.
2. Cria signatarios com token unico e ordem de assinatura.
3. Link publico por token + apelido (empresa) e acesso com CPF + codigo enviado por e-mail.
4. Ao assinar: coleta IP, user agent, data UTC, geolocalizacao e calcula hash de evidencia.
5. Registra evento de assinatura e gera PDF com marca d'agua.
6. Disponibiliza pagina publica de verificacao por QR/URL.

## Avaliacao por requisito (Lei 14.063/2020)

### Assinatura eletronica simples

Requisitos tipicos:

-   Identificacao basica do signatario
-   Associacao do signatario ao documento
    Status: Atendido.
    Justificativa: token + email + CPF opcional + eventos de auditoria vinculados ao documento.

### Assinatura eletronica avancada

Requisitos tipicos:

-   Vinculacao univoca ao signatario
-   Controle do signatario sobre a assinatura
-   Deteccao de alteracoes no documento ou nos dados de assinatura
    Status: Parcialmente atendido.
    Justificativa tecnica:
-   Vinculacao: token unico por signatario + CPF validado + email.
-   Controle: fluxo de verficacao com codigo (2FA) por email.
-   Integridade: hash do PDF + hash de evidencia.
    Ponto de atencao: ausencia de carimbo de tempo de autoridade (timestamping). Esse item eleva confianca, mas nao e obrigatorio para simples/avancada em todos os casos.

### Assinatura eletronica qualificada (ICP-Brasil)

Requisitos tipicos:

-   Certificado digital ICP-Brasil e assinador com chave privada.
    Status: Nao atendido.
    Justificativa: o sistema nao utiliza certificados ICP-Brasil nem assinatura PKI.

## Conclusao tecnica

-   O fluxo atual atende criterios de assinatura eletronica simples.
-   Pode ser enquadrado como assinatura eletronica avancada para diversos casos, desde que a politica interna aceite a cadeia de evidencias coletadas.
-   Nao se enquadra como assinatura eletronica qualificada (ICP-Brasil).

## Recomendacoes (para elevar conformidade)

1. Guardar o payload completo de evidencia no evento (email, cpf, ip, user_agent, consentimento).
2. Registrar carimbo de tempo externo (TSA) quando necessario para maior robustez probatoria.
3. Garantir imutabilidade dos logs (append-only) e politica de retencao.
4. Documentar politicas internas de assinatura (aceite, auditoria, revogacao, expiração).

## Exportacao de evidencias

Endpoint para auditoria (requer permissao `administracao_documentos_legais`):

-   `GET /g/documento-assinatura/{id}/evidencias`
-   `GET /g/documento-assinatura/{id}/evidencias?download=1` (download JSON)
-   `GET /g/documento-assinatura/{id}/evidencias?format=pdf` (download PDF)

Configuracao de mascaramento (tabela `cliente_configs`):

-   `assinatura_exibir_ip_completo` (true = exibe completo; false = mascara)
-   `assinatura_exibir_cpf_completo` (true = exibe completo; false = mascara)

## Observacao final

Esta avaliacao e tecnica. Para validar juridicamente o uso em cada tipo de documento, recomenda-se revisao juridica conforme o risco e a exigencia regulatoria do processo.
