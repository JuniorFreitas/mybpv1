-- =============================================================================
-- Script: Popular centro de custo para CIH (empresa_id = 40568)
-- Objetivo: Preencher centro_custo_id onde está NULL usando a informação da
--           admissão dos colaboradores vinculados ao CIH.
-- Uso: Executar em ambiente de homologação primeiro. Backup recomendado.
-- =============================================================================

-- -----------------------------------------------------------------------------
-- OPÇÃO A: Atualizar CIHs que estão SEM centro de custo, usando o centro de
--          custo da ADMISSÃO dos colaboradores vinculados ao CIH.
-- (Cada CIH recebe o centro de custo de um dos colaboradores que participam
--  desse CIH e que já têm centro de custo preenchido na admissão.)
-- -----------------------------------------------------------------------------

-- Visualização prévia (SELECT): quantos CIHs serão atualizados e com qual centro
SELECT
    c.id AS cih_id,
    c.centro_custo_id AS cih_centro_custo_atual,
    sub.cc_id AS novo_centro_custo_id,
    cc.label AS centro_custo_label
FROM cihs c
INNER JOIN (
    SELECT
        cf.cih_id,
        (
            SELECT a.centro_custo_id
            FROM cih_feedback cf2
            INNER JOIN admissoes a
                ON a.feedback_id = cf2.feedback_id
                AND (a.deleted_at IS NULL)
            INNER JOIN centro_custos cc2
                ON cc2.id = a.centro_custo_id
                AND cc2.empresa_id = 40568
            WHERE cf2.cih_id = cf.cih_id
              AND a.centro_custo_id IS NOT NULL
            LIMIT 1
        ) AS cc_id
    FROM cih_feedback cf
    INNER JOIN cihs cih
        ON cih.id = cf.cih_id
        AND cih.empresa_id = 40568
        AND cih.centro_custo_id IS NULL
        AND (cih.deleted_at IS NULL)
    GROUP BY cf.cih_id
) sub ON sub.cih_id = c.id AND sub.cc_id IS NOT NULL
INNER JOIN centro_custos cc ON cc.id = sub.cc_id AND cc.empresa_id = 40568;

-- UPDATE: aplicar o centro de custo da admissão nos CIHs
UPDATE cihs c
INNER JOIN (
    SELECT
        cf.cih_id,
        (
            SELECT a.centro_custo_id
            FROM cih_feedback cf2
            INNER JOIN admissoes a
                ON a.feedback_id = cf2.feedback_id
               AND a.deleted_at IS NULL
            INNER JOIN centro_custos cc2
                ON cc2.id = a.centro_custo_id
               AND cc2.empresa_id = 40568
            WHERE cf2.cih_id = cf.cih_id
              AND a.centro_custo_id IS NOT NULL
            LIMIT 1
        ) AS cc_id
    FROM cih_feedback cf
    INNER JOIN cihs cih
        ON cih.id = cf.cih_id
       AND cih.empresa_id = 40568
       AND cih.centro_custo_id IS NULL
       AND cih.deleted_at IS NULL
    GROUP BY cf.cih_id
) sub
    ON sub.cih_id = c.id
   AND sub.cc_id IS NOT NULL
INNER JOIN centro_custos cc
    ON cc.id = sub.cc_id
   AND cc.empresa_id = 40568
SET c.centro_custo_id = sub.cc_id
WHERE c.empresa_id = 40568
  AND c.centro_custo_id IS NULL
  AND c.deleted_at IS NULL;


-- =============================================================================
-- OPÇÃO B: Atualizar ADMISSÕES que estão SEM centro de custo, usando o centro
--          de custo do CIH em que o colaborador está vinculado.
-- (Use se a informação do centro de custo está no CIH e você quer espelhar
--  para a admissão do colaborador.)
-- =============================================================================

-- Prévia: admissões que seriam atualizadas (feedback da empresa 40568, sem centro de custo, com CIH que tem centro de custo)
/*
SELECT
    a.id AS admissao_id,
    a.feedback_id,
    a.centro_custo_id AS admissao_cc_atual,
    c.centro_custo_id AS cih_centro_custo_id,
    cc.label AS centro_custo_label
FROM admissoes a
INNER JOIN feedback_curriculos fc ON fc.id = a.feedback_id AND fc.empresa_id = 40568
INNER JOIN cih_feedback cf ON cf.feedback_id = a.feedback_id
INNER JOIN cihs c ON c.id = cf.cih_id AND (c.deleted_at IS NULL) AND c.centro_custo_id IS NOT NULL
INNER JOIN centro_custos cc ON cc.id = c.centro_custo_id AND cc.empresa_id = 40568
WHERE (a.deleted_at IS NULL)
  AND a.centro_custo_id IS NULL;
*/

-- UPDATE admissões (descomente para executar)
/*
UPDATE admissoes a
INNER JOIN feedback_curriculos fc ON fc.id = a.feedback_id AND fc.empresa_id = 40568
INNER JOIN cih_feedback cf ON cf.feedback_id = a.feedback_id
INNER JOIN cihs c ON c.id = cf.cih_id AND (c.deleted_at IS NULL) AND c.centro_custo_id IS NOT NULL
INNER JOIN centro_custos cc ON cc.id = c.centro_custo_id AND cc.empresa_id = 40568
SET a.centro_custo_id = c.centro_custo_id
WHERE (a.deleted_at IS NULL)
  AND a.centro_custo_id IS NULL;
*/
