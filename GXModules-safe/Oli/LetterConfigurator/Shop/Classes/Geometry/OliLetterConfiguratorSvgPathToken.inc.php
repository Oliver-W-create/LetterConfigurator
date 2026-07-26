<?php

/**
 * Immutable lexical token from an SVG path data attribute.
 */
final class OliLetterConfiguratorSvgPathToken
{
    const TYPE_COMMAND = 'command';
    const TYPE_NUMBER = 'number';

    /** @var string */
    private $type;

    /** @var string|null */
    private $command;

    /** @var float|null */
    private $number;

    /** @var string */
    private $lexeme;

    /**
     * @param string      $type
     * @param string|null $command
     * @param float|null  $number
     * @param string      $lexeme
     */
    private function __construct($type, $command, $number, $lexeme)
    {
        $this->type = $type;
        $this->command = $command;
        $this->number = $number;
        $this->lexeme = $lexeme;
    }

    /**
     * @param string $command
     *
     * @return self
     */
    public static function command($command)
    {
        return new self(self::TYPE_COMMAND, (string)$command, null, (string)$command);
    }

    /**
     * @param string $lexeme
     *
     * @return self
     */
    public static function number($lexeme)
    {
        return new self(self::TYPE_NUMBER, null, (float)$lexeme, (string)$lexeme);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @return float|null
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Returns the exact source spelling of this token.
     *
     * @return string
     */
    public function getLexeme()
    {
        return $this->lexeme;
    }

    /**
     * @return bool
     */
    public function isCommand()
    {
        return $this->type === self::TYPE_COMMAND;
    }

    /**
     * @return bool
     */
    public function isNumber()
    {
        return $this->type === self::TYPE_NUMBER;
    }
}
