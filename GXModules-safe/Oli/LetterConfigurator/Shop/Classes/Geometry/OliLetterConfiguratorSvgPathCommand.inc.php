<?php

/**
 * Immutable representation of one normalized SVG path command.
 */
final class OliLetterConfiguratorSvgPathCommand
{
    /** @var string */
    private $command;

    /** @var bool */
    private $relative;

    /** @var float[] */
    private $parameters;

    /**
     * @param string  $command
     * @param bool    $relative
     * @param float[] $parameters
     */
    private function __construct($command, $relative, array $parameters)
    {
        $this->command = strtoupper((string)$command);
        $this->relative = (bool)$relative;
        $this->parameters = [];

        foreach ($parameters as $parameter) {
            $this->parameters[] = (float)$parameter;
        }
    }

    /**
     * @param string  $command
     * @param bool    $relative
     * @param float[] $parameters
     *
     * @return self
     */
    public static function create($command, $relative, array $parameters)
    {
        return new self($command, $relative, $parameters);
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @return bool
     */
    public function isRelative()
    {
        return $this->relative;
    }

    /**
     * @return float[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
