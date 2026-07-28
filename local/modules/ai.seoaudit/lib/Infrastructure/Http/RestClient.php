<?php

namespace Ai\SeoAudit\Infrastructure\Http;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Web\HttpClient;

final class RestClient
{
    private const MODULE_ID = 'ai.seoaudit';

    public function get(string $url, array $headers = [], array $query = [], ?string $circuitKey = null): array
    {
        $requestUrl = $url;
        if (!empty($query)) {
            $requestUrl .= (str_contains($requestUrl, '?') ? '&' : '?') . http_build_query($query);
        }

        return $this->requestWithRetry('GET', $requestUrl, $headers, [], $circuitKey ?? $url);
    }

    public function post(string $url, array $headers = [], array $data = [], ?string $circuitKey = null): array
    {
        return $this->requestWithRetry('POST', $url, $headers, $data, $circuitKey ?? $url);
    }

    private function requestWithRetry(string $method, string $url, array $headers, array $data, string $circuitKey): array
    {
        if ($this->isCircuitOpen($circuitKey)) {
            return ['status' => 0, 'body' => '', 'error' => 'circuit_open'];
        }

        $attempts = 3;
        $last = ['status' => 0, 'body' => '', 'error' => 'unknown'];

        for ($i = 1; $i <= $attempts; $i++) {
            $http = new HttpClient(['socketTimeout' => 20, 'streamTimeout' => 20]);
            foreach ($headers as $name => $value) {
                $http->setHeader((string) $name, (string) $value);
            }

            $body = '';
            if ($method === 'POST') {
                $body = (string) $http->post($url, json_encode($data, JSON_UNESCAPED_UNICODE));
            } else {
                $body = (string) $http->get($url);
            }

            $status = (int) $http->getStatus();
            $last = ['status' => $status, 'body' => $body];

            if ($status >= 200 && $status < 500 && $status !== 429) {
                $this->resetFailure($circuitKey);
                return $last;
            }

            usleep(200000 * $i);
        }

        $this->registerFailure($circuitKey);
        $last['error'] = 'request_failed_after_retry';
        return $last;
    }

    private function failureKey(string $circuitKey): string
    {
        return 'http_fail_' . md5($circuitKey);
    }

    private function openUntilKey(string $circuitKey): string
    {
        return 'http_open_until_' . md5($circuitKey);
    }

    private function isCircuitOpen(string $circuitKey): bool
    {
        $openUntil = (int) Option::get(self::MODULE_ID, $this->openUntilKey($circuitKey), '0');
        return $openUntil > time();
    }

    private function registerFailure(string $circuitKey): void
    {
        $fails = (int) Option::get(self::MODULE_ID, $this->failureKey($circuitKey), '0') + 1;
        Option::set(self::MODULE_ID, $this->failureKey($circuitKey), (string) $fails);

        if ($fails >= 3) {
            Option::set(self::MODULE_ID, $this->openUntilKey($circuitKey), (string) (time() + 120));
            Option::set(self::MODULE_ID, $this->failureKey($circuitKey), '0');
        }
    }

    private function resetFailure(string $circuitKey): void
    {
        Option::delete(self::MODULE_ID, ['name' => $this->failureKey($circuitKey)]);
        Option::delete(self::MODULE_ID, ['name' => $this->openUntilKey($circuitKey)]);
    }
}
