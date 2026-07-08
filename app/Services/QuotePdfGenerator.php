<?php

declare(strict_types=1);

namespace App\Services;

final class QuotePdfGenerator
{
    private const PAGE_WIDTH = 595.28;
    private const PAGE_HEIGHT = 841.89;
    private const MARGIN = 42.0;

    private array $pages = [];
    private array $style = [];
    private string $content = '';
    private float $y = 0.0;

    public function render(array $quote, array $items, ?string $template = null): string
    {
        $this->pages = [];
        $this->style = $this->styleFor($template ?: (string) ($quote['template_key'] ?? 'modern'));
        $this->newPage();

        $this->header($quote);
        $this->clientBlock($quote);
        $this->sectionTitle('Itens');
        $this->itemsTable($items);
        $this->summary($quote);
        $this->conditions($quote);
        $this->footer();
        $this->finishPage();

        return $this->buildPdf();
    }

    private function styleFor(string $template): array
    {
        $styles = [
            'modern' => [
                'key' => 'modern',
                'name' => 'Moderno',
                'font' => 'F1',
                'bold' => 'F2',
                'text' => [0.08, 0.08, 0.09],
                'muted' => [0.42, 0.42, 0.45],
                'accent' => [0.86, 0.60, 0.18],
                'accentText' => [0.08, 0.07, 0.04],
                'header' => [0.07, 0.08, 0.11],
                'surface' => [0.96, 0.95, 0.92],
                'line' => [0.84, 0.82, 0.78],
                'tableHeader' => [0.86, 0.60, 0.18],
                'tableHeaderText' => [0.08, 0.07, 0.04],
            ],
            'minimal' => [
                'key' => 'minimal',
                'name' => 'Minimalista',
                'font' => 'F1',
                'bold' => 'F2',
                'text' => [0.10, 0.10, 0.10],
                'muted' => [0.45, 0.45, 0.45],
                'accent' => [0.18, 0.18, 0.18],
                'accentText' => [1, 1, 1],
                'header' => [1, 1, 1],
                'surface' => [0.98, 0.98, 0.98],
                'line' => [0.74, 0.74, 0.74],
                'tableHeader' => [0.96, 0.96, 0.96],
                'tableHeaderText' => [0.10, 0.10, 0.10],
            ],
            'executive' => [
                'key' => 'executive',
                'name' => 'Executivo',
                'font' => 'F1',
                'bold' => 'F2',
                'text' => [0.08, 0.10, 0.16],
                'muted' => [0.38, 0.43, 0.50],
                'accent' => [0.18, 0.36, 0.58],
                'accentText' => [1, 1, 1],
                'header' => [0.06, 0.12, 0.22],
                'surface' => [0.93, 0.95, 0.98],
                'line' => [0.72, 0.78, 0.86],
                'tableHeader' => [0.18, 0.36, 0.58],
                'tableHeaderText' => [1, 1, 1],
            ],
            'premium' => [
                'key' => 'premium',
                'name' => 'Premium',
                'font' => 'F1',
                'bold' => 'F2',
                'text' => [0.10, 0.08, 0.05],
                'muted' => [0.48, 0.42, 0.34],
                'accent' => [0.76, 0.48, 0.12],
                'accentText' => [0.07, 0.05, 0.02],
                'header' => [0.12, 0.09, 0.05],
                'surface' => [0.98, 0.94, 0.84],
                'line' => [0.76, 0.61, 0.38],
                'tableHeader' => [0.12, 0.09, 0.05],
                'tableHeaderText' => [0.96, 0.86, 0.62],
            ],
        ];

        return $styles[$template] ?? $styles['modern'];
    }

    private function drawBackground(): void
    {
        if ($this->style['key'] === 'minimal') {
            return;
        }

        if ($this->style['key'] === 'executive') {
            $this->rect(0, 0, 26, self::PAGE_HEIGHT, ...$this->style['header']);
            return;
        }

        if ($this->style['key'] === 'premium') {
            $this->rect(0, 0, self::PAGE_WIDTH, self::PAGE_HEIGHT, 0.99, 0.97, 0.91);
            $this->rect(0, 0, 12, self::PAGE_HEIGHT, ...$this->style['accent']);
        }
    }

