<?php

namespace Ai\SeoAudit\Application\Reporting;

use Ai\SeoAudit\Model\ReportTable;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Type\DateTime;

final class ReportService
{
    public static function generate(int $tenantId, int $projectId, array $data): array
    {
        $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/upload/ai_seoaudit/reports';
        Directory::createDirectory($baseDir);

        $stamp = date('Ymd_His');
        $htmlPath = '/upload/ai_seoaudit/reports/report_' . $projectId . '_' . $stamp . '.html';
        $pdfPath = '/upload/ai_seoaudit/reports/report_' . $projectId . '_' . $stamp . '.pdf';

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . $htmlPath, HtmlReportGenerator::generate($data));
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . $pdfPath, PdfReportGenerator::generateSimplePdf('SEO report for project ' . $projectId));

        ReportTable::add([
            'TENANT_ID' => $tenantId,
            'PROJECT_ID' => $projectId,
            'TYPE' => 'seo_audit',
            'FORMAT' => 'html',
            'PATH' => $htmlPath,
            'CREATED_AT' => new DateTime(),
        ]);

        ReportTable::add([
            'TENANT_ID' => $tenantId,
            'PROJECT_ID' => $projectId,
            'TYPE' => 'seo_audit',
            'FORMAT' => 'pdf',
            'PATH' => $pdfPath,
            'CREATED_AT' => new DateTime(),
        ]);

        return ['html' => $htmlPath, 'pdf' => $pdfPath];
    }
}
