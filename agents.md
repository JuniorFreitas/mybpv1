# AGENTS.md — MyBP

Guia para modelos de IA atuarem com seguranca, qualidade e performance no projeto MyBP.

## 1) Visao geral do projeto

- **Dominio**: Sistema de Gestao de RH (recrutamento, admissoes, treinamentos, avaliacoes, assinatura digital).
- **Stack**: Laravel 12 (PHP 8.2), Vue 3 + Laravel Mix, Docker, Horizon, Reverb/Pusher.
- **Objetivo dos agentes**: acelerar entregas mantendo padroes de seguranca, performance e confiabilidade.

## 2) Principios obrigatorios

- **Seguranca primeiro**: nunca expor segredos, nunca incluir `.env` ou credenciais em commits.
- **Performance como padrao**: evitar N+1, preferir eager-loading (`with`), indices quando necessario.
- **Confiabilidade**: qualquer mudanca deve preservar fluxos criticos de RH.
- **Observabilidade**: logs uteis e consistentes (sem dados sensiveis).

## 3) SOLID (obrigatorio em mudancas novas)

- **S** (Single Responsibility): classes/metodos devem ter um unico proposito.
- **O** (Open/Closed): prefira extensao via services/strategies sem modificar fluxos estaveis.
- **L** (Liskov): contratos e retornos devem ser compativeis quando substituir implementacoes.
- **I** (Interface Segregation): evitar interfaces genericas; dividir por responsabilidade.
- **D** (Dependency Inversion): usar abstracoes (services, repositories) para reduzir acoplamento.

## 4) DDD (orientacao obrigatoria em modulos novos)

- **Linguagem Ubiqua**: nomes de classes, services e campos devem refletir o vocabulario de RH usado no negocio.
- **Bounded Contexts**: separar contextos (ex.: Recrutamento, Treinamentos, Assinatura Digital) para evitar acoplamento.
- **Entidades e Value Objects**: usar entidades para identidade (ex.: Curriculo, ParecerRh) e VOs para regras imutaveis.
- **Servicos de Dominio**: regras complexas devem ir para services de dominio, nao controller.
- **Aplicacao vs Dominio**: controllers orquestram; dominio executa regras.

## 5) Eloquent vs Query Builder (performance primeiro)

- **Use Eloquent** quando precisar de relacoes, mutators, casts e regras de dominio.
- **Use Query Builder** quando precisar de alto volume, agregacoes pesadas, filtros complexos ou payload enxuto.
- **Regra pratica**: se a consulta envolve grandes tabelas, filtros combinados e join multiplo, prefira Query Builder.
- **Sempre** validar se ha indice para filtros (`where`, `whereIn`, `whereBetween`).
- **Soft delete obrigatorio em Query Builder**:
    - Se a tabela tiver `deleted_at`, **sempre** aplicar `whereNull('deleted_at')`.
    - Em **joins**, aplicar `whereNull('deleted_at')` em **todas** as tabelas que possuam soft delete.

## 6) Exportacao Excel (padrao CIH obrigatorio)

- Toda exportacao deve seguir o **mesmo modelo CIH**:
    - cabecalho padronizado
    - estrutura de colunas consistente
    - sanitizacao de dados sensiveis
    - fallback para campos vazios como "Nao informado"
- **Sempre via Job**: exportacao deve rodar em fila (nunca sincrono).
- **Query padronizada**: a coleta dos dados deve respeitar o padrao CIH.
- **Protecao contra duplicidade**: usar cache para impedir reprocessamento do mesmo dado.
- Evitar variacoes de layout entre exportacoes.

## 7) E-mails (padrao obrigatorio + fila)

- Todos os e-mails devem seguir o **template/padrao oficial** do projeto.
- **Envio sempre via fila** (jobs), nunca envio sincrono direto do controller.
- Logs de envio **sem dados sensiveis**.

## 8) Cache (gestao obrigatoria)

- Todo cache deve ter **TTL definido** (nunca cache infinito).
- Ao **criar, alterar ou remover** dados que impactam cache, **invalidar/atualizar** imediatamente.
- Preferir **chaves versionadas** ou **tags** quando aplicavel.
- Evitar cache duplicado de mesma fonte sem necessidade.
- Para exportacoes e jobs, usar cache para impedir reprocessamento de dados ja consumidos.

## 9) Frontend (Vue 3 obrigatorio)

