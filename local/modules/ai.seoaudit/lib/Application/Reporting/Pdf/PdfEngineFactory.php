<?php

namespace Ai\SeoAudit\Application\Reporting\Pdf;

use Bitrix\Main\Config\Option;

final class PdfEngineFactory
{
    private const MODULE_ID = 'ai.seoaudit';

    public static function make(): PdfEngineInterface
    {
        $engine = (string) Option::get(self::MODULE_ID, 'pdf_engine', 'wkhtmltopdf');

        return match ($engine) {
            'dompdf' => new DompdfEngine(),
            'tcpdf' => new TcpdfEngine(),
            default => new WkhtmltopdfEngine((string) Option::get(self::MODULE_ID, 'wkhtmltopdf_binary', 'wkhtmltopdf')),
        };
    }
}
