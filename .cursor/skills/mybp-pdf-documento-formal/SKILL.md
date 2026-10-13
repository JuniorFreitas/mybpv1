---
name: mybp-pdf-documento-formal
description: >-
  Migra PDFs DomPDF do MyBP para o layout formal padronizado (cabeçalho logo/CNPJ,
  tipografia DejaVu, margens, rodapé canvas, fechamento de assinaturas, RuntimePublicId).
  Use when the user asks to padronizar PDF, mesmo estilo formal, carta DomPDF, dossiê PDF,
  aplicarPadraoDocumento, public_id runtime em PDF, ou migrar blade pdf/*.
---

# MyBP PDF — Documento Formal (DomPDF)

Skill para migrar ou criar PDFs no **mesmo estilo formal** já usado em dossiê, demissão, carta oferta e medidas administrativas.

## Regra de ouro

**Não alterar o texto legal/conteúdo original.** Só layout, tipografia, cabeçalho, rodapé, assinaturas e URL/`public_id`.

Se o usuário disser “não é para mudar o conteúdo”, restaurar o texto original verbatim e manter só o visual.

## Antes de editar

1. Localizar rota → controller/service → view Blade.
2. Abrir a view atual e **guardar o texto/cláusulas** (diff mental ou `git show HEAD:path`).
3. Abrir **1 referência já migrada**:
   - Curto (1 pág.): `resources/views/pdf/historico/dossie/valetransporte.blade.php`
   - Longo + paginação + assinaturas em página nova: `resources/views/pdf/historico/dossie/termoconfiabilidade.blade.php`
   - Contrato + termo: `resources/views/pdf/historico/dossie/default/contratos/contratotrabalhoassinado.blade.php`
4. Conferir partials:
   - `resources/views/pdf/partials/documento_formal_styles.blade.php`
   - `resources/views/pdf/partials/documento_formal_cabecalho.blade.php`
   - `resources/views/pdf/partials/documento_formal_fechamento.blade.php`
5. Serviço de opções/rodapé: `App\Services\Demissao\ComunicadoDispensaSemJustaCausaPdfService`

## Estrutura Blade obrigatória

```blade
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>...</title>
    <style>
        @include('pdf.partials.documento_formal_styles')
        {{-- overrides só de CONTEÚDO (nunca encolher .cabecalho*) --}}
    </style>
</head>
<body>
@php
    $dados_empresa = is_array($dados['dados_empresa'] ?? null) ? $dados['dados_empresa'] : [];
@endphp
<div class="folha">
    @include('pdf.partials.documento_formal_cabecalho', ['dados_empresa' => $dados_empresa])
    {{-- título + corpo ORIGINAL --}}
    @include('pdf.partials.documento_formal_fechamento', [...])
</div>
</body>
</html>
```

### DomPDF — não fazer

- `* { margin: 0 }` (anula `@page margin`)
- `float` / `inline-block` para assinaturas lado a lado (quebra layout)
- Encolher logo/cabeçalho para “caber em 1 página” — cabeçalho **sempre** no tamanho do partial padrão

### DomPDF — fazer

- Assinaturas/testemunhas via **table** no partial `documento_formal_fechamento`
- `page-break-inside: avoid` no bloco de fechamento
- Assinaturas que não cabem / contratos longos: `'novaPagina' => true`
- Seção tipo Termo de Prorrogação: wrapper `.doc-secao-pagina` (+ `--termo` se precisar distribuir)

## Compactar para 1 página (sem mexer no cabeçalho)

Override só corpo:

```css
@page { size: A4 portrait; margin: 5mm 10mm 14mm 10mm; }
html, body { font-size: 10pt; }
.titulo { font-size: 11pt; margin: 0 0 10pt; line-height: 1.3; }
.paragrafo { font-size: 10pt; line-height: 1.4; margin: 0 0 8pt; }
.doc-fechamento { margin-top: 12pt; }
.doc-fechamento__assinantes--unico { margin-top: 18pt; }
.doc-fechamento__assinantes--unico .doc-fechamento__espaco { height: 28pt; }
```

**Não** sobrescrever `.cabecalho`, `.cabecalho__logo`, `.cabecalho__razao`, `.cabecalho__meta`.

## Fechamento (assinaturas)

```blade
@include('pdf.partials.documento_formal_fechamento', [
    'localData' => 'São Luís - MA, ' . (new \MasterTag\DataHora())->dataCompletaExt() . '.',
    'assinantes' => [
        ['nome' => $razaoSocial, 'papel' => 'EMPRESA'],      // papel acima do nome
        ['nome' => $nomeEmpregado, 'subtitulo' => 'COLABORADOR(A)'], // abaixo do nome
    ],
    'testemunhas' => 2,   // 0 desativa
    'novaPagina' => false, // true = página própria distribuída
])
```

Assinante único (linha 9 cm centralizada): omitir `papel` / usar só `nome`.

## Geração PDF no PHP

```php
/** @var \Barryvdh\DomPDF\PDF $pdf */
$pdf = app('dompdf.wrapper');
$pdf->loadView($view, $viewData);