    private function header(array $quote): void
    {
        $key = $this->style['key'];

        if ($key === 'minimal') {
            $this->y = 790;
            $this->textAt('FECHOU', self::MARGIN, 800, 11, true, ...$this->style['text']);
            $this->textAt('ORÇAMENTO', 460, 800, 10, true, ...$this->style['muted']);
            $this->line(self::MARGIN, 784, self::PAGE_WIDTH - self::MARGIN, 784, ...$this->style['line']);
        } elseif ($key === 'executive') {
            $this->rect(26, 760, self::PAGE_WIDTH - 26, 82, ...$this->style['header']);
            $this->textAt('Fechou', self::MARGIN, 812, 18, true, 1, 1, 1);
            $this->textAt('Proposta executiva', self::MARGIN, 792, 9, false, 0.78, 0.84, 0.92);
            $this->textAt((string) $quote['quote_number'], 420, 812, 16, true, 1, 1, 1);
            $this->textAt(date('d/m/Y'), 420, 792, 9, false, 0.78, 0.84, 0.92);
            $this->y = 730;
        } elseif ($key === 'premium') {
            $this->rect(32, 760, 531, 58, ...$this->style['header']);
            $this->line(42, 748, 553, 748, ...$this->style['accent']);
            $this->textAt('Fechou Premium', self::MARGIN, 794, 17, true, 0.96, 0.86, 0.62);
            $this->textAt((string) $quote['quote_number'], 426, 794, 14, true, 0.96, 0.86, 0.62);
            $this->textAt('Orçamento com apresentação comercial', self::MARGIN, 776, 8, false, 0.95, 0.90, 0.78);
            $this->y = 726;
        } else {
            $this->rect(0, 772, self::PAGE_WIDTH, 70, ...$this->style['header']);
            $this->textAt('Fechou', self::MARGIN, 810, 18, true, ...$this->style['accent']);
            $this->textAt('Orçamento profissional', self::MARGIN, 792, 9, false, 1, 1, 1);
            $this->textAt((string) $quote['quote_number'], 430, 810, 15, true, 1, 1, 1);
            $this->textAt(date('d/m/Y'), 430, 792, 9, false, 1, 1, 1);
            $this->y = 742;
        }

        $this->text((string) ($quote['title'] ?? 'Orçamento'), $key === 'minimal' ? 18 : 20, true);
        $this->text('Modelo: ' . $this->style['name'] . '    Validade: ' . (string) ($quote['validity_days'] ?? 0) . ' dias', 9, false, ...$this->style['muted']);
        $this->space(16);
    }

    private function clientBlock(array $quote): void
    {
        if ($this->style['key'] === 'premium') {
            $this->ensureSpace(70);
            $this->rect(self::MARGIN, $this->y - 46, self::PAGE_WIDTH - (self::MARGIN * 2), 52, ...$this->style['surface']);
            $this->textAt('CLIENTE', self::MARGIN + 14, $this->y - 10, 8, true, ...$this->style['accent']);
            $this->textAt((string) ($quote['client_name'] ?? '-'), self::MARGIN + 14, $this->y - 28, 13, true, ...$this->style['text']);
            $this->textAt('Status: ' . mb_strtoupper((string) ($quote['status'] ?? 'draft')), 395, $this->y - 28, 9, false, ...$this->style['muted']);
            $this->y -= 68;
            return;
        }

        $this->sectionTitle('Cliente');
        $this->text((string) ($quote['client_name'] ?? '-'), 12, true);
        $this->text('Orçamento: ' . (string) $quote['quote_number'] . '    Status: ' . mb_strtoupper((string) ($quote['status'] ?? 'draft')), 9, false, ...$this->style['muted']);
        $this->space(12);
    }

