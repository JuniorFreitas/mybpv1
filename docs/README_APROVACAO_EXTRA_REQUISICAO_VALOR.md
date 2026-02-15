# 📖 Aprovação Extra - Requisição de Vaga e Valor Extra

Sistema de aprovação extra implementado para **Requisição de Vaga** e **Valor Extra Prevista** no MyBP.

---

## 🚀 Início Rápido

### 1. Executar Migrations

```bash
docker compose exec mybpdp php artisan migrate
```

### 2. Configurar no Banco

Execute o script SQL para criar as configurações:

```bash
# Edite o arquivo e ajuste IDs da empresa e usuários
vim docs/SQL_APROVACAO_EXTRA_REQUISICAO_VALOR_EXTRA.sql

# Execute no MySQL
```

### 3. Limpar Cache

```bash
docker compose exec mybpdp php artisan config:clear
docker compose exec mybpdp php artisan cache:clear
docker compose exec mybpdp php artisan route:clear
```

### 4. Testar

```bash
# Executar script de validação
docker compose exec mybpdp php artisan tinker < docs/TESTE_APROVACAO_EXTRA_REQUISICAO_VALOR.php
```

---

## 📚 Documentação Completa

### Documentos Principais

| Documento | Descrição |
|-----------|-----------|
| [IMPLEMENTACAO_APROVACAO_EXTRA_REQUISICAO_VALOR_EXTRA.md](./IMPLEMENTACAO_APROVACAO_EXTRA_REQUISICAO_VALOR_EXTRA.md) | 📋 Documentação completa da implementação |
| [GUIA_TESTE_APROVACAO_EXTRA_REQUISICAO_VALOR.md](./GUIA_TESTE_APROVACAO_EXTRA_REQUISICAO_VALOR.md) | 🧪 Guia detalhado de testes |

### Scripts e Exemplos

| Arquivo | Descrição |
|---------|-----------|
| [SQL_APROVACAO_EXTRA_REQUISICAO_VALOR_EXTRA.sql](./SQL_APROVACAO_EXTRA_REQUISICAO_VALOR_EXTRA.sql) | 💾 Script SQL de configuração |
| [TESTE_APROVACAO_EXTRA_REQUISICAO_VALOR.php](./TESTE_APROVACAO_EXTRA_REQUISICAO_VALOR.php) | ✅ Script PHP de validação |
| [EXEMPLO_COMPONENTE_VUE_APROVACAO_EXTRA_REQUISICAO_VALOR.vue](./EXEMPLO_COMPONENTE_VUE_APROVACAO_EXTRA_REQUISICAO_VALOR.vue) | 🎨 Exemplo de componente Vue |

---

## 🔄 Fluxos de Aprovação

### Requisição de Vaga
```
📝 Solicitação → 👤 Gestor → [⭐ Aprovação Extra] → ✅ Concluído
```

### Valor Extra
```
📝 Solicitação → 👤 Gestor → [⭐ Aprovação Extra] → 👥 RH → ✅ Concluído
```

> **[⭐ Aprovação Extra]** = Etapa opcional configurável

---

## 🎯 Endpoints da API

### Requisição de Vaga

| Método | Rota | Descrição |
|--------|------|-----------|
| POST | `/planejamento/requisicao-vaga` | Criar solicitação |
| POST | `/planejamento/requisicao-vaga/atualizar` | Listar solicitações |
| PUT | `/planejamento/requisicao-vaga/{id}/aprovar` | Aprovar como gestor |
| PUT | `/planejamento/requisicao-vaga/{id}/aprovarextra` | ⭐ **Aprovar como extra** |

### Valor Extra

| Método | Rota | Descrição |
|--------|------|-----------|
| POST | `/planejamento/movimentacao/valor-extra-prevista` | Criar solicitação |
| POST | `/planejamento/movimentacao/valor-extra-prevista/atualizar` | Listar solicitações |
| PUT | `/planejamento/movimentacao/valor-extra-prevista/{id}/aprovar` | Aprovar como gestor |
| PUT | `/planejamento/movimentacao/valor-extra-prevista/{id}/aprovarextra` | ⭐ **Aprovar como extra** |
| PUT | `/planejamento/movimentacao/valor-extra-prevista/{id}/aprovarrh` | Aprovar como RH |

---

## ⚙️ Configuração

### Estrutura da Configuração

```php
[
    'empresa_id' => 1,
    'tipo_processo' => 'requisicao_vaga', // ou 'valor_extra'
    'nome_aprovacao' => 'Gerência', // Nome personalizado
    'usuarios_autorizados' => [2, 3, 4], // IDs dos usuários
    'ativo' => true
]
```

### Quem Pode Aprovar?

✅ Usuários em `usuarios_autorizados`
✅ Usuários com `privilegio_gestao_rh`
✅ Usuários com `privilegio_aprovar_por_rh`

---

## 🧪 Testes Rápidos

### 1. Validar Implementação

```bash
docker compose exec mybpdp php artisan tinker < docs/TESTE_APROVACAO_EXTRA_REQUISICAO_VALOR.php
```

