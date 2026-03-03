<?php

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('ai.seoaudit', [
    'Ai\\SeoAudit\\Application\\AgentManager' => 'lib/Application/AgentManager.php',
    'Ai\\SeoAudit\\Application\\FeatureCatalog' => 'lib/Application/FeatureCatalog.php',
    'Ai\\SeoAudit\\Application\\MigrationManager' => 'lib/Application/MigrationManager.php',
    'Ai\\SeoAudit\\Application\\QueueService' => 'lib/Application/QueueService.php',
    'Ai\\SeoAudit\\Application\\Scheduler' => 'lib/Application/Scheduler.php',

    'Ai\\SeoAudit\\Application\\Auth\\OAuthTokenManager' => 'lib/Application/Auth/OAuthTokenManager.php',

    'Ai\\SeoAudit\\Application\\Embedding\\EmbeddingProviderInterface' => 'lib/Application/Embedding/EmbeddingProviderInterface.php',
    'Ai\\SeoAudit\\Application\\Embedding\\OpenAiEmbeddingProvider' => 'lib/Application/Embedding/OpenAiEmbeddingProvider.php',
    'Ai\\SeoAudit\\Application\\Embedding\\HashEmbeddingFallbackProvider' => 'lib/Application/Embedding/HashEmbeddingFallbackProvider.php',
    'Ai\\SeoAudit\\Application\\Embedding\\EmbeddingProviderFactory' => 'lib/Application/Embedding/EmbeddingProviderFactory.php',

    'Ai\\SeoAudit\\Application\\Reporting\\HtmlReportGenerator' => 'lib/Application/Reporting/HtmlReportGenerator.php',
    'Ai\\SeoAudit\\Application\\Reporting\\PdfReportGenerator' => 'lib/Application/Reporting/PdfReportGenerator.php',
    'Ai\\SeoAudit\\Application\\Reporting\\ReportService' => 'lib/Application/Reporting/ReportService.php',
    'Ai\\SeoAudit\\Application\\Reporting\\Pdf\\PdfEngineInterface' => 'lib/Application/Reporting/Pdf/PdfEngineInterface.php',
    'Ai\\SeoAudit\\Application\\Reporting\\Pdf\\WkhtmltopdfEngine' => 'lib/Application/Reporting/Pdf/WkhtmltopdfEngine.php',
    'Ai\\SeoAudit\\Application\\Reporting\\Pdf\\DompdfEngine' => 'lib/Application/Reporting/Pdf/DompdfEngine.php',
    'Ai\\SeoAudit\\Application\\Reporting\\Pdf\\TcpdfEngine' => 'lib/Application/Reporting/Pdf/TcpdfEngine.php',
    'Ai\\SeoAudit\\Application\\Reporting\\Pdf\\PdfEngineFactory' => 'lib/Application/Reporting/Pdf/PdfEngineFactory.php',

    'Ai\\SeoAudit\\Application\\Rag\\EmbeddingService' => 'lib/Application/Rag/EmbeddingService.php',
    'Ai\\SeoAudit\\Application\\Rag\\VectorStoreService' => 'lib/Application/Rag/VectorStoreService.php',
    'Ai\\SeoAudit\\Application\\Rag\\RagService' => 'lib/Application/Rag/RagService.php',

    'Ai\\SeoAudit\\Domain\\PackagePlan' => 'lib/Domain/PackagePlan.php',

    'Ai\\SeoAudit\\Infrastructure\\Api\\ApiProviderRegistry' => 'lib/Infrastructure/Api/ApiProviderRegistry.php',
    'Ai\\SeoAudit\\Infrastructure\\Api\\Contracts\\LlmProviderInterface' => 'lib/Infrastructure/Api/Contracts/LlmProviderInterface.php',
    'Ai\\SeoAudit\\Infrastructure\\Api\\Contracts\\SearchProviderInterface' => 'lib/Infrastructure/Api/Contracts/SearchProviderInterface.php',
    'Ai\\SeoAudit\\Infrastructure\\Api\\Providers\\GoogleSearchConsoleProvider' => 'lib/Infrastructure/Api/Providers/GoogleSearchConsoleProvider.php',
    'Ai\\SeoAudit\\Infrastructure\\Api\\Providers\\LocalLlmProvider' => 'lib/Infrastructure/Api/Providers/LocalLlmProvider.php',
    'Ai\\SeoAudit\\Infrastructure\\Api\\Providers\\OpenAiProvider' => 'lib/Infrastructure/Api/Providers/OpenAiProvider.php',
    'Ai\\SeoAudit\\Infrastructure\\Api\\Providers\\YandexWebmasterProvider' => 'lib/Infrastructure/Api/Providers/YandexWebmasterProvider.php',
    'Ai\\SeoAudit\\Infrastructure\\Http\\RestClient' => 'lib/Infrastructure/Http/RestClient.php',

    'Ai\\SeoAudit\\Model\\ApiCredentialTable' => 'lib/Model/ApiCredentialTable.php',
    'Ai\\SeoAudit\\Model\\ProjectTable' => 'lib/Model/ProjectTable.php',
    'Ai\\SeoAudit\\Model\\ReportTable' => 'lib/Model/ReportTable.php',
    'Ai\\SeoAudit\\Model\\TaskTable' => 'lib/Model/TaskTable.php',
    'Ai\\SeoAudit\\Model\\TenantTable' => 'lib/Model/TenantTable.php',
    'Ai\\SeoAudit\\Model\\VectorDocumentTable' => 'lib/Model/VectorDocumentTable.php',
]);
