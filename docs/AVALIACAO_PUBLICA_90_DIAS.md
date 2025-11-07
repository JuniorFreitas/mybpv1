# Sistema de Avaliação Pública de 90 Dias

## 📋 Visão Geral

Foi implementado um sistema completo para permitir que avaliações de 90 dias sejam realizadas através de **links públicos com tokens de segurança**, sem necessidade de autenticação no sistema.

## 🏗️ Arquitetura

### 1. **Banco de Dados** (Migration)
**Arquivo**: `database/migrations/2025_11_05_232741_add_token_expiracao_to_avaliacao_noventa_vencimentos.php`

Campos adicionados à tabela `avaliacao_noventa_vencimentos`:
- `token_avaliacao` (string, 64, unique, nullable) - Token único para acesso público
- `token_expiracao` (timestamp, nullable) - Data de expiração do token
- `avaliacao_realizada` (boolean, default false) - Flag indicando se avaliação foi concluída

```bash
# Para aplicar a migration
docker exec mybpdp_app php artisan migrate
```

### 2. **Service Layer** (Lógica de Negócio)
**Arquivo**: `app/Services/AvaliacaoNoventaService.php`

#### Novos Métodos:

**`gerarTokenAvaliacao($feedbackId, $diasValidade = 60)`**
- Gera token único de 64 caracteres hexadecimais
- Define data de expiração (padrão: 60 dias)
- Retorna: `['token' => string, 'expiracao' => Carbon, 'url' => string]`

**`validarTokenAvaliacao($token)`**
- Valida se token é válido, não expirado e não utilizado
- Verifica limite de avaliações (máx. 2)
- Retorna: `['valid' => bool, 'vencimento' => Model, 'mensagem' => string]`

**`marcarAvaliacaoRealizada($token)`**
- Marca avaliação como realizada após submissão
- Previne reuso do mesmo link

**`regenerarToken($feedbackId, $diasValidade = 60)`**
- Regenera token expirado
- Reseta flag de avaliação realizada

**`gerarOuRecuperarToken($avaliacao)` (privado)**
- Reutiliza token válido existente
- Gera novo se inexistente ou expirado

#### Métodos Atualizados:

**`montarVencimentos()`**
- Agora inclui `token` e `link_avaliacao` em cada vencimento
- Gera/recupera tokens automaticamente ao processar vencimentos

### 3. **Controller Público**
**Arquivo**: `app/Http/Controllers/AvaliacaoPublicaController.php`

#### Rotas Implementadas:

**GET `/avaliacao-90-dias/{token}`** → `mostrarFormulario()`
- Valida token
- Carrega dados do colaborador e perguntas
- Renderiza formulário público

**POST `/avaliacao-90-dias/{token}`** → `salvarAvaliacao()`
- Valida dados do formulário
- Salva avaliação (mesma estrutura do sistema interno)
- Marca token como utilizado
- Redireciona para página de sucesso

**GET `/avaliacao-90-dias/{token}/erro`** → `exibirErro()`
- Exibe mensagem de erro para tokens inválidos

#### Validações:
- Token válido e não expirado
- Avaliação não realizada anteriormente
- Todas as perguntas respondidas (notas 1-5)
- Gestor Imediato obrigatório
- Observação opcional

### 4. **Rotas Públicas**
**Arquivo**: `routes/web.php`

```php
Route::get('avaliacao-90-dias/{token}', [AvaliacaoPublicaController::class, 'mostrarFormulario'])
    ->name('avaliacao.publica.formulario');

Route::post('avaliacao-90-dias/{token}', [AvaliacaoPublicaController::class, 'salvarAvaliacao'])
    ->name('avaliacao.publica.salvar');

Route::get('avaliacao-90-dias/{token}/erro', [AvaliacaoPublicaController::class, 'exibirErro'])
    ->name('avaliacao.publica.erro');
```

**Características**:
- Sem middleware de autenticação
- Acessíveis publicamente via token
- CSRF protection habilitado

### 5. **Views Públicas**

#### **Formulário** (`resources/views/public/avaliacao90dias/formulario.blade.php`)
**Características**:
- Design responsivo com Bootstrap 4.6
- Gradiente roxo/violeta
- Cards informativos com dados do colaborador
- Seleção de notas 1-5 com radiobuttons estilizados
- Validação JavaScript antes de envio
- Badge de expiração do token
- Campos: Perguntas (notas), Gestor Imediato, Observações

