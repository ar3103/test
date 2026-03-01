<?php

declare(strict_types=1);

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('company.reviewsync', [
    'Company\\ReviewSync\\Config\\Option' => 'lib/Config/Option.php',
    'Company\\ReviewSync\\DTO\\ReviewItem' => 'lib/DTO/ReviewItem.php',
    'Company\\ReviewSync\\Provider\\ReviewProviderInterface' => 'lib/Provider/ReviewProviderInterface.php',
    'Company\\ReviewSync\\Provider\\AbstractHttpProvider' => 'lib/Provider/AbstractHttpProvider.php',
    'Company\\ReviewSync\\Provider\\YandexProvider' => 'lib/Provider/YandexProvider.php',
    'Company\\ReviewSync\\Provider\\GoogleProvider' => 'lib/Provider/GoogleProvider.php',
    'Company\\ReviewSync\\Provider\\TwoGisProvider' => 'lib/Provider/TwoGisProvider.php',
    'Company\\ReviewSync\\Repository\\ReviewRepository' => 'lib/Repository/ReviewRepository.php',
    'Company\\ReviewSync\\Notifier\\NotificationService' => 'lib/Notifier/NotificationService.php',
    'Company\\ReviewSync\\Service\\AutoReplyService' => 'lib/Service/AutoReplyService.php',
    'Company\\ReviewSync\\Service\\ImportManager' => 'lib/Service/ImportManager.php',
    'Company\\ReviewSync\\Agent\\ReviewImportAgent' => 'lib/Agent/ReviewImportAgent.php',
    'Company\\ReviewSync\\Event\\ReviewPublishHandler' => 'lib/Event/ReviewPublishHandler.php',
]);
