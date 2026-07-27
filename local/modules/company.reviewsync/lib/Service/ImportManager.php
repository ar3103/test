<?php

declare(strict_types=1);

namespace Company\ReviewSync\Service;

use Bitrix\Main\Config\Option as BitrixOption;
use Company\ReviewSync\Config\Option;
use Company\ReviewSync\Notifier\NotificationService;
use Company\ReviewSync\Provider\ReviewProviderInterface;
use Company\ReviewSync\Repository\ReviewRepository;

final class ImportManager
{
    /** @param ReviewProviderInterface[] $providers */
    public function __construct(
        private readonly array $providers,
        private readonly ReviewRepository $repository,
        private readonly NotificationService $notificationService
    ) {
    }

    public function import(): int
    {
        $iblockId = Option::getInt(Option::IBLOCK_ID);
        if ($iblockId <= 0) {
            return 0;
        }

        $imported = 0;
        foreach ($this->providers as $provider) {
            $since = $this->getLastSyncDate($provider->getCode());
            $items = $provider->fetchNewReviews($since);
            $maxDate = $since;

            foreach ($items as $item) {
                $xmlId = $item->getUniqueXmlId();
                if ($xmlId === '' || $this->repository->existsByXmlId($iblockId, $xmlId)) {
                    continue;
                }

                $elementId = $this->repository->add($iblockId, $item);
                $this->notificationService->notifyNewReview($item, $elementId);
                $imported++;

                if ($maxDate === null || $item->publishedAt > $maxDate) {
                    $maxDate = $item->publishedAt;
                }
            }

            if ($maxDate !== null) {
                $this->saveLastSyncDate($provider->getCode(), $maxDate);
            }
        }

        return $imported;
    }

    private function getLastSyncDate(string $providerCode): ?\DateTimeImmutable
    {
        $value = trim((string) BitrixOption::get(Option::MODULE_ID, 'last_sync_' . $providerCode, ''));
        return $value !== '' ? new \DateTimeImmutable($value) : null;
    }

    private function saveLastSyncDate(string $providerCode, \DateTimeImmutable $date): void
    {
        BitrixOption::set(Option::MODULE_ID, 'last_sync_' . $providerCode, $date->format(DATE_ATOM));
    }
}
