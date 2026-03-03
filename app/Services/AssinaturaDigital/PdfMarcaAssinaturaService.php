<?php

namespace App\Services\AssinaturaDigital;

use Illuminate\Support\Facades\Log;

/**
 * Gera uma cópia do PDF com QR code de verificação no canto inferior esquerdo de todas as páginas
 * e página final "Página de assinaturas".
 * Requer setasign/fpdi e setasign/fpdf (composer require setasign/fpdi setasign/fpdf).
 * Textos são convertidos de UTF-8 para ISO-8859-1 para exibição correta de acentuação no FPDF.
 */
class PdfMarcaAssinaturaService
{

    /**
     * Converte texto UTF-8 para ISO-8859-1 para exibição correta no FPDF (acentos, ç, etc.).
     * Substitui caracteres que não existem em ISO-8859-1 (ex.: em dash) por equivalentes ASCII.
     */
    protected function paraPdf(string $text): string
    {
        $map = [
            "\u{2014}" => '-',  // em dash —
            "\u{2013}" => '-',  // en dash –
            "\u{201C}" => '"',  // left double quote "
            "\u{201D}" => '"',  // right double quote "
            "\u{2018}" => "'",  // left single quote '
            "\u{2019}" => "'",  // right single quote '
            "\u{2026}" => '...', // ellipsis …
            "\u{00A0}" => ' ',  // non-breaking space
        ];
        foreach ($map as $unicode => $ascii) {
            $text = str_replace($unicode, $ascii, $text);
        }
        if (!mb_check_encoding($text, 'UTF-8')) {
            return $text;
        }
        $converted = @iconv('UTF-8', 'ISO-8859-1//IGNORE', $text);
        return $converted !== false ? $converted : $text;
    }

    /**
     * Retorna o conteúdo do PDF com QR code de verificação no canto inferior esquerdo de todas as páginas
     * e página final "Página de assinaturas".
     *
     * @param string $pdfContent Conteúdo binário do PDF original
     * @param string|null $dataAssinatura Ignorado (mantido por compatibilidade)
     * @param array $signatarios [ ['qr_payload' => 'url', ... ], ... ]
     * @param array $dadosPagina [ 'identificador' => '', 'data_ultima_atualizacao' => '', 'eventos' => [...] ]
     * @return string|null Conteúdo do novo PDF ou null em caso de erro
     */
    public function adicionarMarcaAgua(string $pdfContent, ?string $dataAssinatura = null, array $signatarios = [], array $dadosPagina = []): ?string
    {
        if (!class_exists(\setasign\Fpdi\Fpdi::class)) {
            Log::warning('PdfMarcaAssinaturaService: FPDI nao instalado. Rode: composer require setasign/fpdi setasign/fpdf');
            return null;
        }

        $tempIn = null;
        $qrPayload = $signatarios[0]['qr_payload'] ?? '';
        $qrSize = 18;
        $marginQr = 10;

        try {
            $tempIn = tempnam(sys_get_temp_dir(), 'doc_');
            if ($tempIn === false || file_put_contents($tempIn, $pdfContent) === false) {
                Log::warning('PdfMarcaAssinaturaService: falha ao criar arquivo temporário');
                return null;
            }

            $pdf = new \setasign\Fpdi\Fpdi();
            $pageCount = $pdf->setSourceFile($tempIn);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);
                $w = $size['width'] ?? $size['w'] ?? 210;
                $h = $size['height'] ?? $size['h'] ?? 297;
                $orientation = isset($size['orientation']) ? $size['orientation'] : ($w > $h ? 'L' : 'P');
                $pdf->AddPage($orientation, [$w, $h]);
                $pdf->useImportedPage($templateId);

                if ($qrPayload !== '') {
                    $qrPath = $this->gerarQrCodeTemp($qrPayload);
                    if ($qrPath && is_file($qrPath)) {
                        try {
                            $xQr = $w - $marginQr - $qrSize;
                            $yQr = $h - $marginQr - $qrSize;
                            $pdf->Image($qrPath, $xQr, $yQr, $qrSize, $qrSize);
                        } catch (\Throwable $e) {
                            // ignora se Image falhar
                        }
                        @unlink($qrPath);
                    }
                }
            }

            $this->adicionarPaginaAssinaturas($pdf, $signatarios, $dadosPagina);

