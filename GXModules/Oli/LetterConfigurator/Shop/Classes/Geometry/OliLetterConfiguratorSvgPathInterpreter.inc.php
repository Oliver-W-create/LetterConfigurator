<?php

/**
 * Resolves normalized SVG path commands to their resulting points.
 */
class OliLetterConfiguratorSvgPathInterpreter
{
    /**
     * @param OliLetterConfiguratorSvgPathCommand[] $commands
     *
     * @return array
     *
     * @throws OliLetterConfiguratorGeometryException
     */
    public function interpret(array $commands)
    {
        if (count($commands) === 0) {
            return [];
        }

        $points = [];
        $currentPoint = null;
        $subPathStart = null;

        foreach ($commands as $pathCommand) {
            $command = $pathCommand->getCommand();
            $parameters = $pathCommand->getParameters();
            $relative = $pathCommand->isRelative();
            $fromPoint = $currentPoint;

            if ($command === 'M') {
                $baseX = $relative && $currentPoint !== null ? $currentPoint['x'] : 0.0;
                $baseY = $relative && $currentPoint !== null ? $currentPoint['y'] : 0.0;
                $currentPoint = [
                    'x' => $relative ? $baseX + $parameters[0] : $parameters[0],
                    'y' => $relative ? $baseY + $parameters[1] : $parameters[1],
                ];
                $subPathStart = $currentPoint;
            } else {
                if ($currentPoint === null || $subPathStart === null) {
                    throw new OliLetterConfiguratorGeometryException(
                        'SVG path command ' . $command . ' cannot be interpreted before M or m.'
                    );
                }

                switch ($command) {
                    case 'L':
                        $currentPoint = [
                            'x' => $relative ? $currentPoint['x'] + $parameters[0] : $parameters[0],
                            'y' => $relative ? $currentPoint['y'] + $parameters[1] : $parameters[1],
                        ];
                        break;

                    case 'H':
                        $currentPoint = [
                            'x' => $relative ? $currentPoint['x'] + $parameters[0] : $parameters[0],
                            'y' => $currentPoint['y'],
                        ];
                        break;

                    case 'V':
                        $currentPoint = [
                            'x' => $currentPoint['x'],
                            'y' => $relative ? $currentPoint['y'] + $parameters[0] : $parameters[0],
                        ];
                        break;

                    case 'Z':
                        $currentPoint = $subPathStart;
                        break;

                    default:
                        throw new OliLetterConfiguratorGeometryException(
                            'SVG path command ' . $command . ' is not implemented yet.'
                        );
                }
            }

            $points[] = [
                'command' => $command,
                'from' => $command === 'M' ? null : $fromPoint,
                'to' => [
                    'x' => $currentPoint['x'],
                    'y' => $currentPoint['y'],
                ],
            ];
        }

        return $points;
    }
}