**Dados Exibidos**:
- Nome do colaborador
- CPF
- Cargo
- Função
- Centro de Custo
- Data de Admissão
- Validade do link

#### **Sucesso** (`resources/views/public/avaliacao90dias/sucesso.blade.php`)
- Animação de check verde
- Confirmação de avaliação enviada
- Informação sobre única utilização do link

#### **Erro** (`resources/views/public/avaliacao90dias/erro.blade.php`)
- Ícone de alerta
- Mensagem de erro personalizada
- Possíveis motivos da falha
- Orientação para contato com RH

## 🔐 Segurança

### Proteção Implementada:
1. **Token Único**: 64 caracteres hexadecimais aleatórios
2. **Expiração**: Configurável (padrão 60 dias)
3. **Uso Único**: Flag `avaliacao_realizada` previne reuso
4. **Validação Multi-Camada**:
   - Token existe no BD
   - Token não expirado
   - Avaliação não realizada
   - Limite de avaliações não atingido
5. **CSRF Protection**: Habilitado via `@csrf` em formulários
6. **Validação Server-Side**: Laravel Validator
7. **Logs Detalhados**: Registro de todas as operações

### Tokens no Banco:
```sql
SELECT 
    token_avaliacao,
    token_expiracao,
    avaliacao_realizada,
    feedback_id
FROM avaliacao_noventa_vencimentos
WHERE token_avaliacao = 'abc123...';
```

## 📧 Integração com Email e CSV

### Fluxo Automático:

1. **Comando `mybp:avaliacao90dias` executa**
2. **Service `montarVencimentos()` processa**:
   - Gera/recupera token para cada vencimento
   - Adiciona `link_avaliacao` aos dados
3. **Excel gerado inclui coluna "Link Avaliação"**
4. **Email enviado com links tokenizados**

### Estrutura de Dados:
```php
[
    'colaborador' => 'João Silva',
    'cargo' => 'Ajudante',
    'funcao' => 'Produção',
    'centro_custo' => 'CC-001 - Produção',
    'prazo_vencido' => '01/12/2025',
    'status' => 'A VENCER',
    'token' => 'a1b2c3d4e5f6...',
    'link_avaliacao' => 'https://sistema.com/avaliacao-90-dias/a1b2c3d4e5f6...'
]
```

### Atualizar Template de Email:
**Arquivo**: `resources/views/email/admissao/historico/avaliacaoNoventaVencimento.blade.php`

Adicionar coluna "Link" na tabela:
```blade
<td>
    <a href="{{ $vencimento['link_avaliacao'] }}" 
       target="_blank"
       style="color: #667eea; text-decoration: underline;">
        Realizar Avaliação
    </a>
</td>
```

### Atualizar Geração de Excel:
**Método**: `gerarExcelS3()` em `AvaliacaoNoventaService`

Adicionar coluna "Link Avaliação":
```php
$cabecalhos = [
    'Colaborador', 
    'Cargo', 
    'Função', 
    'Centro de Custo', 
    'Data de Vencimento', 
    'Status', 
    'Dias em Atraso', 
    'Observação', 
    'Avaliações Realizadas',
    'Link Avaliação'  // <-- ADICIONAR
];

// No loop de dados:
$sheet->setCellValue('J' . $linha, $venc['link_avaliacao']); // <-- ADICIONAR
```

## 🚀 Como Usar

### 1. **Aplicar Migration**
```bash
docker exec mybpdp_app php artisan migrate
```

### 2. **Gerar Tokens Manualmente (Opcional)**
```php
use App\Services\AvaliacaoNoventaService;

$service = new AvaliacaoNoventaService();
$result = $service->gerarTokenAvaliacao($feedbackId, 60);

echo $result['url']; // https://sistema.com/avaliacao-90-dias/{token}
```

### 3. **Executar Comando de Notificações**
```bash
docker exec mybpdp_app php artisan mybp:avaliacao90dias --empresa_id=73473 --destinatario=gestores
```

**Resultado**:
- Tokens gerados automaticamente
- Links incluídos no email
- Links incluídos no CSV/Excel
- Jobs enfileirados para envio

