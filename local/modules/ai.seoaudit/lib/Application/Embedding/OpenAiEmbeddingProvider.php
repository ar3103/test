<?php

namespace Ai\SeoAudit\Application\Embedding;

use Ai\SeoAudit\Infrastructure\Http\RestClient;

final class OpenAiEmbeddingProvider implements EmbeddingProviderInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'text-embedding-3-small',
        private readonly ?RestClient $restClient = null
    ) {
    }

    public function embed(string $text): array
    {
        if ($this->apiKey === '') {
            return [];
        }

        $response = ($this->restClient ?? new RestClient())->post(
            'https://api.openai.com/v1/embeddings',
            [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            ['input' => $text, 'model' => $this->model],
            'openai_embeddings'
        );

        $raw = json_decode($response['body'], true) ?: [];
        $vec = $raw['data'][0]['embedding'] ?? [];
        return is_array($vec) ? $vec : [];
    }
}
