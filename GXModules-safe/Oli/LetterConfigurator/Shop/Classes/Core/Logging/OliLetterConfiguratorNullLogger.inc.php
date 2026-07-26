<?php

/**
 * No-op logger used when no concrete Gambio logger has been wired yet.
 */
final class OliLetterConfiguratorNullLogger implements OliLetterConfiguratorLoggerInterface
{
    public function debug($message, array $context = [])
    {
    }

    public function info($message, array $context = [])
    {
    }

    public function warning($message, array $context = [])
    {
    }

    public function error($message, array $context = [])
    {
    }
}
