<?php

/**
 * Converts SVG path tokens into normalized path commands.
 *
 * Command parsing is intentionally deferred to M5.4.2.2.
 */
class OliLetterConfiguratorSvgPathParser
{
    /** @var array<string, int> */
    const COMMAND_PARAMETER_COUNTS = [
        'M' => 2,
        'L' => 2,
        'H' => 1,
        'V' => 1,
        'C' => 6,
        'S' => 4,
        'Q' => 4,
        'T' => 2,
        'A' => 7,
        'Z' => 0,
    ];

    /**
     * @param OliLetterConfiguratorSvgPathToken[] $tokens
     *
     * @return OliLetterConfiguratorSvgPathCommand[]
     *
     * @throws OliLetterConfiguratorGeometryException
     */
    public function parse(array $tokens)
    {
        if (count($tokens) === 0) {
            return [];
        }

        throw new OliLetterConfiguratorGeometryException('SVG path parser not implemented yet.');
    }
}
