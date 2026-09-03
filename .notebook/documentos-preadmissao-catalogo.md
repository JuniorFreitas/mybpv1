# Catálogo documentos pré-admissão
> Por empresa; alimenta o formulário do candidato

Entry: `app/Http/Controllers/DocumentoPreadmissaoController.php` + `DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa()`
UI cadastro: `resources/js/components/cadastros/documentospreadmissao/DocumentosPreadmissao.vue`
UI candidato: `resources/js/components/documento/Documento.vue`
Seed: `database/seeders/DocumentoEmpresaAdmissaoTableSeeder.php`

Tabelas:
- `documentos_curriculos_cat_adm_empresa` — `empresa_id`, `label`, `ativo`
- `documentos_curriculos_adm_empresa` — `empresa_id`, `categoria_id`, `label`, `metodo`, `descricao`, `tipo`, `configuracoes` (json), `ordem`, `ativo`

Regras:
- Sem catálogo global: cada cliente já tem suas linhas (`empresa_id` obrigatório)
- Anexos do currículo em `documentos_curriculos.tipo` = `documentos_curriculos_adm_empresa.tipo`
- `configuracoes`: `obrigatorio`, `apenas_img`, `apenas_pdf`, `apenas_pdf_img`, `multiple`, `min`, `max`, `sogestao` (`sogestao` true esconde no form do candidato)
- Cache key `docAdmEmpresa_{empresa_id}` TTL 168h — invalidar em CRUD (`limparCache`); getter usa get/put (não forget-on-read)
- Model categoria: `$table = documentos_curriculos_cat_adm_empresa`
- Cadastro UI: `/g/cadastro/documentos-preadmissao` (permissão `cadastro_documentos_preadmissao`)
- `foto3x4` é padrão do sistema (Curriculo::FotoTres, dossiê, admissão): não edita/inativa; `garantirPadraoSistema()` cria/reativa por empresa

Updated: 2026-08-31
