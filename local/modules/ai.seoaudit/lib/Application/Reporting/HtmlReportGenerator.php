<?php

namespace Ai\SeoAudit\Application\Reporting;

final class HtmlReportGenerator
{
    public static function generate(array $data): string
    {
        $rows = '';
        foreach ($data as $k => $v) {
            $rows .= '<tr><td>' . htmlspecialchars((string) $k) . '</td><td>' . htmlspecialchars((string) (is_scalar($v) ? $v : json_encode($v, JSON_UNESCAPED_UNICODE))) . '</td></tr>';
        }

        return '<html><head><meta charset="utf-8"><title>SEO Report</title></head><body>'
            . '<h1>SEO Audit Report</h1><table border="1" cellpadding="6" cellspacing="0">'
            . $rows
            . '</table></body></html>';
    }
}
