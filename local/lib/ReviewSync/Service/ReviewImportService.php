<?php

declare(strict_types=1);

namespace Company\ReviewSync\Service;

use Bitrix\Main\Config\Option;
use Company\ReviewSync\Config;
use Company\ReviewSync\Infrastructure\IblockReviewRepository;
use Company\ReviewSync\Infrastructure\NotificationService;
use Company\ReviewSync\Infrastructure\QueueRepository;
use Company\ReviewSync\Providers\GoogleProvider;
use Company\ReviewSync\Providers\ProviderInterface;
use Company\ReviewSync\Providers\TwoGisProvider;
use Company\ReviewSync\Providers\YandexProvider;

final class ReviewImportService
{
    private const LOCK_OPTION = 'import_lock';

    /** @var ProviderInterface[] */
    private array $providers;

    public function __construct(
        private readonly QueueRepository $queue,
        private readonly IblockReviewRepository $reviewRepository,
        private readonly NotificationService $notificationService
    ) {
        $this->providers = [
            new YandexProvider(),
            new GoogleProvider(),
            new TwoGisProvider(),
        ];
    }

    public function execute(): void
    {
        if ($this->isLocked()) {
            return;
        }

        $this->acquireLock();
        try {
            $this->queue->ensureTable();
            $this->loadFromProviders();
            $this->flushQueueToIblock();
        } finally {
            $this->releaseLock();
        }
    }

    private function loadFromProviders(): void
    {
        $batchSize = Config::getImportBatchSize();

        foreach ($this->providers as $provider) {
            foreach ($provider->fetch($batchSize) as $review) {
                $this->queue->enqueue($review->provider, $review->externalId, $review->getHash(), json_encode($review, JSON_UNESCAPED_UNICODE));
            }
        }
    }

    private function flushQueueToIblock(): void
    {
        $rows = $this->queue->reserveBatch(Config::getImportBatchSize());

        foreach ($rows as $row) {
            $queueId = (int)$row['ID'];

            try {
                $payload = json_decode((string)$row['PAYLOAD'], true, 512, JSON_THROW_ON_ERROR);
                $review = new \Company\ReviewSync\Domain\ReviewDto(
                    (string)$payload['externalId'],
                    (string)$payload['provider'],
                    (string)$payload['authorName'],
                    (string)$payload['authorPosition'],
                    (int)$payload['rating'],
                    (string)$payload['text'],
                    (string)$payload['photoUrl'],
                    (string)$payload['attachmentUrl'],
                    (string)$payload['publishedAt']
                );

                if ($this->reviewRepository->existsByExternalId($review->provider, $review->externalId)) {
                    $this->queue->markDone($queueId, 0);
                    continue;
                }

                $elementId = $this->reviewRepository->createOnModeration($review);
                $this->notificationService->notifyAboutModeration($review, $elementId);
                $this->queue->markDone($queueId, $elementId);
            } catch (\Throwable) {
                $this->queue->markFailed($queueId);
            }
        }
    }

    private function isLocked(): bool
    {
        return Option::get(Config::MODULE_ID, self::LOCK_OPTION, 'N') === 'Y';
    }

    private function acquireLock(): void
    {
        Option::set(Config::MODULE_ID, self::LOCK_OPTION, 'Y');
    }

    private function releaseLock(): void
    {
        Option::set(Config::MODULE_ID, self::LOCK_OPTION, 'N');
    }
}
