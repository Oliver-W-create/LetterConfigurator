<?php

/**
 * Immutable runtime configuration for reusable configurator services.
 *
 * This object intentionally contains no Gambio-specific dependencies.
 */
final class OliLetterConfiguratorModuleConfiguration
{
    /** @var bool */
    private $debugEnabled;

    /** @var string */
    private $logDirectory;

    /** @var int */
    private $configurationSchemaVersion;

    public function __construct(
        $debugEnabled = false,
        $logDirectory = '',
        $configurationSchemaVersion = OliLetterConfiguratorModuleInfo::CONFIGURATION_SCHEMA_VERSION
    ) {
        $this->debugEnabled = (bool)$debugEnabled;
        $this->logDirectory = rtrim((string)$logDirectory, "/\\");
        $this->configurationSchemaVersion = max(1, (int)$configurationSchemaVersion);
    }

    public function isDebugEnabled()
    {
        return $this->debugEnabled;
    }

    public function getLogDirectory()
    {
        return $this->logDirectory;
    }

    public function getConfigurationSchemaVersion()
    {
        return $this->configurationSchemaVersion;
    }
}
