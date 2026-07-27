<?php

declare(strict_types=1);

namespace Company\ReviewSync\Provider;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Web\HttpClient;

abstract class AbstractHttpProvider implements ReviewProviderInterface
{
    protected HttpClient $httpClient;

    public function __construct()
    {
        $this->httpClient = new HttpClient([
            'socketTimeout' => 10,
            'streamTimeout' => 10,
            'disableSslVerification' => false,
        ]);
        $this->httpClient->setHeader('Accept', 'application/json', true);
    }

    /** @return array<mixed> */
    protected function getJson(string $url, array $headers = []): array
    {
        foreach ($headers as $name => $value) {
            $this->httpClient->setHeader((string) $name, (string) $value, true);
        }

        $response = $this->httpClient->get($url);
        if ($response === false || $response === '') {
            Debug::writeToFile(['url' => $url, 'error' => $this->httpClient->getError()], 'reviewsync_http_error', '/upload/logs/reviewsync.log');
            return [];
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            Debug::writeToFile(['url' => $url, 'response' => $response], 'reviewsync_json_error', '/upload/logs/reviewsync.log');
            return [];
        }

        return $decoded;
    }
}
