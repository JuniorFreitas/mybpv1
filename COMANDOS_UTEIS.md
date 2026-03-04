# 🚀 Comandos Úteis - Sistema de Aprovações Extras

## Docker

### Subir containers

```bash
docker compose up -d
```

### Ver logs

```bash
docker compose logs -f mybpdp
```

### Acessar container

```bash
docker compose exec mybpdp bash
```

### Parar containers

```bash
docker compose down
```

## Migrations

### Rodar migrations

```bash
docker compose exec mybpdp php artisan migrate
```

### Ver status das migrations

```bash
docker compose exec mybpdp php artisan migrate:status
```

### Rollback (caso necessário)

```bash
docker compose exec mybpdp php artisan migrate:rollback --step=4
```

## Assets (JavaScript/CSS)

Requer Node 24 para compilar assets (use o .nvmrc).

### Compilar para desenvolvimento

```bash
npm run dev
```

### Compilar para produção

```bash
npm run production
```

### Watch (recompila automaticamente)

```bash
npm run watch
```

## Cache

### Limpar cache de configuração

```bash
docker compose exec mybpdp php artisan config:clear
```

### Limpar cache de rotas

```bash
docker compose exec mybpdp php artisan route:clear
```

### Limpar cache de views

```bash
docker compose exec mybpdp php artisan view:clear
```

### Limpar todos os caches

```bash
docker compose exec mybpdp php artisan cache:clear
docker compose exec mybpdp php artisan config:clear
docker compose exec mybpdp php artisan route:clear
docker compose exec mybpdp php artisan view:clear
```

## Rotas

### Listar todas as rotas

```bash
docker compose exec mybpdp php artisan route:list | grep aprovacao-extra
```

## Permissões

### Verificar permissões no banco

```bash
docker compose exec mybpdp php artisan tinker
```

Dentro do tinker:

```php
// Ver todas as permissões
\App\Models\Permission::all();

// Criar permissão (se necessário)
\App\Models\Permission::create([
    'name' => 'administracao_aprovacao_extra_config',
    'display_name' => 'Configuração de Aprovações Extras'
]);
```

## Testes

### Testar rota específica

```bash
curl http://localhost:8000/g/administracao/aprovacao-extra-config
```

### Ver erros no log

```bash
docker compose exec mybpdp tail -f storage/logs/laravel.log
```

## Banco de Dados

### Acessar MySQL

```bash
docker compose exec mybpdp mysql -u root -p
```

### Ver tabelas criadas

```sql
USE nome_do_banco;
SHOW TABLES LIKE '%aprovacao_extra%';
DESCRIBE aprovacao_extra_configs;
SELECT * FROM aprovacao_extra_configs;
```

### Backup de tabelas específicas

```bash
docker compose exec mybpdp mysqldump -u root -p nome_do_banco \
  aprovacao_extra_configs \
  demissao_previstas \
  ferias_previstas \
  > backup_aprovacao_extra.sql
```

## Git

### Verificar status

```bash
git status
```

### Ver diferenças

```bash
git diff
```

### Adicionar arquivos

```bash
git add .
```

### Commit

```bash
git commit -m "feat: implementa sistema de aprovações extras

- Adiciona migrations para configuração de aprovações
- Cria controller e rotas para CRUD
- Implementa frontend Vue.js com multiselect
- Adiciona documentação completa
"
```

### Push

```bash
git push origin feature/aprovacao-extra
```

## Verificações Rápidas

### Verificar se o arquivo JavaScript foi compilado

```bash
ls -lh public/js/g/administracao/aprovacao-extra-config/
```

### Verificar se as rotas estão registradas

```bash
docker compose exec mybpdp php artisan route:list | grep -i "aprovacao-extra"
```

### Verificar se as migrations rodaram

```bash
docker compose exec mybpdp php artisan migrate:status | grep aprovacao
```

### Verificar pacotes NPM instalados

```bash
npm list sweetalert2 vue-multiselect
```

## Troubleshooting

### Erro: "Class not found"

```bash
docker compose exec mybpdp php artisan config:clear
docker compose exec mybpdp composer dump-autoload
```

### Erro: "Route not found"

```bash
docker compose exec mybpdp php artisan route:clear
docker compose exec mybpdp php artisan route:cache
```

### Erro: "View not found"

```bash
docker compose exec mybpdp php artisan view:clear
```

### Assets não carregam

```bash
# Limpar cache do navegador
# Ou recompilar
npm run dev
```

### Permissão negada

```bash
docker compose exec mybpdp chmod -R 775 storage bootstrap/cache
docker compose exec mybpdp chown -R www-data:www-data storage bootstrap/cache
```

## URLs Úteis

- **Aplicação**: http://localhost:8000
- **PhpMyAdmin** (se configurado): http://localhost:8080
- **Tela de Configuração**: http://localhost:8000/g/administracao/aprovacao-extra-config

## Estrutura de Arquivos

```
mybp/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── AprovacaoExtraConfigController.php
│   └── Models/
│       ├── AprovacaoExtraConfig.php
│       ├── DemissaoPrevista.php
│       └── FeriasPrevista.php
│
├── database/
│   └── migrations/
│       ├── 2025_01_30_000001_create_aprovacao_extra_configs_table.php
│       ├── 2025_01_30_000002_add_aprovacao_extra_to_demissao_previstas_table.php
│       ├── 2025_01_30_000003_add_aprovacao_extra_to_ferias_previstas_table.php
│       └── 2025_01_30_000004_add_usuarios_autorizados_to_aprovacao_extra_configs_table.php
│
├── resources/
│   ├── js/
│   │   ├── components/
│   │   │   └── administracao/
│   │   │       └── AprovacaoExtraConfig.vue
│   │   └── g/
│   │       └── administracao/
│   │           └── aprovacao-extra-config/
│   │               └── app.js
│   └── views/
│       ├── g/
│       │   └── administracao/
│       │       └── aprovacao-extra-config/
│       │           └── index.blade.php
│       └── layouts/
│           └── menu.blade.php
│
├── routes/
│   └── web.php
│
├── public/
│   └── js/
│       └── g/
│           └── administracao/
│               └── aprovacao-extra-config/
│                   └── app.js (compilado)
│
├── docs/
│   ├── README_APROVACAO_EXTRA.md
│   ├── COMO_USAR_APROVACAO_EXTRA.md
│   └── [outros arquivos de documentação]
│
├── webpack.mix.js
├── package.json
└── CHECKLIST_FINAL_APROVACAO_EXTRA.md
```

---

**Dica**: Salve este arquivo para referência rápida!
