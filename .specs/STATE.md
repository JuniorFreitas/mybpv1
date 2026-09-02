# STATE

## Decisions

- AD-001: Mensagem de aniversário — só corpo; assunto padrão; TinyMCE; menu Administração; permissão `administracao_aniversariantes`.

## Handoff

- Feature: cadastro-documentos-preadmissao
- Phase: Execute done (Verifier em andamento); sem commit
- Gate: 18 testes PHPUnit OK (13 unit + 5 feature)
- Next: rodar `HabilidadesTableSeeder` para liberar o menu; UAT em Cadastro > Documentos da Pré-admissão
- Note: commits só se o usuário pedir
