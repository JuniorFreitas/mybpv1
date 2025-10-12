# Documentação do Projeto MyBP

Esta pasta contém toda a documentação técnica do projeto MyBP.

## 📋 Índice da Documentação

### 🚀 Deploy e Infraestrutura
- **[README-DEPLOY.md](./README-DEPLOY.md)** - Documentação completa do processo de deploy
- **[MELHORIAS_DEPLOY.md](./MELHORIAS_DEPLOY.md)** - Melhorias implementadas no script de deploy
- **[LIMPEZA_TASK_DEFINITIONS.md](./LIMPEZA_TASK_DEFINITIONS.md)** - Limpeza automática de Task Definitions ECS
- **[LIMPEZA_ECR_AUTOMATICA.md](./LIMPEZA_ECR_AUTOMATICA.md)** - Limpeza automática de imagens ECR por prefixo
- **[LIMPEZA_IMAGENS_LATEST.md](./LIMPEZA_IMAGENS_LATEST.md)** - Limpeza de imagens 'latest' ECR para reduzir custos

### 🔧 Soluções Técnicas
- **[SOLUCAO_DUPLICACAO_JOB_ECS.md](./SOLUCAO_DUPLICACAO_JOB_ECS.md)** - Solução para duplicação de jobs em ambiente ECS
- **[SOLUCAO_KERNEL_ECS.md](./SOLUCAO_KERNEL_ECS.md)** - Solução para comandos agendados em ambiente ECS

### 👤 Acesso e Segurança
- **[README_FIRST_ACCESS_TEMP_PASSWORD.md](./README_FIRST_ACCESS_TEMP_PASSWORD.md)** - Documentação de primeiro acesso com senha temporária
- **[README_PASSWORD_RESET.md](./README_PASSWORD_RESET.md)** - Documentação de reset de senhas

## 📁 Estrutura da Documentação

```
docs/
├── README.md                                    # Este arquivo (índice)
├── README-DEPLOY.md                            # Deploy principal
├── MELHORIAS_DEPLOY.md                         # Melhorias no deploy
├── LIMPEZA_TASK_DEFINITIONS.md                 # Limpeza de Task Definitions
├── LIMPEZA_ECR_AUTOMATICA.md                   # Limpeza de imagens ECR
├── SOLUCAO_DUPLICACAO_JOB_ECS.md               # Jobs em ECS
├── SOLUCAO_KERNEL_ECS.md                       # Comandos agendados
├── README_FIRST_ACCESS_TEMP_PASSWORD.md        # Primeiro acesso
└── README_PASSWORD_RESET.md                    # Reset de senhas
```

## 🔍 Como Navegar

### Para Desenvolvedores
1. **Deploy**: Comece com `README-DEPLOY.md`
2. **Problemas ECS**: Consulte `SOLUCAO_DUPLICACAO_JOB_ECS.md` e `SOLUCAO_KERNEL_ECS.md`
3. **Melhorias**: Veja `MELHORIAS_DEPLOY.md`

### Para Administradores
1. **Acesso**: Consulte `README_FIRST_ACCESS_TEMP_PASSWORD.md`
2. **Senhas**: Veja `README_PASSWORD_RESET.md`
3. **Deploy**: Use `README-DEPLOY.md`

### Para DevOps
1. **Deploy**: `README-DEPLOY.md` e `MELHORIAS_DEPLOY.md`
2. **ECS**: `SOLUCAO_DUPLICACAO_JOB_ECS.md` e `SOLUCAO_KERNEL_ECS.md`

## 📝 Convenções

- **Arquivos de solução**: Começam com `SOLUCAO_`
- **Documentação de deploy**: Contém `DEPLOY` no nome
- **Documentação de acesso**: Contém `ACCESS` ou `PASSWORD` no nome
- **Este README**: Sempre atualizado com novos documentos

## 🔄 Atualizações

Quando adicionar nova documentação:
1. Coloque o arquivo nesta pasta `docs/`
2. Atualize este `README.md` com o novo item
3. Mantenha a organização por categoria
4. Use nomes descritivos e consistentes

## 📞 Suporte

Para dúvidas sobre a documentação:
- Verifique primeiro este índice
- Consulte o arquivo específico da área
- Se não encontrar, adicione nova documentação aqui
