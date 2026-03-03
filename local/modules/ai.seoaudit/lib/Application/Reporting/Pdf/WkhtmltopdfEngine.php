<?php

namespace Ai\SeoAudit\Application\Reporting\Pdf;

final class WkhtmltopdfEngine implements PdfEngineInterface
{
    public function __construct(private readonly string $binary = 'wkhtmltopdf')
    {
    }

    public function renderFromHtml(string $html): string
    {
        $tmpHtml = tempnam(sys_get_temp_dir(), 'seo_html_') . '.html';
        $tmpPdf = tempnam(sys_get_temp_dir(), 'seo_pdf_') . '.pdf';
        file_put_contents($tmpHtml, $html);

        $cmd = sprintf('%s %s %s 2>/dev/null', escapeshellcmd($this->binary), escapeshellarg($tmpHtml), escapeshellarg($tmpPdf));
        exec($cmd, $output, $code);

        $pdf = ($code === 0 && file_exists($tmpPdf)) ? (string) file_get_contents($tmpPdf) : '';
        @unlink($tmpHtml);
        @unlink($tmpPdf);

        return $pdf;
    }
}
