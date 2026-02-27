<?php

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(null, [
    'Local\\AiConsultant\\SiteResolver' => '/local/lib/AiConsultant/SiteResolver.php',
    'Local\\AiConsultant\\KnowledgeBase' => '/local/lib/AiConsultant/KnowledgeBase.php',
    'Local\\AiConsultant\\GptClient' => '/local/lib/AiConsultant/GptClient.php',
    'Local\\AiConsultant\\LeadRepository' => '/local/lib/AiConsultant/LeadRepository.php',
    'Local\\AiConsultant\\NotificationService' => '/local/lib/AiConsultant/NotificationService.php',
    'Local\\AiConsultant\\DialogueService' => '/local/lib/AiConsultant/DialogueService.php',
]);
