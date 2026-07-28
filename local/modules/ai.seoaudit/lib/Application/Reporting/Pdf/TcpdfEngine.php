<?php

namespace Ai\SeoAudit\Application\Reporting\Pdf;

final class TcpdfEngine implements PdfEngineInterface
{
    public function renderFromHtml(string $html): string
    {
        if (!class_exists('TCPDF')) {
            return '';
        }

        $pdf = new \TCPDF();
        $pdf->AddPage();
        $pdf->writeHTML($html);

        return $pdf->Output('', 'S');
    }
}