### 4. **Acessar Link Público**
```
https://seu-dominio.com/avaliacao-90-dias/{token}
```

**Fluxo do Usuário**:
1. Clica no link recebido por email/CSV
2. Visualiza dados do colaborador
3. Responde perguntas (notas 1-5)
4. Informa Gestor Imediato
5. Adiciona observações (opcional)
6. Submete formulário
7. Vê confirmação de sucesso

## 📊 Relatórios e Logs

### Logs Registrados:
- `Token de avaliação gerado` - Quando token é criado
- `Avaliação 90 dias realizada via token público` - Ao submeter formulário
- `Avaliação marcada como realizada via token` - Após conclusão

### Consultas Úteis:
```sql
-- Tokens ativos
SELECT * FROM avaliacao_noventa_vencimentos 
WHERE token_avaliacao IS NOT NULL 
  AND avaliacao_realizada = 0 
  AND token_expiracao > NOW();

-- Tokens expirados
SELECT * FROM avaliacao_noventa_vencimentos 
WHERE token_expiracao < NOW();

-- Avaliações realizadas via token
SELECT * FROM avaliacao_noventa_vencimentos 
WHERE avaliacao_realizada = 1;
```

## 🔧 Manutenção

### Regenerar Token Expirado:
```php
$service = new AvaliacaoNoventaService();
$result = $service->regenerarToken($feedbackId, 60);
echo $result['url'];
```

### Limpeza de Tokens Antigos (Opcional):
```sql
-- Limpar tokens expirados há mais de 90 dias
UPDATE avaliacao_noventa_vencimentos 
SET token_avaliacao = NULL, 
    token_expiracao = NULL 
WHERE token_expiracao < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

## ✅ Checklist de Implementação

- [x] Migration criada e aplicada
- [x] Service com métodos de token
- [x] Controller público criado
- [x] Rotas públicas configuradas
- [x] View do formulário
- [x] View de sucesso
- [x] View de erro
- [x] Integração com montarVencimentos
- [x] Geração automática de tokens
- [ ] Atualizar template de email (adicionar coluna Link)
- [ ] Atualizar geração de Excel (adicionar coluna Link Avaliação)
- [ ] Testar fluxo completo
- [ ] Documentar processo para equipe

## 🎯 Próximos Passos

1. **Atualizar Email Template**:
   - Adicionar coluna "Link" na tabela do email
   - Incluir botão CTA "Realizar Avaliação"

2. **Atualizar Excel**:
   - Adicionar coluna "Link Avaliação"
   - Testar download e abertura de links

3. **Testes**:
   - Testar geração de token
   - Testar acesso via link público
   - Testar submissão de formulário
   - Testar token expirado
   - Testar token já utilizado
   - Testar limite de avaliações

4. **Monitoramento**:
   - Configurar alertas para tokens não utilizados
   - Dashboard de avaliações públicas realizadas

## 📝 Notas Importantes

- **Validade Padrão**: 60 dias (configurável)
- **Uso Único**: Link inválido após submissão
- **Máximo**: 2 avaliações por colaborador
- **Sem Autenticação**: Acesso via token é suficiente
- **Seguro**: Token único de 64 caracteres
- **Logs**: Todas operações são registradas
- **Responsivo**: Funciona em mobile/tablet/desktop

## 🆘 Troubleshooting

### Link não abre / Erro 404
- Verificar se rota está registrada em `routes/web.php`
- Conferir se controller existe e está no namespace correto
- Limpar cache de rotas: `php artisan route:clear`

### Token inválido mesmo sendo novo
- Verificar se migration foi aplicada
- Confirmar se campo `token_avaliacao` existe na tabela
- Verificar expiração (`token_expiracao`)

### Formulário não salva
- Verificar validação dos campos
- Conferir se model `FormularioNoventaDiasPergunta` existe
- Ver logs em `storage/logs/laravel.log`

### Token não gerado automaticamente
- Verificar se método `montarVencimentos` foi atualizado
- Confirmar chamada de `gerarOuRecuperarToken`
- Ver logs de erro

## 📞 Suporte

Para dúvidas ou problemas:
1. Verificar logs em `storage/logs/laravel.log`
2. Consultar documentação do Laravel
3. Contatar equipe de desenvolvimento
