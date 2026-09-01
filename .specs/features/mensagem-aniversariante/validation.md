# Mensagem de Aniversariante — Validation

**Status:** PARTIAL PASS (domínio coberto; tela/assunto sem teste HTTP)
**Date:** 2026-08-31
**Diff:** working tree `feature/mensagem-aniversariante` (sem commit — regra do usuário)
**Gate:** `php vendor/bin/phpunit tests/Unit --filter Aniversariante` → 10 tests, 13 assertions, OK

---

## Spec-anchored ACs

| Criterion | Spec outcome | Evidence | Result |
| --- | --- | --- | --- |
| ANIV-01 editor carrega salvo ou padrão | HTML padrão ou custom | Resolver: `AniversarianteMensagemResolverTest.php:40` `assertSame(MENSAGEM_PADRAO)`; `L74` HTML custom. Tela sem teste HTTP | ⚠️ UI gap |
| ANIV-02 persistir por empresa | 1 HTML por empresa_id | `AniversarianteMensagemResolverTest.php:93-94` empresas 1 e 2 distintas | ✅ |
| ANIV-03 preview placeholders + sanitização | `{{nome}}`/`{{empresa}}` substituídos; script removido | `AniversarianteMensagemRendererTest.php:20` assertSame Maria/ACME; `L30` script removido; `L40` placeholder vazio | ✅ |
| ANIV-04 envio usa custom | HTML da empresa no corpo | `AniversarianteMensagemResolverTest.php:127` `assertSame('<p>Oi Ana da Beta</p>')` | ✅ pipeline; ⚠️ Mail HTTP não testado |
| ANIV-05 fallback padrão | texto padrão + nome | `AniversarianteMensagemResolverTest.php:34-37` padrão; `L108-110` Maria Silva + Muitos parabéns | ✅ |
| ANIV-06 assunto inalterado | `{empresa} - FELIZ ANIVERSÁRIO` | `AniversariantesMail.php:30` — sem assertion | ⚠️ Spec-precision gap |

## Edge cases

| Case | Evidence | Result |
| --- | --- | --- |
| HTML vazio no save recusa | controller 422; frontend strip tags — sem teste HTTP | ⚠️ |
| Placeholder desconhecido → vazio | RendererTest L40 | ✅ |
| `<script>` removido | RendererTest L30 | ✅ |
| Job multi-empresa sem scope | resolver `withoutGlobalScopes` + teste empresas 1 vs 2 | ✅ |

## Discrimination sensor (mental, sem mutar working tree)

Mutante “sempre retornar MENSAGEM_PADRAO” seria morto por `testRetornaHtmlDaEmpresa` e `testPipelineUsaHtmlCustomizadoDaEmpresa`.

## Gaps

1. Sem Feature test da tela/rotas (auth/habilidades)
2. Assunto do Mailable sem assertion
3. UAT no browser não executado (precisa login)

## Veredito

Domínio (resolver/renderer/fallback/sanitização) está coberto. Tela e assunto dependem de conferência manual.
