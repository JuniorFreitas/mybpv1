-- ============================================================================
-- SQL PARA CONFIGURAÇÃO INICIAL DO SISTEMA DE APROVAÇÃO EXTRA
-- ============================================================================
-- Arquivo: docs/SQL_APROVACAO_EXTRA_SETUP.sql
-- Data: 30/01/2025
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1. CRIAR HABILIDADES (PERMISSÕES)
-- ----------------------------------------------------------------------------

INSERT INTO habilidades (nome, descricao, created_at, updated_at) VALUES
('administracao_aprovacao_extra_config', 'Gerenciar configurações de aprovação extra', NOW(), NOW()),
('planejamento_movimentacao_demissao_aprovar_extra', 'Aprovar demissões como aprovação extra', NOW(), NOW()),
('planejamento_movimentacao_ferias_aprovar_extra', 'Aprovar férias como aprovação extra', NOW(), NOW()),
('planejamento_movimentacao_mudanca_cargo_aprovar_extra', 'Aprovar mudança de cargo como aprovação extra', NOW(), NOW()),
('planejamento_movimentacao_transferencia_aprovar_extra', 'Aprovar transferência como aprovação extra', NOW(), NOW());

-- ----------------------------------------------------------------------------
-- 2. ATRIBUIR HABILIDADES AO PAPEL DE ADMINISTRADOR
-- ----------------------------------------------------------------------------

-- Buscar ID da habilidade criada
SET @habilidade_config_id = (SELECT id FROM habilidades WHERE nome = 'administracao_aprovacao_extra_config');

-- Buscar ID do papel de Administrador (ajustar conforme necessário)
SET @papel_admin_id = (SELECT id FROM papeis WHERE nome = 'Administrador' LIMIT 1);

-- Atribuir habilidade ao papel
INSERT IGNORE INTO habilidades_papel (habilidade_id, papel_id)
VALUES (@habilidade_config_id, @papel_admin_id);

-- ----------------------------------------------------------------------------
-- 3. EXEMPLOS DE CONFIGURAÇÃO DE APROVAÇÃO EXTRA
-- ----------------------------------------------------------------------------

-- EXEMPLO 1: Empresa Hospital - SESMT para demissões
-- Ajustar empresa_id conforme seu banco
INSERT INTO aprovacao_extra_configs (empresa_id, tipo_processo, nome_aprovacao, ativo, created_at, updated_at)
VALUES (1, 'demissao', 'SESMT', true, NOW(), NOW());

-- EXEMPLO 2: Empresa Hospital - Coordenador para férias
INSERT INTO aprovacao_extra_configs (empresa_id, tipo_processo, nome_aprovacao, ativo, created_at, updated_at)
VALUES (1, 'ferias', 'Coordenador', true, NOW(), NOW());

-- EXEMPLO 3: Empresa Construção - Engenheiro de Segurança para demissões
INSERT INTO aprovacao_extra_configs (empresa_id, tipo_processo, nome_aprovacao, ativo, created_at, updated_at)
VALUES (2, 'demissao', 'Engenheiro de Segurança', true, NOW(), NOW());

-- EXEMPLO 4: Empresa Indústria - Supervisor para demissões e férias
INSERT INTO aprovacao_extra_configs (empresa_id, tipo_processo, nome_aprovacao, ativo, created_at, updated_at)
VALUES 
(3, 'demissao', 'Supervisor', true, NOW(), NOW()),
(3, 'ferias', 'Supervisor', true, NOW(), NOW());

-- ----------------------------------------------------------------------------
-- 4. CONSULTAS ÚTEIS PARA VERIFICAÇÃO
-- ----------------------------------------------------------------------------

-- Listar todas as configurações de aprovação extra
SELECT 
    c.id,
    cli.razao_social as empresa,
    c.tipo_processo,
    c.nome_aprovacao,
    c.ativo,
    c.created_at
FROM aprovacao_extra_configs c
JOIN clientes cli ON c.empresa_id = cli.id
ORDER BY cli.razao_social, c.tipo_processo;

-- Verificar habilidades criadas
SELECT * FROM habilidades 
WHERE nome LIKE '%aprovacao_extra%' 
   OR nome LIKE '%aprovar_extra%';

-- Verificar papéis com acesso à configuração
SELECT 
    p.nome as papel,
    h.nome as habilidade,
    h.descricao
FROM papeis p
JOIN habilidades_papel hp ON p.id = hp.papel_id
JOIN habilidades h ON hp.habilidade_id = h.id
WHERE h.nome LIKE '%aprovacao_extra%';

-- ----------------------------------------------------------------------------
-- 5. CONSULTAS DE ANÁLISE (APÓS USO)
-- ----------------------------------------------------------------------------

-- Demissões com aprovação extra aprovada
SELECT 
    d.id,
    u.nome as colaborador,
    d.data_demissao,
    d.status_aprovacao as status_gestor,
    d.status_aprovacao_rh as status_rh,
    d.status_aprovacao_extra as status_extra,
    u_extra.nome as aprovador_extra,
    c.nome_aprovacao as tipo_aprovacao_extra
FROM demissao_previstas d
JOIN users u ON d.colaborador_id = u.id
LEFT JOIN users u_extra ON d.aprovacao_extra_id = u_extra.id
LEFT JOIN aprovacao_extra_configs c ON d.empresa_id = c.empresa_id 
    AND c.tipo_processo = 'demissao' 
    AND c.ativo = 1
WHERE d.status_aprovacao_extra IS NOT NULL;

