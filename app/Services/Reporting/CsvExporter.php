<?php

namespace App\Services\Reporting;

use Symfony\Component\HttpFoundation\StreamedResponse;

/** FR-RPT-006; NFR-DATA-04. Plain fputcsv — no new dependency needed for this. */
class CsvExporter
{
    /**
     * @param  array<int, string>  $headers
     * @param  iterable<int, array<int|string, mixed>>  $rows
     */
    public static function stream(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, array_map(self::sanitizeCell(...), array_values($row)));
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * A cell starting with =, +, -, or @ is prefixed with a single quote so
     * spreadsheet software opens it as text instead of evaluating it as a
     * formula (CSV/formula injection).
     */
    private static function sanitizeCell(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@]/', $value) === 1 ? "'".$value : $value;
    }
}
