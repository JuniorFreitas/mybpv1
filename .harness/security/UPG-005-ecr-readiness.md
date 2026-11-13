# Próximo passo — ECR / cutover (aguardando aprovação explícita)

## Pronto localmente
- Branch: `feature/atualizacao-de-imagem` (muitas alterações **ainda não commitadas**)
- Stack: Laravel 13.42.0 + PHP 8.5.12
- Imagens locais: `mybp/php-base:8.5.12-bookworm`, `mybp/sistema:php85-local`
- Trivy: 0 HIGH/CRITICAL fixáveis
- SEC-001: mitigação efetiva no container local

## O que **não** foi feito (precisa sua ordem explícita)
1. **Commit** das mudanças na branch
2. **Push** da branch / PR
3. **Build+push ECR** (ex.: `.deploy/ci-build-push.sh homol` depois `prod`)
4. **Deploy ECS** (`.deploy/deploy-ecs.sh` / CI)

## Comandos sugeridos (só após aprovação)
```bash
# 1) Commit (mensagem a combinar)
# 2) Homologação
.deploy/ci-build-push.sh homol
# 3) Deploy ECS homol (script do projeto)
# 4) Smoke em https://qa.mybp.com.br
# 5) Só então prod
.deploy/ci-build-push.sh prod
```

## Atenção no cutover
- Workers/Horizon: restart após deploy (L13 em memória)
- Cache Redis: chaves `_v2` / payloads novos; opcional `cache:clear` controlado
- Sessões: `serialization=php` — usuários **não** são deslogados em massa
- `laravel/ui` ainda em `4.x-dev`
