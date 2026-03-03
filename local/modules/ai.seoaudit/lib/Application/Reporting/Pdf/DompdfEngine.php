<?php

namespace Ai\SeoAudit\Application\Reporting\Pdf;

final class DompdfEngine implements PdfEngineInterface
{
    public function renderFromHtml(string $html): string
    {
        if (!class_exists('Dompdf\\Dompdf')) {
            return '';
        }

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
