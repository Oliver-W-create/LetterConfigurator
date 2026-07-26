<?php

/**
 * Small dependency-free validation result used by configurator state objects.
 */
final class OliLetterConfiguratorValidationResult implements JsonSerializable
{
    /** @var array */
    private $errors;

    /** @var array */
    private $warnings;

    public function __construct(array $errors = array(), array $warnings = array())
    {
        $this->errors = array_values($errors);
        $this->warnings = array_values($warnings);
    }

    public function isValid()
    {
        return count($this->errors) === 0;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getWarnings()
    {
        return $this->warnings;
    }

    public function toArray()
    {
        return array(
            'valid' => $this->isValid(),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
        );
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
