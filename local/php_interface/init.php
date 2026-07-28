<?php

declare(strict_types=1);

use Bitrix\Main\Loader;
use Company\ReviewSync\Agent\ReviewImportAgent;
use Company\ReviewSync\EventHandlers\ReviewEvents;

Loader::registerAutoLoadClasses(null, [
    'Company\\ReviewSync\\Config' => '/local/lib/ReviewSync/Config.php',
    'Company\\ReviewSync\\Infrastructure\\QueueRepository' => '/local/lib/ReviewSync/Infrastructure/QueueRepository.php',
    'Company\\ReviewSync\\Infrastructure\\IblockReviewRepository' => '/local/lib/ReviewSync/Infrastructure/IblockReviewRepository.php',
    'Company\\ReviewSync\\Infrastructure\\NotificationService' => '/local/lib/ReviewSync/Infrastructure/NotificationService.php',
    'Company\\ReviewSync\\Domain\\ReviewDto' => '/local/lib/ReviewSync/Domain/ReviewDto.php',
    'Company\\ReviewSync\\Providers\\ProviderInterface' => '/local/lib/ReviewSync/Providers/ProviderInterface.php',
    'Company\\ReviewSync\\Providers\\AbstractHttpProvider' => '/local/lib/ReviewSync/Providers/AbstractHttpProvider.php',
    'Company\\ReviewSync\\Providers\\YandexProvider' => '/local/lib/ReviewSync/Providers/YandexProvider.php',
    'Company\\ReviewSync\\Providers\\GoogleProvider' => '/local/lib/ReviewSync/Providers/GoogleProvider.php',
    'Company\\ReviewSync\\Providers\\TwoGisProvider' => '/local/lib/ReviewSync/Providers/TwoGisProvider.php',
    'Company\\ReviewSync\\Service\\ReviewImportService' => '/local/lib/ReviewSync/Service/ReviewImportService.php',
    'Company\\ReviewSync\\Service\\AutoReplyService' => '/local/lib/ReviewSync/Service/AutoReplyService.php',
    'Company\\ReviewSync\\EventHandlers\\ReviewEvents' => '/local/lib/ReviewSync/EventHandlers/ReviewEvents.php',
    'Company\\ReviewSync\\Agent\\ReviewImportAgent' => '/local/lib/ReviewSync/Agent/ReviewImportAgent.php',
]);

$eventManager = Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementUpdate', [ReviewEvents::class, 'onAfterReviewPublished']);

if (Loader::includeModule('main')) {
    ReviewImportAgent::ensureAgentExists();
}
