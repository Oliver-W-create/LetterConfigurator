<?php

/**
 * Minimal logging contract for domain and application services.
 */
interface OliLetterConfiguratorLoggerInterface
{
    public function debug($message, array $context = []);

    public function info($message, array $context = []);

    public function warning($message, array $context = []);

    public function error($message, array $context = []);
}
