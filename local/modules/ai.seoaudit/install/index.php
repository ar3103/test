<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class ai_seoaudit extends CModule
{
    public $MODULE_ID = 'ai.seoaudit';
    public $MODULE_VERSION = '1.1.0';
    public $MODULE_VERSION_DATE = '2026-03-03';
    public $MODULE_NAME = 'AI SEO Audit';
    public $MODULE_DESCRIPTION = 'SEO + AI модуль для мониторинга, аудита и автоматизации продвижения';
    public $PARTNER_NAME = 'AI SEO Labs';
    public $PARTNER_URI = 'https://example.com';

    public function DoInstall(): void
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->installDB();
        $this->installEvents();
        $this->installFiles();
        $this->installAgents();
    }

    public function DoUninstall(): void
    {
        $this->unInstallAgents();
        $this->unInstallEvents();
        $this->unInstallDB();
        $this->unInstallFiles();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    protected function installDB(): void
    {
        \Ai\SeoAudit\Application\MigrationManager::installSchema(__DIR__ . '/..');
    }

    protected function unInstallDB(): void
    {
        \Ai\SeoAudit\Application\MigrationManager::uninstallSchema(__DIR__ . '/..');
    }

    protected function installEvents(): void
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'main',
            'OnAfterEpilog',
            $this->MODULE_ID,
            \Ai\SeoAudit\Application\Scheduler::class,
            'tick'
        );
    }

    protected function unInstallEvents(): void
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'main',
            'OnAfterEpilog',
            $this->MODULE_ID,
            \Ai\SeoAudit\Application\Scheduler::class,
            'tick'
        );
    }

    protected function installFiles(): void
    {
        CopyDirFiles(__DIR__ . '/../admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin', true, true);
    }

    protected function unInstallFiles(): void
    {
        DeleteDirFiles(__DIR__ . '/../admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
    }

    protected function installAgents(): void
    {
        \Ai\SeoAudit\Application\AgentManager::install();
    }

    protected function unInstallAgents(): void
    {
        \Ai\SeoAudit\Application\AgentManager::uninstall();
    }
}
