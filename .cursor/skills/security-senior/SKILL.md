---
name: security-senior
description: Especialista em segurança para hardening de aplicações web, prevenção de SQL injection, brute force, rate limiting, segurança de Nginx e performance segura.
---

# Security Senior

Use esta skill para revisar e implementar melhorias de segurança em aplicações web sem degradar confiabilidade e performance.

## Prioridades

- Bloquear vetores críticos: SQL injection, brute force, enumeração de usuários, upload inseguro e exposição de headers.
- Aplicar controle de taxa por contexto (usuário, IP, endpoint sensível) com limites realistas.
- Endurecer autenticação e sessão preservando UX.
- Ajustar Nginx com headers de segurança, políticas de acesso e compressão/caching eficientes.
- Preferir mudanças cirúrgicas, aderentes aos padrões existentes do repositó/rio.

## Checklist de Segurança

- Validar e normalizar input em todas as fronteiras HTTP.
- Evitar SQL dinâmico não parametrizado (`raw`, concatenação em query).
- Escapar padrões de busca (`LIKE`) para evitar wildcard abuse.
- Adotar proteção anti brute force no login (limite + lockout temporário).
- Garantir mensagens de autenticação que não facilitem enumeração.
- Revisar headers HTTP de segurança e exposição de informações de servidor.
- Validar tamanho/tipo de upload e regras de acesso por perfil/tenant.

## Checklist de Performance Segura

- Usar paginação e filtros para evitar full scans desnecessários.
- Manter compressão HTTP (gzip) para assets textuais.
- Configurar cache de estáticos no edge (Nginx) com TTL apropriado.
- Evitar validações ou middlewares pesados em rotas de alto volume sem necessidade.

## Saída Esperada

- Relatar riscos encontrados por severidade.
- Implementar correções completas com impacto mínimo.
- Descrever mudanças em autenticação, consultas e proxy web.
- Confirmar que os checks existentes do projeto continuam verdes.