-- Tempo médio de aprovação extra
SELECT 
    c.nome_aprovacao,
    AVG(TIMESTAMPDIFF(HOUR, d.data_aprovacao_rh, d.data_aprovacao_extra)) as horas_media,
    COUNT(*) as total_aprovacoes
FROM demissao_previstas d
JOIN aprovacao_extra_configs c ON d.empresa_id = c.empresa_id 
    AND c.tipo_processo = 'demissao'
WHERE d.data_aprovacao_extra IS NOT NULL
GROUP BY c.nome_aprovacao;

-- Demissões pendentes de aprovação extra por empresa
SELECT 
    cli.razao_social as empresa,
    c.nome_aprovacao as aguardando,
    COUNT(*) as total_pendente
FROM demissao_previstas d
JOIN clientes cli ON d.empresa_id = cli.id
JOIN aprovacao_extra_configs c ON d.empresa_id = c.empresa_id 
    AND c.tipo_processo = 'demissao' 
    AND c.ativo = 1
WHERE d.status_aprovacao = 'aprovado'
  AND d.status_aprovacao_rh = 'aprovado'
  AND d.status_aprovacao_extra IS NULL
GROUP BY cli.razao_social, c.nome_aprovacao;

-- Taxa de aprovação/reprovação na aprovação extra
SELECT 
    c.nome_aprovacao,
    d.status_aprovacao_extra,
    COUNT(*) as total,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (PARTITION BY c.nome_aprovacao), 2) as percentual
FROM demissao_previstas d
JOIN aprovacao_extra_configs c ON d.empresa_id = c.empresa_id 
    AND c.tipo_processo = 'demissao'
WHERE d.status_aprovacao_extra IS NOT NULL
GROUP BY c.nome_aprovacao, d.status_aprovacao_extra;

-- ----------------------------------------------------------------------------
-- 6. SCRIPTS DE MANUTENÇÃO
-- ----------------------------------------------------------------------------

-- Desativar todas as configurações de um tipo para uma empresa
-- (Útil antes de ativar uma nova)
UPDATE aprovacao_extra_configs 
SET ativo = false 
WHERE empresa_id = 1 
  AND tipo_processo = 'demissao';

-- Ativar configuração específica
UPDATE aprovacao_extra_configs 
SET ativo = true 
WHERE id = 1;

-- Remover configurações inativas antigas (mais de 6 meses)
DELETE FROM aprovacao_extra_configs 
WHERE ativo = false 
  AND created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);

-- ----------------------------------------------------------------------------
-- 7. BACKUP ANTES DE IMPLEMENTAR
-- ----------------------------------------------------------------------------

-- IMPORTANTE: Executar antes de aplicar as migrations
-- mysqldump -u usuario -p nome_banco > backup_antes_aprovacao_extra.sql

-- ----------------------------------------------------------------------------
-- 8. ROLLBACK (SE NECESSÁRIO)
-- ----------------------------------------------------------------------------

-- Remover configurações
-- DELETE FROM aprovacao_extra_configs;

-- Remover campos das tabelas (executar migrations de rollback)
-- php artisan migrate:rollback --step=3

-- Remover habilidades
-- DELETE FROM habilidades WHERE nome LIKE '%aprovacao_extra%';
-- DELETE FROM habilidades WHERE nome LIKE '%aprovar_extra%';

-- ----------------------------------------------------------------------------
-- 9. DADOS DE TESTE (OPCIONAL)
-- ----------------------------------------------------------------------------

-- Criar usuário SESMT de teste (ajustar conforme necessário)
-- INSERT INTO users (nome, email, password, empresa_id, created_at, updated_at)
-- VALUES ('SESMT Teste', 'sesmt@empresa.com', '$2y$10$...', 1, NOW(), NOW());

-- Atribuir habilidade ao usuário SESMT
-- SET @user_sesmt_id = LAST_INSERT_ID();
-- SET @habilidade_aprovar_id = (SELECT id FROM habilidades 
--     WHERE nome = 'planejamento_movimentacao_demissao_aprovar_extra');
-- INSERT INTO habilidades_user (habilidade_id, user_id)
-- VALUES (@habilidade_aprovar_id, @user_sesmt_id);

-- ----------------------------------------------------------------------------
-- 10. VERIFICAÇÕES FINAIS
-- ----------------------------------------------------------------------------

-- Verificar se migrations foram aplicadas
SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'demissao_previstas'
  AND COLUMN_NAME LIKE '%aprovacao_extra%';

-- Verificar constraints
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('demissao_previstas', 'ferias_previstas', 'aprovacao_extra_configs')
  AND REFERENCED_TABLE_NAME IS NOT NULL;

-- ============================================================================
-- FIM DO ARQUIVO
-- ============================================================================

/*
INSTRUÇÕES DE USO:

1. Fazer backup do banco:
   mysqldump -u usuario -p nome_banco > backup.sql

2. Executar migrations:
   php artisan migrate

3. Executar este script:
   mysql -u usuario -p nome_banco < docs/SQL_APROVACAO_EXTRA_SETUP.sql

4. Ou copiar e colar os comandos necessários diretamente no MySQL

5. Ajustar IDs de empresas e usuários conforme seu banco

NOTAS IMPORTANTES:
- Ajuste os IDs de empresas conforme seu banco de dados
- Ajuste os nomes de papéis conforme seu sistema
- Teste primeiro em ambiente de desenvolvimento
- Sempre faça backup antes de executar em produção
*/