- Sempre usar **Vue 3** no frontend.
- Preferir **componentes Vue** ao inves de concentrar logica/layout direto em Blade.
- **Dividir componentes** da melhor forma possivel para manutencao e codigo mais leve.
- Em qualquer componente, considerar **performance, seguranca e otimizacao** desde o inicio.
- O objetivo e manter o frontend modular para permitir **migracao futura** sem reescrever tudo.

## 10) Banco de Dados e Models (padrao obrigatorio)

- **Singular vs plural**: usar plural em nomes de tabela e singular em nomes de model.
- Tabelas de relacionamento devem usar o **prefixo da tabela principal**, valido para **1-1, 1-N e N-N**.
    - Exemplo: tabela principal `pessoas` -> relacionamento `pessoas_telefones`.
- Em **toda model**, definir explicitamente:
    - `protected $table`
    - `protected $fillable`
    - `protected $casts`
- Campos de data devem ter **mutators** para expor formato brasileiro,
  usando um atributo com sufixo `_br` (ex.: `data_nascimento_br`).

## 11) Estrutura e areas-chave

- Backend: `app/`, `routes/`, `database/`
- Frontend: `resources/js/`, `resources/views/`
- Documentacao: `docs/`, `README.md`
- Agents internos: `agents/` (padroes e prompts por papel)

## 12) Setup local (referencia rapida)

- Docker: `docker compose up -d`
- Migrations: `docker compose exec mybpdp php artisan migrate`
- Frontend: Node >= 24 (ver `.nvmrc`)
- Build: `npm run dev` / `npm run prod`
- Logs: `docker compose exec mybpdp tail -f storage/logs/laravel.log`

## 13) Boas praticas obrigatorias (dev)

- **Eloquent**: revisar N+1 e usar `with()` quando listar relacionamentos.
- **Validacao**: usar Form Request para validacoes complexas.
- **Transaction**: operacoes criticas devem usar `DB::transaction`.
- **Erros**: tratar nulls em relacionamentos (nullsafe, defaults).
- **Uploads/Storage**: usar metodos compativeis com Flysystem v3 (`size()`).

## 14) Boas praticas obrigatorias (seguranca)

- **Nunca** logar dados sensiveis (documentos, tokens, CPF completo).
- **Sempre** sanitizar dados exportados (PDF/Excel).
- **Evitar** hardcoded secrets.
- **Cuidado** com permissoes e multi-tenant (`empresa_id`).

## 15) Boas praticas obrigatorias (performance)

- **Consultas**: evitar `->get()` sem filtros; use paginacao quando possivel.
- **Cache**: use cache para relatorios pesados.
- **Fila**: mover processamento pesado para jobs.
- **Arquivos**: evitar leituras duplicadas.

## 16) Testes (PHPUnit obrigatorio)

- **Sempre em memoria**: os testes devem rodar **apenas** com banco em memoria (SQLite `:memory:`), configurado em `phpunit.xml`.
- **Nunca** usar a base real em testes; **nunca** dar refresh/migrate na base de dados real.
- O `phpunit.xml` define `DB_CONNECTION=sqlite` e `DB_DATABASE=:memory:` para garantir isolamento; nao remover nem sobrescrever com `.env`.

## 17) Fluxo de mudancas recomendado

1. Entender o contexto no `docs/` e no modulo afetado.
2. Mapear impacto no fluxo de RH.
3. Alterar codigo minimamente, com seguranca.
4. Indicar testes necessarios (ou rodar se solicitado).

## 18) Checklist minimo antes de finalizar

- [ ] Sem segredos expostos
- [ ] Sem N+1 evidentes
- [ ] Relacoes null-safe
- [ ] Exportacao no padrao CIH
- [ ] E-mails padronizados e enviados por fila
- [ ] Cache com TTL e invalidacao correta
- [ ] Testes indicados (e rodando em memoria, nunca na base real)
- [ ] Build/Assets nao quebrados

## 19) Referencias rapidas

- Documentacao principal: `docs/README.md`
- Deploy: `docs/README-DEPLOY.md`
- Scripts de agents: `agents/scripts/README.md`
- Padroes internos: `agents/*/README.md`
- Migracao frontend (Composition API + Services): `docs/PLANO_MIGRACAO_COMPOSITION_API_SERVICES.md`, agente `agents/migracao-frontend/README.md`
