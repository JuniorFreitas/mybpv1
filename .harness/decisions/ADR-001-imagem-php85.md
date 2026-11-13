# ADR-001 — Imagem própria PHP 8.5 para MyBPIN

## Status
Aprovada

## Contexto
O runtime atual depende de `juniorfreitas/laravel` sobre Debian Buster (arquivo APT) e PHP 8.2.8, incompatível com a meta de Laravel 13 + imagem sem CVEs críticas/altas. É necessário definir a estratégia da imagem base antes do upgrade de framework.

## Alternativas
1. Continuar herdando `juniorfreitas/laravel` e só atualizar tags internas
2. Imagem própria multi-stage a partir de `php:8.5.x-fpm-bookworm` (oficial), com Nginx + Supervisor
3. Alpine (`php:8.5-fpm-alpine`) para menor tamanho
4. Big-bang: nova imagem + Laravel 13 no mesmo PR

## Decisão (proposta)
Adotar a alternativa **2**, em **duas fases**: primeiro só a imagem (Laravel 12 compatível com PHP 8.5); depois upgrade Laravel 13. Pin de patch PHP (ex. 8.5.12). Manter mitigação `session.upload_progress.enabled=Off`. Meta de scan: 0 CRITICAL/HIGH (com exceções documentadas).

## Justificativa
- Imagem oficial Docker Library recebe patches de segurança com mais previsibilidade
- Buster/archive é fonte estrutural de CVEs de OS
- Fasear reduz risco de regressão em fluxos críticos de RH
- Alpine traz trade-offs com extensões (imagick/gd) e debugging

## Consequências
- Reescrever `Dockerfile`, `.deploy/scripts/start` (paths `/usr/local/etc/php` em vez de `/etc/php/8.2`)
- Publicar tags versionadas no registry escolhido
- Processo de rebump quando sair novo patch PHP
- SEC-001 permanece válida até patch oficial do CVE-2026-115560

## Aprovado por
Usuário (aceite explícito da recomendação Harness — faseada, Bookworm, 0 CRITICAL/HIGH)

## Data
2026-11-13
