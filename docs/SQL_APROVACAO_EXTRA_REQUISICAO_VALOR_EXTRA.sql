-- Script SQL para configurar Aprovação Extra em Requisição de Vaga e Valor Extra
-- Execute este script para habilitar a aprovação extra nos processos

-- ============================================================================
-- 1. CONFIGURAÇÃO DE APROVAÇÃO EXTRA PARA REQUISIÇÃO DE VAGA
-- ============================================================================

-- Exemplo: Adicionar aprovação extra para requisição de vaga
-- Altere os valores conforme necessário para sua empresa

INSERT INTO aprovacao_extra_configs
(empresa_id, tipo_processo, nome_aprovacao, usuarios_autorizados, ativo, created_at, updated_at)
VALUES
(
    1, -- ID da empresa (ALTERE PARA O ID DA SUA EMPRESA)
    'requisicao_vaga', -- Tipo do processo (não altere)
    'Gerência', -- Nome da aprovação (pode personalizar)
    '[2,3,4]', -- IDs dos usuários autorizados em formato JSON (ALTERE para IDs reais)
    1, -- Ativo (1 = sim, 0 = não)
    NOW(),
    NOW()
);

-- ============================================================================
-- 2. CONFIGURAÇÃO DE APROVAÇÃO EXTRA PARA VALOR EXTRA
-- ============================================================================

-- Exemplo: Adicionar aprovação extra para valor extra
-- Altere os valores conforme necessário para sua empresa

INSERT INTO aprovacao_extra_configs
(empresa_id, tipo_processo, nome_aprovacao, usuarios_autorizados, ativo, created_at, updated_at)
VALUES
(
    1, -- ID da empresa (ALTERE PARA O ID DA SUA EMPRESA)
    'valor_extra', -- Tipo do processo (não altere)
    'Diretoria', -- Nome da aprovação (pode personalizar)
    '[2,3,5]', -- IDs dos usuários autorizados em formato JSON (ALTERE para IDs reais)
    1, -- Ativo (1 = sim, 0 = não)
    NOW(),
    NOW()
);

-- ============================================================================
-- CONSULTAS ÚTEIS
-- ============================================================================

-- Verificar configurações existentes
SELECT
    id,
    empresa_id,
    tipo_processo,
    nome_aprovacao,
    usuarios_autorizados,
    ativo,
    created_at
FROM aprovacao_extra_configs
WHERE tipo_processo IN ('requisicao_vaga', 'valor_extra')
ORDER BY empresa_id, tipo_processo;

-- Listar usuários que podem aprovar (para pegar os IDs)
SELECT
    u.id,
    u.nome,
    u.login,
    c.razao_social as empresa
FROM users u
INNER JOIN clientes c ON c.id = u.empresa_id
WHERE u.ativo = 1
    AND u.empresa_id = 1 -- ALTERE para o ID da sua empresa
ORDER BY u.nome;

-- Verificar requisições de vaga com aprovação extra
SELECT
    rv.id,
    rv.created_at as data_solicitacao,
    u1.nome as solicitante,
    v.nome as cargo,
    rv.status_aprovacao as status_gestor,
    rv.status_aprovacao_extra,
    u2.nome as quem_aprovou_extra,
    rv.data_aprovacao_extra
FROM requisicao_vagas rv
LEFT JOIN users u1 ON u1.id = rv.user_id
LEFT JOIN vagas v ON v.id = rv.cargo_id
LEFT JOIN users u2 ON u2.id = rv.aprovacao_extra_id
WHERE rv.aprovacao_extra_id IS NOT NULL
ORDER BY rv.created_at DESC;

-- Verificar valores extras com aprovação extra
SELECT
    vep.id,
    vep.created_at as data_solicitacao,
    u1.nome as solicitante,
    u2.nome as colaborador,
    vep.tipo,
    vep.periodo_dias,
    vep.status_aprovacao as status_gestor,
    vep.status_aprovacao_extra,
    u3.nome as quem_aprovou_extra,
    vep.data_aprovacao_extra,
    vep.status_aprovacao_rh
FROM valor_extra_previstas vep
LEFT JOIN users u1 ON u1.id = vep.user_id
LEFT JOIN users u2 ON u2.id = vep.colaborador_id
LEFT JOIN users u3 ON u3.id = vep.aprovacao_extra_id
WHERE vep.aprovacao_extra_id IS NOT NULL
ORDER BY vep.created_at DESC;

-- ============================================================================
-- DESATIVAR APROVAÇÃO EXTRA (se necessário)
-- ============================================================================

-- Desativar aprovação extra para requisição de vaga
UPDATE aprovacao_extra_configs
SET ativo = 0, updated_at = NOW()
WHERE tipo_processo = 'requisicao_vaga'
    AND empresa_id = 1; -- ALTERE para o ID da sua empresa

-- Desativar aprovação extra para valor extra
UPDATE aprovacao_extra_configs
SET ativo = 0, updated_at = NOW()
WHERE tipo_processo = 'valor_extra'
    AND empresa_id = 1; -- ALTERE para o ID da sua empresa

-- ============================================================================
-- ATUALIZAR USUÁRIOS AUTORIZADOS
-- ============================================================================

-- Atualizar lista de usuários autorizados para requisição de vaga
UPDATE aprovacao_extra_configs
SET usuarios_autorizados = '[2,3,4,5]', -- ALTERE para os IDs desejados
    updated_at = NOW()
WHERE tipo_processo = 'requisicao_vaga'
    AND empresa_id = 1; -- ALTERE para o ID da sua empresa

-- Atualizar lista de usuários autorizados para valor extra
UPDATE aprovacao_extra_configs
SET usuarios_autorizados = '[2,3,5,6]', -- ALTERE para os IDs desejados
    updated_at = NOW()
WHERE tipo_processo = 'valor_extra'
    AND empresa_id = 1; -- ALTERE para o ID da sua empresa
