# Exemplos — PDF documento formal

## Views migradas (referência)

| Tipo | View | Notas |
|---|---|---|
| Autodeclaração | `pdf/historico/dossie/autodeclaracao_etnico_racial.blade.php` | Curto; rodapé geração |
| Vale transporte | `pdf/historico/dossie/valetransporte.blade.php` | 1 assinante; linha 9 cm |
| Checklist | `pdf/historico/dossie/docchecklist.blade.php` | Tabela; 1 página |
| Termo confidencialidade | `pdf/historico/dossie/termoconfiabilidade.blade.php` | Longo; paginação; `novaPagina` |
| Contrato FIXO | `pdf/historico/dossie/default/contratos/contratotrabalhoassinado.blade.php` | + Termo prorrogação |
| Carta medida | `pdf/admissao/historico/medidasadministrativas/carta-advertencia.blade.php` | Texto original + formal |
| Carta oferta | `pdf/admissao/carta-oferta.blade.php` | Header inline (mesmo visual) |

## Dossiê — registrar view

Em `DossiePdfService`:

```php
private const VIEWS_DOCUMENTO_FORMAL = [
    // ...
    'pdf.historico.dossie.meunovodoc',
];

private const VIEWS_RODAPE_PAGINACAO = [
    // só se multipágina / longo
    'pdf.historico.dossie.meunovodoc',
];
```

## public_id no Vue

```javascript
gerarPdf(obj) {
    if (!obj.public_id) return
    open(`${URL_ADMIN}/historico/.../${obj.public_id}/pdf`, 'blank')
}
```

## Controller decode

```php
public function pdf(string $publicId)
{
    try {
        $id = \App\Support\RuntimePublicId::decode($publicId);
    } catch (\InvalidArgumentException $e) {
        abort(404);
    }
    // ...
}
```