app(\App\Services\Demissao\ComunicadoDispensaSemJustaCausaPdfService::class)
    ->aplicarPadraoDocumento(
        $pdf,
        (string) (auth()->user()?->nome ?? ''),
        // RODAPE_GERACAO (padrão) | RODAPE_PAGINACAO (docs longos)
        \App\Services\Demissao\ComunicadoDispensaSemJustaCausaPdfService::RODAPE_GERACAO
    );
```

### Dossiê (`DossiePdfService`)

Registrar a view em:

- `VIEWS_DOCUMENTO_FORMAL` — aplica padão
- `VIEWS_RODAPE_PAGINACAO` — só se for documento longo (contrato, termo confidencialidade, etc.)

`$dados_empresa` vem de `Sistema::getEmpresaFilialMatriz($filialId, $empresaId)` (array com `logo`, `razao_social`, `cnpj`, `endereco_completo`, `empresa_id`).

## RuntimePublicId (URL sem ID sequencial)

Classe: `App\Support\RuntimePublicId`

1. **Model** (ou no JSON da listagem):

```php
protected $appends = ['public_id'];

public function getPublicIdAttribute(): ?string
{
    $id = (int) ($this->attributes['id'] ?? 0);
    return $id > 0 ? \App\Support\RuntimePublicId::encode($id) : null;
}
```

2. **Rota**: `/{publicId}/pdf` (ou `/{tipo}/{publicId}` no dossiê).
3. **Controller**: `RuntimePublicId::decode($publicId)` → `abort(404)` se inválido.
4. **Vue**: abrir com `item.public_id` — nunca IDs numéricos na URL pública do PDF.

Refs: dossiê (`DossieController` + `Dossie.vue`), medidas (`MedidasAdministrativasController` + `MedidasAdministrativas.vue`), demissão prevista.

## Workflow de migração

```
- [ ] Mapear rota/controller/view
- [ ] Copiar texto original intacto
- [ ] Trocar layout antigo (pdf_filial / cabecalio* / rodape*) pelo formal
- [ ] Cabeçalho = partial padrão (sem shrink)
- [ ] Corpo compacto se precisar 1 página
- [ ] Fechamento via partial (table)
- [ ] Wire aplicarPadraoDocumento (+ paginação se longo)
- [ ] public_id se a URL expõe IDs
- [ ] Atualizar Vue/link
- [ ] Verificar pages=1 (curtos) via tinker/output DomPDF
```

## Verificação rápida (Docker)

```bash
docker compose exec -T mybpdp php artisan view:clear
docker compose exec -T mybpdp php artisan tinker --execute="..."
# após output(): echo pages= . \$pdf->getDomPDF()->getCanvas()->get_page_count()
```

## Anti-padrões aprendidos

| Errado | Certo |
|---|---|
| Reescrever cláusulas “melhor” | Manter texto original |
| Logo menor para caber página | Compactar só corpo |
| Float testemunhas | Table no fechamento |
| Rodapé Blade + canvas | Só `aplicarPadraoDocumento` |
| URL `/123/456/pdf` | `/{public_id}/pdf` |

## Referências

- Partials: `resources/views/pdf/partials/documento_formal_*.blade.php`
- Serviço: `app/Services/Demissao/ComunicadoDispensaSemJustaCausaPdfService.php`
- Dossiê: `app/Services/Dossie/DossiePdfService.php`
- Public ID: `app/Support/RuntimePublicId.php`
- Exemplos extras: [examples.md](examples.md)
