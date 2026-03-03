<?php

namespace Ai\SeoAudit\Application\Reporting\Pdf;

interface PdfEngineInterface
{
    public function renderFromHtml(string $html): string;
}
