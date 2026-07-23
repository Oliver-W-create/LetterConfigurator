<?php

/**
 * Converts SVG path tokens into normalized path commands.
 *
 * This milestone supports moveto, line and close-path commands. Curve and arc
 * commands remain explicitly unavailable until the next parser milestone.
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
        $tokenCount = count($tokens);
        if ($tokenCount === 0) {
            return [];
        }

        $commands = [];
        $position = 0;

        while ($position < $tokenCount) {
            $commandToken = $tokens[$position];
            if (!$commandToken instanceof OliLetterConfiguratorSvgPathToken || !$commandToken->isCommand()) {
                throw new OliLetterConfiguratorGeometryException(
                    'Expected SVG path command at token index ' . $position . '.'
                );
            }

            $rawCommand = $commandToken->getCommand();
            $command = strtoupper((string)$rawCommand);
            $relative = $rawCommand !== $command;

            if ($position === 0 && $command !== 'M') {
                throw new OliLetterConfiguratorGeometryException(
                    'A non-empty SVG path must begin with M or m at token index ' . $position . '.'
                );
            }

            if (!array_key_exists($command, self::COMMAND_PARAMETER_COUNTS)) {
                throw new OliLetterConfiguratorGeometryException(
                    'Unknown SVG path command at token index ' . $position . '.'
                );
            }

            if (in_array($command, ['C', 'S', 'Q', 'T', 'A'], true)) {
                throw new OliLetterConfiguratorGeometryException(
                    'SVG path command ' . $command . ' is not implemented yet at token index ' . $position . '.'
                );
            }

            $commandPosition = $position;
            $position++;
            $parameterCount = self::COMMAND_PARAMETER_COUNTS[$command];

            if ($parameterCount === 0) {
                $commands[] = OliLetterConfiguratorSvgPathCommand::create($command, $relative, []);

                if ($position < $tokenCount
                    && $tokens[$position] instanceof OliLetterConfiguratorSvgPathToken
                    && $tokens[$position]->isNumber()
                ) {
                    throw new OliLetterConfiguratorGeometryException(
                        'SVG path command ' . $command . ' cannot have parameters at token index ' . $position . '.'
                    );
                }

                continue;
            }

            $groupIndex = 0;
            while ($position < $tokenCount
                && $tokens[$position] instanceof OliLetterConfiguratorSvgPathToken
                && $tokens[$position]->isNumber()
            ) {
                $parameters = [];

                for ($parameterIndex = 0; $parameterIndex < $parameterCount; $parameterIndex++) {
                    if ($position >= $tokenCount
                        || !$tokens[$position] instanceof OliLetterConfiguratorSvgPathToken
                        || !$tokens[$position]->isNumber()
                    ) {
                        throw new OliLetterConfiguratorGeometryException(
                            'SVG path command ' . $command . ' has too few parameters near token index '
                            . $position . '.'
                        );
                    }

                    $parameters[] = $tokens[$position]->getNumber();
                    $position++;
                }

                $outputCommand = $command === 'M' && $groupIndex > 0 ? 'L' : $command;
                $commands[] = OliLetterConfiguratorSvgPathCommand::create(
                    $outputCommand,
                    $relative,
                    $parameters
                );
                $groupIndex++;
            }

            if ($groupIndex === 0) {
                throw new OliLetterConfiguratorGeometryException(
                    'SVG path command ' . $command . ' requires parameters after token index '
                    . $commandPosition . '.'
                );
            }

            if ($position < $tokenCount
                && (!$tokens[$position] instanceof OliLetterConfiguratorSvgPathToken
                    || !$tokens[$position]->isCommand())
            ) {
                throw new OliLetterConfiguratorGeometryException(
                    'Unexpected token at index ' . $position . ' after SVG path command ' . $command . '.'
                );
            }
        }

        return $commands;
    }
}
