<?php

namespace App\Helpers;

use Illuminate\Http\Response;

class ExcelHelper
{
    public static function downloadTable(string $filename, string $titulo, array $headers, array $rows): Response
    {
        $logos = LogoHelper::getBase64Logos();
        $html = view('exports.tabla', array_merge($logos, [
            'titulo' => $titulo,
            'headers' => $headers,
            'rows' => $rows,
        ]))->render();

        $safeFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename);
        $exportName = $safeFilename . '_' . date('Y-m-d') . '.xls';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $exportName . '"',
        ]);
    }
}