            return $pdf->Output('S');
        } catch (\Throwable $e) {
            Log::error('PdfMarcaAssinaturaService: erro ao aplicar marca d\'água', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return null;
        } finally {
            if ($tempIn && is_file($tempIn)) {
                @unlink($tempIn);
            }
        }
    }

    /**
     * Adiciona a última página "Página de assinaturas" no estilo autenticação eletrônica:
     * bloco de autenticação (identificador, data), blocos de assinatura por signatário, e histórico.
     *
     * @param \setasign\Fpdi\Fpdi $pdf
     * @param array $signatarios [ ['nome' => '', 'email' => '', 'cpf' => '', 'data_formatada' => ''], ... ]
     * @param array $dadosPagina [ 'identificador' => '', 'data_ultima_atualizacao' => '', 'eventos' => [...] ]
     */
    protected function adicionarPaginaAssinaturas(\setasign\Fpdi\Fpdi $pdf, array $signatarios, array $dadosPagina = []): void
    {
        $pdf->AddPage('P', 'A4');
        $margin = 18;
        $pdf->SetMargins($margin, $margin, $margin);
        $pdf->SetAutoPageBreak(true, 15);
        $w = 210 - 2 * $margin;
        $pdf->SetTextColor(0, 0, 0);

        // ---- Topo: bloco de autenticação à direita ----
        $identificador = $this->paraPdf((string) ($dadosPagina['identificador'] ?? ''));
        $dataAtualizacao = $this->paraPdf((string) ($dadosPagina['data_ultima_atualizacao'] ?? date('d M Y \a\s H:i')));
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetXY($margin + $w - 75, $margin);
        $pdf->MultiCell(75, 4, $this->paraPdf("Autenticacao eletronica\nData e horarios em Horario de Brasilia (GMT-3)\nUltima atualizacao em ") . $dataAtualizacao . "\n" . $this->paraPdf('Identificador: ') . $identificador, 0, 'R');
        $pdf->Ln(2);

        // ---- Título central ----
        $pdf->SetFont('Helvetica', 'B', 18);
        $pdf->Cell(0, 14, $this->paraPdf('Pagina de assinaturas'), 0, 1, 'C');
        $pdf->Ln(4);

        // ---- Blocos de assinatura: QR à esquerda + texto (Assinado digitalmente por, CPF, data, local, hash, identificação) + linha + nome ----
        $docId = $dadosPagina['documento_id'] ?? 0;
        foreach ($signatarios as $s) {
            $yInicio = $pdf->GetY();
            $qrSize = 22;
            $xQr = $margin;
            $xTexto = $margin + $qrSize + 4;
            $larguraTexto = $w - $qrSize - 4;

            $qrPath = $this->gerarQrCodeTemp($s['qr_payload'] ?? '');
            if ($qrPath && is_file($qrPath)) {
                try {
                    $pdf->Image($qrPath, $xQr, $yInicio, $qrSize, $qrSize);
                } catch (\Throwable $e) {
                }
                @unlink($qrPath);
            }

            $pdf->SetFont('Helvetica', '', 7);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY($xTexto, $yInicio);
            $nome = $this->paraPdf(mb_strtoupper($s['nome'] ?? '-', 'UTF-8'));
            $linha1 = $this->paraPdf('Assinado digitalmente por: ') . $nome;
            $pdf->MultiCell($larguraTexto, 4, $this->truncar($linha1, 85), 0, 'L');
            $pdf->SetX($xTexto);
            $cpfPdf = $this->paraPdf((string) ($s['cpf'] ?? '-'));
            $pdf->Cell($larguraTexto, 4, $this->paraPdf('CPF: ') . $cpfPdf, 0, 1, 'L');
            $pdf->SetX($xTexto);
            $pdf->Cell($larguraTexto, 4, $this->paraPdf((string) ($s['data_local_br'] ?? '-')), 0, 1, 'L');
            $pdf->SetX($xTexto);
            $localAssinatura = (string) ($s['local_assinatura'] ?? '');
            if ($localAssinatura !== '') {
                $pdf->Cell($larguraTexto, 4, $this->paraPdf('Local (por IP): ') . $this->paraPdf($localAssinatura), 0, 1, 'L');
                $pdf->SetX($xTexto);
            }
            $pdf->Cell($larguraTexto, 4, $this->paraPdf('Horario de Brasilia (ISO): ') . $this->paraPdf((string) ($s['timestamp_brasilia_iso'] ?? $s['gmt_timestamp'] ?? '-')), 0, 1, 'L');
            $pdf->SetX($xTexto);
            $hash = $s['hash_evidencia'] ?? '';
            $pdf->Cell($larguraTexto, 4, $this->paraPdf('Hash da evidencia (SHA-256): ') . ($hash ?: $this->paraPdf('-')), 0, 1, 'L');
            $pdf->SetX($xTexto);
            $ip = $this->paraPdf((string) ($s['ip'] ?? '-'));
            $ua = $this->paraPdf($this->truncar((string) ($s['user_agent'] ?? '-'), 60));
            $identif = $this->paraPdf('Identificacao: IP: ') . $ip . $this->paraPdf('; Navegador: ') . $ua;
            $pdf->MultiCell($larguraTexto, 4, $identif, 0, 'L');

            $yLinha = $pdf->GetY() + 2;
            $pdf->Line($xTexto, $yLinha, 210 - $margin, $yLinha);
            $pdf->SetXY($xTexto, $yLinha + 2);
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->Cell($larguraTexto, 6, $nome, 0, 1, 'L');
            $pdf->Ln(6);
        }

        // ---- Linha separadora e título HISTÓRICO ----
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.3);
        $pdf->Line($margin, $pdf->GetY(), 210 - $margin, $pdf->GetY());
        $pdf->Ln(6);
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell(0, 8, $this->paraPdf('HISTORICO'), 0, 1, 'L');
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Line($margin, $pdf->GetY() + 1, $margin + 30, $pdf->GetY() + 1);
        $pdf->Ln(8);

        $eventos = $dadosPagina['eventos'] ?? [];
        $pdf->SetFont('Helvetica', '', 9);
        foreach ($eventos as $ev) {
            $dataFormatada = $this->paraPdf((string) ($ev['data_formatada'] ?? ''));
            $hora = $this->paraPdf((string) ($ev['hora'] ?? ''));
            $descricao = $this->paraPdf((string) ($ev['descricao'] ?? ''));
            $yLinha = $pdf->GetY();
            $colData = 48;
            $colDesc = $w - $colData;

            $pdf->SetXY($margin, $yLinha);
            $pdf->MultiCell($colData, 5, $dataFormatada . "\n" . $hora, 0, 'L');
            $yData = $pdf->GetY();

            $pdf->SetXY($margin + $colData, $yLinha);
            $pdf->MultiCell($colDesc, 5, $descricao, 0, 'L');
            $yDesc = $pdf->GetY();

            $pdf->SetY(max($yData, $yDesc) + 2);
        }

        $pdf->Ln(4);
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetTextColor(120, 120, 120);
        $dataGeracao = $this->paraPdf((string) ($dadosPagina['data_geracao_ptbr'] ?? ''));
        $rodape = $dataGeracao !== ''
            ? $this->paraPdf('Documento gerado eletronicamente em ') . $dataGeracao . $this->paraPdf('. Validade juridica conforme Lei 14.063/2020 e MP 2.200-2/2001. Para verificar a autenticidade, consulte o identificador e os eventos no sistema BPIN by MyBP.')
            : $this->paraPdf('Documento gerado eletronicamente. Validade juridica conforme Lei 14.063/2020 e MP 2.200-2/2001. Para verificar a autenticidade, consulte o identificador e os eventos no sistema BPIN by MyBP.');
        $pdf->MultiCell(0, 4, $rodape, 0, 'L');
    }

    /**
     * Gera QR code com o payload (URL de verificação) e retorna path do arquivo PNG temporário.
     * Retorna vazio se a lib endroid/qr-code não estiver instalada.
     */
    protected function gerarQrCodeTemp(string $payload): string
    {
        if (empty($payload) || !class_exists(\Endroid\QrCode\QrCode::class)) {
            return '';
        }
        try {
            $qr = \Endroid\QrCode\QrCode::create($payload);
            if (method_exists($qr, 'setSize')) {
                $qr->setSize(200);
            }
            if (method_exists($qr, 'setMargin')) {
                $qr->setMargin(5);
            }
            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $result = $writer->write($qr);
            $path = sys_get_temp_dir() . '/qr_' . uniqid('', true) . '.png';
            $string = $result->getString();
            if ($string !== null && file_put_contents($path, $string) !== false) {
                return $path;
            }
        } catch (\Throwable $e) {
            Log::warning('PdfMarcaAssinaturaService: QR code falhou', ['message' => $e->getMessage()]);
        }
        return '';
    }

    protected function truncar(string $s, int $max): string
    {
        $s = trim($s);
        if (strlen($s) <= $max) {
            return $s;
        }
        return substr($s, 0, $max - 3) . '...';
    }
}
