<?php

namespace Ai\SeoAudit\Infrastructure\Api\Contracts;

interface LlmProviderInterface
{
    public function getCode(): string;

    public function isConfigured(): bool;

    public function generateMetaTags(string $content, array $keywords): array;
}
