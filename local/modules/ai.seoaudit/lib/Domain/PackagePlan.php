<?php

namespace Ai\SeoAudit\Domain;

final class PackagePlan
{
    public function __construct(
        private readonly string $code,
        private readonly string $name,
        private readonly array $features,
        private readonly array $limits = []
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFeatures(): array
    {
        return $this->features;
    }

    public function getLimits(): array
    {
        return $this->limits;
    }
}
