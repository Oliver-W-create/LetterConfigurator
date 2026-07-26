<?php

/**
 * Converts an SVG path data attribute into command and number tokens.
 *
 * This class performs lexical analysis only. It deliberately does not validate
 * command arity or create path segments.
 *
 * Manual regression cases (no project test harness is available):
 * - "1e999999" must be rejected as a non-finite number.
 * - "M-.5-.6" must produce M, -.5 and -.6.
 * - "M10.20" must produce M and 10.20.
 */
class OliLetterConfiguratorSvgPathTokenizer
{
    const DEFAULT_MAX_BYTES = 262144;

    /**
     * Matches signed SVG integers/decimals and optional exponents at the current offset.
     *
     * @var string
     */
    const NUMBER_PATTERN = '/\G[+-]?(?:(?:[0-9]+\.[0-9]*)|(?:\.[0-9]+)|(?:[0-9]+))(?:[eE][+-]?[0-9]+)?/';

    /** @var int */
    private $maxBytes;

    /**
     * @param int $maxBytes Maximum accepted byte length of the path data.
     */
    public function __construct($maxBytes = self::DEFAULT_MAX_BYTES)
    {
        $this->maxBytes = max(1, (int)$maxBytes);
    }

    /**
     * Tokenizes an SVG path data attribute without interpreting its commands.
     *
     * Empty and whitespace-only input produces an empty token list.
     *
     * @param mixed $pathData
     *
     * @return OliLetterConfiguratorSvgPathToken[]
     *
     * @throws OliLetterConfiguratorGeometryException
     */
    public function tokenize($pathData)
    {
        $pathData = (string)$pathData;
        $length = strlen($pathData);

        if ($length > $this->maxBytes) {
            throw new OliLetterConfiguratorGeometryException(
                'The SVG path data exceeds the configured size limit.'
            );
        }

        $tokens = [];
        $offset = 0;

        while ($offset < $length) {
            if ($this->isWhitespace($pathData[$offset])) {
                $offset++;
                continue;
            }

            if ($pathData[$offset] === ',') {
                $separatorOffset = $offset;
                $lastToken = count($tokens) > 0 ? $tokens[count($tokens) - 1] : null;
                if (!$lastToken instanceof OliLetterConfiguratorSvgPathToken || !$lastToken->isNumber()) {
                    $this->throwInvalidSeparator($separatorOffset);
                }

                $offset++;
                while ($offset < $length && $this->isWhitespace($pathData[$offset])) {
                    $offset++;
                }

                if ($offset >= $length || $this->matchNumber($pathData, $offset) === null) {
                    $this->throwInvalidSeparator($separatorOffset);
                }

                continue;
            }

            $character = $pathData[$offset];
            if ($this->isCommand($character)) {
                $tokens[] = OliLetterConfiguratorSvgPathToken::command($character);
                $offset++;
                continue;
            }

            $number = $this->matchNumber($pathData, $offset);
            if ($number !== null) {
                if (!is_finite((float)$number)) {
                    throw new OliLetterConfiguratorGeometryException(
                        'Non-finite SVG number at byte offset ' . $offset . '.'
                    );
                }

                $tokens[] = OliLetterConfiguratorSvgPathToken::number($number);
                $offset += strlen($number);
                continue;
            }

            if ($character === '+' || $character === '-' || $character === '.'
                || ($character >= '0' && $character <= '9')
            ) {
                throw new OliLetterConfiguratorGeometryException(
                    'Invalid SVG number at byte offset ' . $offset . '.'
                );
            }

            $displayCharacter = addcslashes($character, "\0..\37\177..\377\"\\");
            if (($character >= 'A' && $character <= 'Z') || ($character >= 'a' && $character <= 'z')) {
                throw new OliLetterConfiguratorGeometryException(
                    'Unknown SVG path command "' . $displayCharacter . '" at byte offset ' . $offset . '.'
                );
            }

            throw new OliLetterConfiguratorGeometryException(
                'Unexpected character "' . $displayCharacter
                . '" in SVG path data at byte offset ' . $offset . '.'
            );
        }

        return $tokens;
    }

    /**
     * @param string $character
     *
     * @return bool
     */
    private function isWhitespace($character)
    {
        return $character === ' '
            || $character === "\t"
            || $character === "\n"
            || $character === "\r"
            || $character === "\f";
    }

    /**
     * @param string $character
     *
     * @return bool
     */
    private function isCommand($character)
    {
        return strpos('MmLlHhVvCcSsQqTtAaZz', $character) !== false;
    }

    /**
     * @param string $pathData
     * @param int    $offset
     *
     * @return string|null
     */
    private function matchNumber($pathData, $offset)
    {
        $matches = [];
        $result = preg_match(self::NUMBER_PATTERN, $pathData, $matches, 0, $offset);

        if ($result === 1) {
            return $matches[0];
        }

        if ($result === false) {
            throw new OliLetterConfiguratorGeometryException(
                'SVG path number tokenization failed at byte offset ' . $offset . '.'
            );
        }

        return null;
    }

    /**
     * @param int $offset
     *
     * @return void
     *
     * @throws OliLetterConfiguratorGeometryException
     */
    private function throwInvalidSeparator($offset)
    {
        throw new OliLetterConfiguratorGeometryException(
            'Invalid comma separator in SVG path data at byte offset ' . $offset . '.'
        );
    }
}
