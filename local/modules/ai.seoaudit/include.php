<?php

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('ai.seoaudit', [
    'Ai\\SeoAudit\\Application\\AgentManager' => 'lib/Application/AgentManager.php',
    'Ai\\SeoAudit\\Application\\FeatureCatalog' => 'lib/Application/FeatureCatalog.php',
    'Ai\\SeoAudit\\Application\\MigrationManager' => 'lib/Application/MigrationManager.php',
    'Ai\\SeoAudit\\Application\\QueueService' => 'lib/Application/QueueService.php',
    'Ai\\SeoAudit\\Application\\Scheduler' => 'lib/Application/Scheduler.php',
    'Ai\\SeoAudit\\Domain\\PackagePlan' => 'lib/Domain/PackagePlan.php',
    'Ai\\SeoAudit\\Infrastructure\\Api\\ApiProviderRegistry' => 'lib/Infrastructure/Api/ApiProviderRegistry.php',
    'Ai\\SeoAudit\\Infrastructure\\Api\\Contracts\\LlmProviderInterface' => 'lib/Infrastructure/Api/Contracts/LlmProviderInterface.php',
    'Ai\\SeoAudit\\Infrastructure\\Api\\Contracts\\SearchProviderInterface' => 'lib/Infrastructure/Api/Contracts/SearchProviderInterface.php',
    'Ai\\SeoAudit\\Infrastructure\\Api\\Providers\\GoogleSearchConsoleProvider' => 'lib/Infrastructure/Api/Providers/GoogleSearchConsoleProvider.php',
    'Ai\\SeoAudit\\Infrastructure\\Api\\Providers\\LocalLlmProvider' => 'lib/Infrastructure/Api/Providers/LocalLlmProvider.php',
    'Ai\\SeoAudit\\Infrastructure\\Api\\Providers\\OpenAiProvider' => 'lib/Infrastructure/Api/Providers/OpenAiProvider.php',
    'Ai\\SeoAudit\\Infrastructure\\Api\\Providers\\YandexWebmasterProvider' => 'lib/Infrastructure/Api/Providers/YandexWebmasterProvider.php',
    'Ai\\SeoAudit\\Model\\ApiCredentialTable' => 'lib/Model/ApiCredentialTable.php',
    'Ai\\SeoAudit\\Model\\ProjectTable' => 'lib/Model/ProjectTable.php',
    'Ai\\SeoAudit\\Model\\TaskTable' => 'lib/Model/TaskTable.php',
    'Ai\\SeoAudit\\Model\\TenantTable' => 'lib/Model/TenantTable.php',
]);
