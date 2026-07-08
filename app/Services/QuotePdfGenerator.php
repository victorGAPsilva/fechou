<?php

declare(strict_types=1);

namespace App\Services;

final class QuotePdfGenerator
{
    private const PAGE_WIDTH = 595.28;
    private const PAGE_HEIGHT = 841.89;
    private const MARGIN = 42.0;

    private array $pages = [];
    private string $content = '';
    private float $y = 0.0;

    public function render(array $quote, array $items): string
    {
        $this->pages = [];
        $this->newPage();

        $this->header($quote);
        $this->sectionTitle('Cliente');
        $this->text((string) ($quote['client_name'] ?? '-'), 12, true);
        $this->text('Orçamento: ' . (string) $quote['quote_number'] . '    Status: ' . mb_strtoupper((string) ($quote['status'] ?? 'draft')), 9);
        $this->space(12);

        $this->sectionTitle('Itens');
        $this->itemsTable($items);

        $this->space(8);
        $this->summary($quote);

        if (!empty($quote['payment_terms']) || !empty($quote['warranty']) || !empty($quote['notes'])) {
            $this->space(10);
            $this->sectionTitle('Condições');
            $this->paragraph('Pagamento: ' . ((string) ($quote['payment_terms'] ?? '') ?: '-'));
            $this->paragraph('Garantia: ' . ((string) ($quote['warranty'] ?? '') ?: '-'));

            if (!empty($quote['notes'])) {
                $this->paragraph('Observações: ' . (string) $quote['notes']);
            }
        }

        $this->footer();
        $this->finishPage();

        return $this->buildPdf();
    }

    private function header(array $quote): void
    {
        $this->rect(0, 772, self::PAGE_WIDTH, 70, 0.08, 0.09, 0.12);
        $this->textAt('Fechou', self::MARGIN, 810, 18, true, 0.93, 0.76, 0.42);
        $this->textAt('Orçamento profissional', self::MARGIN, 792, 9, false, 1, 1, 1);
        $this->textAt((string) $quote['quote_number'], 430, 810, 15, true, 1, 1, 1);
        $this->textAt(date('d/m/Y'), 430, 792, 9, false, 1, 1, 1);

        $this->y = 742;
        $this->text((string) ($quote['title'] ?? 'Orçamento'), 20, true);
        $this->text('Validade: ' . (string) ($quote['validity_days'] ?? 0) . ' dias', 10);
        $this->space(16);
    }

    private function itemsTable(array $items): void
    {
        $this->ensureSpace(60);
        $x = self::MARGIN;
        $width = self::PAGE_WIDTH - (self::MARGIN * 2);

        $this->rect($x, $this->y - 18, $width, 24, 0.93, 0.76, 0.42);
        $this->textAt('Descrição', $x + 8, $this->y - 9, 9, true, 0.08, 0.07, 0.04);
        $this->textAt('Qtd.', $x + 282, $this->y - 9, 9, true, 0.08, 0.07, 0.04);
        $this->textAt('Unitário', $x + 332, $this->y - 9, 9, true, 0.08, 0.07, 0.04);
        $this->textAt('Desc.', $x + 410, $this->y - 9, 9, true, 0.08, 0.07, 0.04);
        $this->textAt('Total', $x + 466, $this->y - 9, 9, true, 0.08, 0.07, 0.04);
        $this->y -= 32;

        foreach ($items as $item) {
            $descriptionLines = $this->wrap((string) ($item['description'] ?? '-'), 48);
            $rowHeight = max(24, count($descriptionLines) * 12 + 12);
            $this->ensureSpace($rowHeight + 8);

            $rowTop = $this->y + 6;
            $this->line($x, $rowTop, $x + $width, $rowTop, 0.85, 0.85, 0.85);

            $lineY = $this->y - 7;
            foreach ($descriptionLines as $line) {
                $this->textAt($line, $x + 8, $lineY, 9);
                $lineY -= 12;
            }

            $this->textAt($this->number((float) ($item['quantity'] ?? 0)), $x + 282, $this->y - 7, 9);
            $this->textAt($this->money((float) ($item['unit_price'] ?? 0)), $x + 332, $this->y - 7, 9);
            $this->textAt($this->money((float) ($item['discount'] ?? 0)), $x + 410, $this->y - 7, 9);
            $this->textAt($this->money((float) ($item['total'] ?? 0)), $x + 466, $this->y - 7, 9, true);

            $this->y -= $rowHeight;
        }

        if ($items === []) {
            $this->text('Nenhum item cadastrado.', 10);
        }
    }

    private function summary(array $quote): void
    {
        $this->ensureSpace(92);
        $x = 350.0;
        $this->line($x, $this->y, 553, $this->y, 0.7, 0.7, 0.7);
        $this->y -= 18;

        $this->summaryLine('Subtotal', (float) ($quote['subtotal_total'] ?? 0), $x);
        $this->summaryLine('Frete', (float) ($quote['shipping_total'] ?? 0), $x);
        $this->summaryLine('Desconto', (float) ($quote['discount_total'] ?? 0), $x);
        $this->space(2);
        $this->summaryLine('Total', (float) ($quote['total'] ?? 0), $x, true);
    }

    private function summaryLine(string $label, float $value, float $x, bool $strong = false): void
    {
        $this->textAt($label, $x, $this->y, 10, $strong);
        $this->textAt($this->money($value), 465, $this->y, 10, $strong);
        $this->y -= 16;
    }

    private function sectionTitle(string $title): void
    {
        $this->ensureSpace(28);
        $this->text($title, 12, true, 0.66, 0.43, 0.12);
        $this->line(self::MARGIN, $this->y + 4, self::PAGE_WIDTH - self::MARGIN, $this->y + 4, 0.84, 0.84, 0.84);
        $this->space(6);
    }

    private function paragraph(string $text): void
    {
        foreach ($this->wrap($text, 95) as $line) {
            $this->text($line, 9);
        }

        $this->space(4);
    }

    private function footer(): void
    {
        $this->textAt('Gerado pelo Fechou', self::MARGIN, 28, 8, false, 0.45, 0.45, 0.45);
    }

    private function newPage(): void
    {
        $this->content = '';
        $this->y = self::PAGE_HEIGHT - self::MARGIN;
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
        $font = $bold ? 'F2' : 'F1';
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
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        $encoded = $encoded === false ? $text : $encoded;

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encoded);
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
