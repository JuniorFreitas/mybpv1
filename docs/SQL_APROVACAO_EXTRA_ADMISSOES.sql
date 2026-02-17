-- ============================================
-- CONFIGURAÇÃO DE APROVAÇÃO EXTRA PARA ADMISSÕES
-- ============================================

-- 1. VERIFICAR CONFIGURAÇÕES EXISTENTES
-- ====================================
SELECT * FROM aprovacao_extra_configs
WHERE tipo_processo = 'admissao';

-- 2. CRIAR CONFIGURAÇÃO PARA EMPRESA (exemplo: empresa_id = 1)
-- ============================================================
-- IMPORTANTE: Ajustar os IDs dos usuários autorizados conforme necessário

-- Exemplo 1: Aprovação por Gerência
INSERT INTO aprovacao_extra_configs
(empresa_id, tipo_processo, nome_aprovacao, usuarios_autorizados, ativo, created_at, updated_at)
VALUES
(1, 'admissao', 'Gerência', JSON_ARRAY(2, 3), 1, NOW(), NOW());

-- Exemplo 2: Aprovação por Diretoria
INSERT INTO aprovacao_extra_configs
(empresa_id, tipo_processo, nome_aprovacao, usuarios_autorizados, ativo, created_at, updated_at)
VALUES
(1, 'admissao', 'Diretoria', JSON_ARRAY(5, 6, 7), 1, NOW(), NOW());

-- Exemplo 3: Aprovação por SESMT (Segurança do Trabalho)
INSERT INTO aprovacao_extra_configs
(empresa_id, tipo_processo, nome_aprovacao, usuarios_autorizados, ativo, created_at, updated_at)
VALUES
(1, 'admissao', 'SESMT', JSON_ARRAY(10, 11), 1, NOW(), NOW());

-- 3. LISTAR USUÁRIOS ELEGÍVEIS PARA APROVAÇÃO
-- ===========================================
-- Usuários com privilégio de gestão RH
SELECT id, nome, email
FROM users
WHERE empresa_id = 1
AND (privilegio_gestao_rh = 1 OR privilegio_aprovar_por_rh = 1)
ORDER BY nome;

-- 4. ATUALIZAR CONFIGURAÇÃO EXISTENTE
-- ===================================
-- Adicionar mais usuários autorizados
UPDATE aprovacao_extra_configs
SET usuarios_autorizados = JSON_ARRAY(2, 3, 5, 6),
    updated_at = NOW()
WHERE tipo_processo = 'admissao'
AND empresa_id = 1;

-- Alterar nome da aprovação
UPDATE aprovacao_extra_configs
SET nome_aprovacao = 'Coordenação',
    updated_at = NOW()
WHERE tipo_processo = 'admissao'
AND empresa_id = 1;

-- 5. DESATIVAR APROVAÇÃO EXTRA
-- ============================
UPDATE aprovacao_extra_configs
SET ativo = 0,
    updated_at = NOW()
WHERE tipo_processo = 'admissao'
AND empresa_id = 1;

-- Reativar
UPDATE aprovacao_extra_configs
SET ativo = 1,
    updated_at = NOW()
WHERE tipo_processo = 'admissao'
AND empresa_id = 1;

-- 6. VERIFICAR ADMISSÕES COM APROVAÇÃO EXTRA
-- ==========================================
SELECT
    ap.id,
    ap.cargo_id,
    c.nome as cargo,
    ap.data_admissao,
    ap.status_aprovacao as status_gestor,
    ap.status_aprovacao_extra,
    ap.status_aprovacao_rh,
    aec.nome_aprovacao,
    ap.created_at
FROM admissoes_previstas ap
LEFT JOIN aprovacao_extra_configs aec ON ap.aprovacao_extra_id = aec.id
LEFT JOIN cargos c ON ap.cargo_id = c.id
WHERE ap.empresa_id = 1
ORDER BY ap.created_at DESC
LIMIT 20;

-- 7. ESTATÍSTICAS DE APROVAÇÕES
-- =============================
-- Admissões pendentes de aprovação extra
SELECT COUNT(*) as total_pendentes
FROM admissoes_previstas
WHERE empresa_id = 1
AND status_aprovacao = 'aprovado'
AND status_aprovacao_extra IS NULL
AND aprovacao_extra_id IS NOT NULL;

-- Admissões aprovadas pela aprovação extra
SELECT COUNT(*) as total_aprovadas_extra
FROM admissoes_previstas
WHERE empresa_id = 1
AND status_aprovacao_extra = 'aprovado';

-- 8. REMOVER CONFIGURAÇÃO (USE COM CUIDADO!)
-- ==========================================
-- DELETE FROM aprovacao_extra_configs
-- WHERE tipo_processo = 'admissao'
-- AND empresa_id = 1;

-- 9. CONSULTA COMPLETA - VISÃO GERAL
-- ==================================
SELECT
    aec.id,
    aec.empresa_id,
    aec.tipo_processo,
    aec.nome_aprovacao,
    aec.usuarios_autorizados,
    aec.ativo,
    COUNT(ap.id) as total_admissoes,
    SUM(CASE WHEN ap.status_aprovacao_extra IS NULL THEN 1 ELSE 0 END) as pendentes,
    SUM(CASE WHEN ap.status_aprovacao_extra = 'aprovado' THEN 1 ELSE 0 END) as aprovadas,
    SUM(CASE WHEN ap.status_aprovacao_extra = 'reprovado' THEN 1 ELSE 0 END) as reprovadas
FROM aprovacao_extra_configs aec
LEFT JOIN admissoes_previstas ap ON ap.aprovacao_extra_id = aec.id
WHERE aec.tipo_processo = 'admissao'
GROUP BY aec.id, aec.empresa_id, aec.tipo_processo, aec.nome_aprovacao, aec.usuarios_autorizados, aec.ativo;

-- ============================================
-- NOTAS IMPORTANTES
-- ============================================
/*
1. FLUXO DE APROVAÇÃO DE ADMISSÕES:
   Gestor → Aprovação Extra (opcional) → RH

2. QUANDO A APROVAÇÃO EXTRA É ATIVADA:
   - O sistema verifica se existe configuração ativa para 'admissao'
   - Se sim, após aprovação do gestor, vai para aprovação extra
   - Se não, após aprovação do gestor, vai direto para RH

3. QUEM PODE APROVAR:
   - Usuários listados em 'usuarios_autorizados' do config
   - Usuários com privilegio_gestao_rh = 1
   - Usuários com privilegio_aprovar_por_rh = 1

4. TESTE RÁPIDO:
   - Criar uma configuração de teste
   - Criar uma solicitação de admissão
   - Aprovar como gestor
   - Verificar se aparece a opção de aprovação extra na interface
   - Aprovar/reprovar pela aprovação extra
   - Aprovar pelo RH

5. TROUBLESHOOTING:
   - Se não aparecer opção de aprovação extra:
     * Verificar se config está ativa (ativo = 1)
     * Verificar se gestor já aprovou
     * Verificar se usuário tem permissão
   - Se aparecer para todos:
     * Verificar usuarios_autorizados
     * Verificar privilegios do usuário
*/
