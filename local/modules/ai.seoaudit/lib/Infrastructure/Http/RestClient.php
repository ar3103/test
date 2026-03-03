<?php

namespace Ai\SeoAudit\Infrastructure\Http;

use Bitrix\Main\Web\HttpClient;

final class RestClient
{
    public function get(string $url, array $headers = [], array $query = []): array
    {
        $http = new HttpClient(['socketTimeout' => 20, 'streamTimeout' => 20]);
        foreach ($headers as $name => $value) {
            $http->setHeader((string) $name, (string) $value);
        }

        if (!empty($query)) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
        }

        $body = (string) $http->get($url);
        return ['status' => $http->getStatus(), 'body' => $body];
    }

    public function post(string $url, array $headers = [], array $data = []): array
    {
        $http = new HttpClient(['socketTimeout' => 20, 'streamTimeout' => 20]);
        foreach ($headers as $name => $value) {
            $http->setHeader((string) $name, (string) $value);
        }

        $body = (string) $http->post($url, json_encode($data, JSON_UNESCAPED_UNICODE));
        return ['status' => $http->getStatus(), 'body' => $body];
    }
}
