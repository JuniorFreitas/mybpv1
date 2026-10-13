/*
 * Padrão visual formal (demissão / carta oferta).
 * IMPORTANTE (DomPDF): NÃO usar "* { margin:0 }" — isso anula @page margin.
 */
html, body, .folha, .folha * {
    font-family: DejaVu Sans, Arial, sans-serif !important;
}
@page {
    size: A4 portrait;
    margin: 5mm 10mm 16mm 10mm;
}
html, body {
    font-size: 11pt;
    color: #000;
    background: #fff;
}
.folha { width: 100%; }
.cabecalho {
    text-align: center;
    margin: 0 0 18pt;
    padding: 0 0 8pt;
    border-bottom: 1pt solid #000;
}
.cabecalho__logo { margin: 0 auto 4pt; }
.cabecalho__logo img {
    display: block;
    margin: 0 auto;
    max-height: 16mm;
    max-width: 48mm;
}
.cabecalho__logo--compact img { max-height: 12mm; }
.cabecalho__razao {
    font-size: 10.5pt;
    font-weight: bold;
    text-transform: uppercase;
    line-height: 1.35;
    margin: 0 0 2pt;
}
.cabecalho__meta {
    font-size: 8.5pt;
    line-height: 1.35;
    margin: 0;
}
.titulo {
    text-align: center;
    font-size: 12pt;
    font-weight: bold;
    text-transform: uppercase;
    line-height: 1.5;
    margin: 0 0 22pt;
}
.destinatario {
    font-size: 11pt;
    line-height: 1.6;
    margin: 0 0 18pt;
    text-align: left;
}
.paragrafo {
    font-size: 11pt;
    line-height: 1.7;
    text-align: justify;
    text-indent: 12.5mm;
    margin: 0 0 14pt;
    word-spacing: normal;
    letter-spacing: normal;
}
.paragrafo--sem-recuo {
    text-indent: 0;
}
.fechamento {
    margin-top: 36pt;
}
.local-data {
    font-size: 11pt;
    line-height: 1.5;
    text-align: left;
    margin: 0 0 36pt;
}
.assinatura {
    text-align: center;
    font-size: 11pt;
    line-height: 1.5;
    margin: 0 0 16pt;
}
.assinatura__linha {
    width: 9cm;
    margin: 0 auto 6pt;
    border: none;
    border-top: 1pt solid #000;
}
.conteudo-carta p {
    font-size: 11pt;
    line-height: 1.7;
    margin: 0 0 14pt;
}
.conteudo-carta hr {
    width: 9cm;
    margin: 22pt auto 6pt auto;
    border: none;
    border-top: 1pt solid #000;
}
