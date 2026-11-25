# STATE

## Decisions

- AD-001: Mensagem de aniversário — só corpo; assunto padrão; TinyMCE; menu Administração; permissão `administracao_aniversariantes`.

## Handoff

- Feature: cadastro-documentos-preadmissao
- Phase: DONE (Verifier PASS + QA-003 housekeeping)
- Gate: PHPUnit Feature/Unit Preadmissão; seeder habilidades no código
- Next: em cada ambiente, executar `HabilidadesTableSeeder` se o menu ainda não aparecer; UAT visual opcional
- Note: commits só se o usuário pedir

## Housekeeping residual (2026-11-23)

- Adiados por pedido humano: SEC-001 (deploy CVE), SEC-002 (rotação secret Dynamus)