    private function itemsTable(array $items): void
    {
        $this->ensureSpace(60);
        $x = self::MARGIN;
        $width = self::PAGE_WIDTH - (self::MARGIN * 2);
        $headerY = $this->y - 18;

        if ($this->style['key'] !== 'minimal') {
            $this->rect($x, $headerY, $width, 24, ...$this->style['tableHeader']);
        } else {
            $this->line($x, $headerY + 20, $x + $width, $headerY + 20, ...$this->style['line']);
            $this->line($x, $headerY, $x + $width, $headerY, ...$this->style['line']);
        }

        [$tr, $tg, $tb] = $this->style['tableHeaderText'];
        $this->textAt('Descrição', $x + 8, $this->y - 9, 9, true, $tr, $tg, $tb);
        $this->textAt('Qtd.', $x + 282, $this->y - 9, 9, true, $tr, $tg, $tb);
        $this->textAt('Unitário', $x + 332, $this->y - 9, 9, true, $tr, $tg, $tb);
        $this->textAt('Desc.', $x + 410, $this->y - 9, 9, true, $tr, $tg, $tb);
        $this->textAt('Total', $x + 466, $this->y - 9, 9, true, $tr, $tg, $tb);
        $this->y -= 32;

        foreach ($items as $item) {
            $descriptionLines = $this->wrap((string) ($item['description'] ?? '-'), 48);
            $rowHeight = max(24, count($descriptionLines) * 12 + 12);
            $this->ensureSpace($rowHeight + 8);

            $rowTop = $this->y + 6;
            $this->line($x, $rowTop, $x + $width, $rowTop, ...$this->style['line']);

            $lineY = $this->y - 7;
            foreach ($descriptionLines as $line) {
                $this->textAt($line, $x + 8, $lineY, 9, false, ...$this->style['text']);
                $lineY -= 12;
            }

            $this->textAt($this->number((float) ($item['quantity'] ?? 0)), $x + 282, $this->y - 7, 9, false, ...$this->style['text']);
            $this->textAt($this->money((float) ($item['unit_price'] ?? 0)), $x + 332, $this->y - 7, 9, false, ...$this->style['text']);
            $this->textAt($this->money((float) ($item['discount'] ?? 0)), $x + 410, $this->y - 7, 9, false, ...$this->style['text']);
            $this->textAt($this->money((float) ($item['total'] ?? 0)), $x + 466, $this->y - 7, 9, true, ...$this->style['text']);

            $this->y -= $rowHeight;
        }

        if ($items === []) {
            $this->text('Nenhum item cadastrado.', 10);
        }
    }

    private function summary(array $quote): void
    {
        $this->ensureSpace(96);
        $x = $this->style['key'] === 'premium' ? 330.0 : 350.0;

        if ($this->style['key'] === 'premium') {
            $this->rect($x - 14, $this->y - 78, 225, 90, 0.12, 0.09, 0.05);
            $labelColor = [0.96, 0.86, 0.62];
            $valueColor = [1, 1, 1];
        } else {
            $this->line($x, $this->y, 553, $this->y, ...$this->style['line']);
            $labelColor = $this->style['text'];
            $valueColor = $this->style['text'];
        }

        $this->y -= 18;
        $this->summaryLine('Subtotal', (float) ($quote['subtotal_total'] ?? 0), $x, false, $labelColor, $valueColor);
        $this->summaryLine('Frete', (float) ($quote['shipping_total'] ?? 0), $x, false, $labelColor, $valueColor);
        $this->summaryLine('Desconto', (float) ($quote['discount_total'] ?? 0), $x, false, $labelColor, $valueColor);
        $this->space(2);
        $this->summaryLine('Total', (float) ($quote['total'] ?? 0), $x, true, $labelColor, $valueColor);
        $this->space(8);
    }

    private function summaryLine(string $label, float $value, float $x, bool $strong = false, ?array $labelColor = null, ?array $valueColor = null): void
    {
        $labelColor ??= $this->style['text'];
        $valueColor ??= $this->style['text'];
        $this->textAt($label, $x, $this->y, 10, $strong, ...$labelColor);
        $this->textAt($this->money($value), 465, $this->y, 10, $strong, ...$valueColor);
        $this->y -= 16;
    }

    private function conditions(array $quote): void
    {
        if (empty($quote['payment_terms']) && empty($quote['warranty']) && empty($quote['notes'])) {
            return;
        }

        $this->space(6);
        $this->sectionTitle('Condições');
        $this->paragraph('Pagamento: ' . ((string) ($quote['payment_terms'] ?? '') ?: '-'));
        $this->paragraph('Garantia: ' . ((string) ($quote['warranty'] ?? '') ?: '-'));

        if (!empty($quote['notes'])) {
            $this->paragraph('Observações: ' . (string) $quote['notes']);
        }
    }

    private function sectionTitle(string $title): void
    {
        $this->ensureSpace(28);

        if ($this->style['key'] === 'executive') {
            $this->text($title, 11, true, ...$this->style['accent']);
            $this->line(self::MARGIN, $this->y + 4, self::PAGE_WIDTH - self::MARGIN, $this->y + 4, ...$this->style['line']);
        } elseif ($this->style['key'] === 'minimal') {
            $this->text($title, 10, true, ...$this->style['text']);
            $this->line(self::MARGIN, $this->y + 5, self::PAGE_WIDTH - self::MARGIN, $this->y + 5, ...$this->style['line']);
        } else {
            $this->text($title, 12, true, ...$this->style['accent']);
            $this->line(self::MARGIN, $this->y + 4, self::PAGE_WIDTH - self::MARGIN, $this->y + 4, ...$this->style['line']);
        }

        $this->space(6);
    }

