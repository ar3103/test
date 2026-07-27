<?php

declare(strict_types=1);

namespace Company\ReviewSync\Agent;

use Company\ReviewSync\Notifier\NotificationService;
use Company\ReviewSync\Provider\GoogleProvider;
use Company\ReviewSync\Provider\TwoGisProvider;
use Company\ReviewSync\Provider\YandexProvider;
use Company\ReviewSync\Repository\ReviewRepository;
use Company\ReviewSync\Service\ImportManager;

final class ReviewImportAgent
{
    public static function run(): string
    {
        $manager = new ImportManager(
            providers: [new YandexProvider(), new GoogleProvider(), new TwoGisProvider()],
            repository: new ReviewRepository(),
            notificationService: new NotificationService()
        );

        $manager->import();

        return '\\Company\\ReviewSync\\Agent\\ReviewImportAgent::run();';
    }
}
