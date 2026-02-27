<?php

declare(strict_types=1);

namespace Company\ReviewSync\Providers;

use Bitrix\Main\Web\HttpClient;
use Company\ReviewSync\Config;
use Company\ReviewSync\Domain\ReviewDto;

abstract class AbstractHttpProvider implements ProviderInterface
{
    /** @return ReviewDto[] */
    public function fetch(int $limit): array
    {
        $config = Config::getProviderConfig($this->getCode());
        if (empty($config['endpoint']) || empty($config['api_key']) || empty($config['place_id'])) {
            return [];
        }

        $client = new HttpClient([
            'socketTimeout' => 10,
            'streamTimeout' => 10,
            'disableSslVerification' => false,
        ]);

        $url = sprintf('%s?place_id=%s&limit=%d', $config['endpoint'], urlencode($config['place_id']), $limit);
        $client->setHeader('Authorization', 'Bearer ' . $config['api_key']);
        $raw = $client->get($url);

        if (!$raw) {
            return [];
        }

        $response = json_decode($raw, true);
        if (!is_array($response) || !isset($response['reviews']) || !is_array($response['reviews'])) {
            return [];
        }

        $result = [];
        foreach ($response['reviews'] as $item) {
            if (count($result) >= $limit) {
                break;
            }

            $dto = $this->mapReview($item);
            if ($dto instanceof ReviewDto) {
                $result[] = $dto;
            }
        }

        return $result;
    }

    abstract protected function mapReview(array $item): ?ReviewDto;
}