    private function paragraph(string $text): void
    {
        foreach ($this->wrap($text, 95) as $line) {
            $this->text($line, 9, false, ...$this->style['text']);
        }

        $this->space(4);
    }

    private function footer(): void
    {
        $label = 'Gerado pelo Fechou • Modelo ' . $this->style['name'];
        $this->textAt($label, self::MARGIN, 28, 8, false, ...$this->style['muted']);
    }

    private function newPage(): void
    {
        $this->content = '';
        $this->y = self::PAGE_HEIGHT - self::MARGIN;
        if ($this->style !== []) {
            $this->drawBackground();
        }
    }

    private function finishPage(): void
    {
        $this->pages[] = $this->content;
    }

    private function ensureSpace(float $needed): void
    {
        if ($this->y - $needed > 58) {
            return;
        }

        $this->footer();
        $this->finishPage();
        $this->newPage();
        $this->y = self::PAGE_HEIGHT - self::MARGIN;
    }

    private function text(string $text, int $size = 10, bool $bold = false, float $r = 0.08, float $g = 0.08, float $b = 0.08): void
    {
        $this->ensureSpace($size + 8);
        $this->textAt($text, self::MARGIN, $this->y, $size, $bold, $r, $g, $b);
        $this->y -= $size + 6;
    }

    private function textAt(string $text, float $x, float $y, int $size = 10, bool $bold = false, float $r = 0.08, float $g = 0.08, float $b = 0.08): void
    {
        $font = $bold ? $this->style['bold'] : $this->style['font'];
        $this->content .= sprintf(
            "BT %.3F %.3F %.3F rg /%s %d Tf %.2F %.2F Td (%s) Tj ET\n",
            $r,
            $g,
            $b,
            $font,
            $size,
            $x,
            $y,
            $this->escape($text)
        );
    }

    private function rect(float $x, float $y, float $w, float $h, float $r, float $g, float $b): void
    {
        $this->content .= sprintf("%.3F %.3F %.3F rg %.2F %.2F %.2F %.2F re f\n", $r, $g, $b, $x, $y, $w, $h);
    }

    private function line(float $x1, float $y1, float $x2, float $y2, float $r, float $g, float $b): void
    {
        $this->content .= sprintf("%.3F %.3F %.3F RG 0.7 w %.2F %.2F m %.2F %.2F l S\n", $r, $g, $b, $x1, $y1, $x2, $y2);
    }

    private function space(float $height): void
    {
        $this->y -= $height;
    }

    private function wrap(string $text, int $maxChars): array
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');

        if ($text === '') {
            return ['-'];
        }

        return explode("\n", wordwrap($text, $maxChars, "\n", true));
    }

    private function money(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    private function number(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, ',', '.'), '0'), ',');
    }

    private function escape(string $text): string
    {
        $encoded = $this->ascii($text);

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encoded);
    }

    private function ascii(string $text): string
    {
        $map = [
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'Ç' => 'C', 'ç' => 'c',
            'Ñ' => 'N', 'ñ' => 'n',
            '–' => '-', '—' => '-', '•' => '-',
            '“' => '"', '”' => '"', '‘' => "'", '’' => "'",
        ];

        $text = strtr($text, $map);
        $encoded = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);

        return $encoded === false ? preg_replace('/[^\x20-\x7E]/', '', $text) ?? '' : $encoded;
    }

    private function buildPdf(): string
    {
        $objects = [];
        $pageIds = [];

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';

        foreach ($this->pages as $index => $content) {
            $contentId = 5 + ($index * 2);
            $pageId = $contentId + 1;
            $pageIds[] = $pageId . ' 0 R';
            $objects[$contentId] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "endstream";
            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . self::PAGE_WIDTH . ' ' . self::PAGE_HEIGHT . '] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ' . $contentId . ' 0 R >>';
        }

        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $pageIds) . '] /Count ' . count($pageIds) . ' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];

        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $count = max(array_keys($objects)) + 1;
        $pdf .= "xref\n0 " . $count . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i < $count; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
        }

        $pdf .= "trailer\n<< /Size " . $count . " /Root 1 0 R >>\nstartxref\n" . $xrefOffset . "\n%%EOF";

        return $pdf;
    }
}
