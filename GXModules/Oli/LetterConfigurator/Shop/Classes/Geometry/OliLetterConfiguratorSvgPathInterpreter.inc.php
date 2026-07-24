<?php

/**
 * Resolves normalized SVG path commands to path geometry.
 */
class OliLetterConfiguratorSvgPathInterpreter
{
    /**
     * @param OliLetterConfiguratorSvgPathCommand[] $commands
     *
     * @return OliLetterConfiguratorPathGeometry
     *
     * @throws OliLetterConfiguratorGeometryException
     */
    public function interpret(array $commands)
    {
        $geometry = new OliLetterConfiguratorPathGeometry();

        if (count($commands) === 0) {
            return $geometry;
        }

        $currentPoint = null;
        $subPathStart = null;

        foreach ($commands as $pathCommand) {
            $command = $pathCommand->getCommand();
            $parameters = $pathCommand->getParameters();
            $relative = $pathCommand->isRelative();

            if ($command === 'M') {
                $baseX = $relative && $currentPoint !== null ? $currentPoint->getX() : 0.0;
                $baseY = $relative && $currentPoint !== null ? $currentPoint->getY() : 0.0;
                $currentPoint = new OliLetterConfiguratorPoint(
                    $relative ? $baseX + $parameters[0] : $parameters[0],
                    $relative ? $baseY + $parameters[1] : $parameters[1]
                );
                $subPathStart = $currentPoint;
            } else {
                if ($currentPoint === null || $subPathStart === null) {
                    throw new OliLetterConfiguratorGeometryException(
                        'SVG path command ' . $command . ' cannot be interpreted before M or m.'
                    );
                }

                $fromPoint = $currentPoint;

                switch ($command) {
                    case 'L':
                        $currentPoint = new OliLetterConfiguratorPoint(
                            $relative ? $currentPoint->getX() + $parameters[0] : $parameters[0],
                            $relative ? $currentPoint->getY() + $parameters[1] : $parameters[1]
                        );
                        break;

                    case 'H':
                        $currentPoint = new OliLetterConfiguratorPoint(
                            $relative ? $currentPoint->getX() + $parameters[0] : $parameters[0],
                            $currentPoint->getY()
                        );
                        break;

                    case 'V':
                        $currentPoint = new OliLetterConfiguratorPoint(
                            $currentPoint->getX(),
                            $relative ? $currentPoint->getY() + $parameters[0] : $parameters[0]
                        );
                        break;

                    case 'Z':
                        $currentPoint = $subPathStart;
                        break;

                    default:
                        throw new OliLetterConfiguratorGeometryException(
                            'SVG path command ' . $command . ' is not implemented yet.'
                        );
                }

                $geometry->addSegment(
                    new OliLetterConfiguratorLineSegment($fromPoint, $currentPoint)
                );
            }
        }

        return $geometry;
    }
}