### 2. Verificar Configuração

```sql
SELECT * FROM aprovacao_extra_configs
WHERE tipo_processo IN ('requisicao_vaga', 'valor_extra');
```

### 3. Testar API (exemplo com curl)

```bash
# Criar requisição
curl -X POST http://localhost:8000/planejamento/requisicao-vaga \
  -H "Content-Type: application/json" \
  -d '{...}'

# Aprovar como extra
curl -X PUT http://localhost:8000/planejamento/requisicao-vaga/1/aprovarextra \
  -H "Content-Type: application/json" \
  -d '{"status_aprovacao_extra":"aprovado","obs_aprovacao_extra":"OK"}'
```

---

## 📊 Resposta da API

Ao chamar o endpoint de listagem (`/atualizar`), o backend retorna:

```json
{
    "dados": {
        "itens": [...],
        "pode_aprovar_extra": true,
        "tem_aprovacao_extra": true,
        "nome_aprovacao_extra": "Gerência",
        "aprovar_por_gestor": true,
        "aprovar_por_rh": true
    }
}
```

Use essas flags para exibir/ocultar botões no frontend.

---

## 🎨 Frontend (Vue)

Veja o exemplo completo em: [EXEMPLO_COMPONENTE_VUE_APROVACAO_EXTRA_REQUISICAO_VALOR.vue](./EXEMPLO_COMPONENTE_VUE_APROVACAO_EXTRA_REQUISICAO_VALOR.vue)

### Snippet Básico

```vue
<b-button
    v-if="temAprovacaoExtra && podeAprovarExtra"
    @click="abrirModalAprovacaoExtra(item)"
>
    Aprovar - {{ nomeAprovacaoExtra }}
</b-button>
```

---

## 🗂️ Estrutura de Arquivos

```
app/
├── Models/
│   ├── RequisicaoVaga.php          [atualizado]
│   ├── ValorExtraPrevista.php      [atualizado]
│   └── AprovacaoExtraConfig.php    [atualizado]
├── Http/Controllers/
│   ├── RequisicaoVagaController.php         [atualizado]
│   └── ValorExtraPrevistaController.php     [atualizado]

database/migrations/
├── 2026_02_07_000001_add_aprovacao_extra_to_requisicao_vagas_table.php
└── 2026_02_07_000002_add_aprovacao_extra_to_valor_extra_previstas_table.php

routes/
└── web.php                         [atualizado]

docs/
├── IMPLEMENTACAO_APROVACAO_EXTRA_REQUISICAO_VALOR_EXTRA.md
├── GUIA_TESTE_APROVACAO_EXTRA_REQUISICAO_VALOR.md
├── SQL_APROVACAO_EXTRA_REQUISICAO_VALOR_EXTRA.sql
├── TESTE_APROVACAO_EXTRA_REQUISICAO_VALOR.php
├── EXEMPLO_COMPONENTE_VUE_APROVACAO_EXTRA_REQUISICAO_VALOR.vue
└── README_APROVACAO_EXTRA_REQUISICAO_VALOR.md  [este arquivo]
```

---

## ✅ Checklist de Implementação

- [x] Models atualizados com colunas
- [x] Migrations criadas e executadas
- [x] Relacionamentos adicionados
- [x] Controllers com método `aprovarExtra()`
- [x] Rotas criadas
- [x] Eager loading configurado
- [x] Configuração retornada para frontend
- [x] Documentação completa
- [x] Scripts de teste
- [x] Exemplo Vue
- [ ] Frontend implementado (pendente)
- [ ] Testes E2E (pendente)

---

## 🐛 Troubleshooting

### Erro 403 ao aprovar

**Causa:** Usuário sem permissão

**Solução:** Verificar `usuarios_autorizados` ou privilégios

```sql
SELECT usuarios_autorizados FROM aprovacao_extra_configs
WHERE tipo_processo = 'requisicao_vaga';
```

### Config não aparece

**Causa:** Cache não limpo

**Solução:**
```bash
docker compose exec mybpdp php artisan config:clear
```

### Colunas não existem

**Causa:** Migrations não executadas

**Solução:**
```bash
docker compose exec mybpdp php artisan migrate
```

---

## 📞 Suporte

Para dúvidas ou problemas:

1. Consulte a [documentação completa](./IMPLEMENTACAO_APROVACAO_EXTRA_REQUISICAO_VALOR_EXTRA.md)
2. Execute o [script de validação](./TESTE_APROVACAO_EXTRA_REQUISICAO_VALOR.php)
3. Siga o [guia de testes](./GUIA_TESTE_APROVACAO_EXTRA_REQUISICAO_VALOR.md)

---

## 🎉 Status

✅ **Backend 100% Implementado e Funcional**

O sistema de aprovação extra está completo e pronto para uso nos processos de Requisição de Vaga e Valor Extra!

---

**Última atualização:** 07/02/2026
**Versão:** 1.0
**Autor:** Sistema MyBP
