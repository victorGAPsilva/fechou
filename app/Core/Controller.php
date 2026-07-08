<?php

declare(strict_types=1);

namespace App\Core;

class Controller
{
    protected function render(string $view, array $data = [], ?string $layout = 'layouts/app'): void
    {
        View::render($view, $data, $layout);
    }

    protected function redirect(string $path): void
    {
        redirect($path);
    }

    protected function back(): void
    {
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('/')));
        exit;
    }

    protected function downloadCsv(string $filename, array $headers, array $rows): void
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        if ($output === false) {
            exit;
        }

        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers, ';');

        foreach ($rows as $row) {
            fputcsv($output, $row, ';');
        }

        fclose($output);
        exit;
    }
}
