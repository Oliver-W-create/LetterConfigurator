<?php

/**
 * Resolves normalized SVG path commands to path geometry.
 */
class OliLetterConfiguratorSvgPathInterpreter
{
    /** @var OliLetterConfiguratorCubicBezierInterpreter */
    private $cubicBezierInterpreter;

    /** @var OliLetterConfiguratorSmoothCubicBezierInterpreter */
    private $smoothCubicBezierInterpreter;

    /** @var OliLetterConfiguratorQuadraticBezierInterpreter */
    private $quadraticBezierInterpreter;

    /** @var OliLetterConfiguratorSmoothQuadraticBezierInterpreter */
    private $smoothQuadraticBezierInterpreter;

    /** @var OliLetterConfiguratorArcInterpreter */
    private $arcInterpreter;

    public function __construct(
        ?OliLetterConfiguratorCubicBezierInterpreter $cubicBezierInterpreter = null,
        ?OliLetterConfiguratorSmoothCubicBezierInterpreter $smoothCubicBezierInterpreter = null,
        ?OliLetterConfiguratorQuadraticBezierInterpreter $quadraticBezierInterpreter = null,
        ?OliLetterConfiguratorSmoothQuadraticBezierInterpreter $smoothQuadraticBezierInterpreter = null,
        ?OliLetterConfiguratorArcInterpreter $arcInterpreter = null
    ) {
        $this->cubicBezierInterpreter = $cubicBezierInterpreter
            ?: new OliLetterConfiguratorCubicBezierInterpreter();
        $this->smoothCubicBezierInterpreter = $smoothCubicBezierInterpreter
            ?: new OliLetterConfiguratorSmoothCubicBezierInterpreter();
        $this->quadraticBezierInterpreter = $quadraticBezierInterpreter
            ?: new OliLetterConfiguratorQuadraticBezierInterpreter();
        $this->smoothQuadraticBezierInterpreter = $smoothQuadraticBezierInterpreter
            ?: new OliLetterConfiguratorSmoothQuadraticBezierInterpreter();
        $this->arcInterpreter = $arcInterpreter
            ?: new OliLetterConfiguratorArcInterpreter();
    }

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
        $previousPathCommand = null;
        $previousCommandStartPoint = null;
        $previousQuadraticControlPoint = null;

        foreach ($commands as $pathCommand) {
            $commandStartPoint = $currentPoint;
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

                    case 'C':
                        $cubicBezierSegments = $this->cubicBezierInterpreter->interpret(
                            $currentPoint,
                            $pathCommand
                        );
                        foreach ($cubicBezierSegments as $cubicBezierSegment) {
                            $geometry->addSegment($cubicBezierSegment);
                        }
                        $currentPoint = $cubicBezierSegments[count($cubicBezierSegments) - 1]->getTo();
                        $previousPathCommand = $pathCommand;
                        $previousCommandStartPoint = $commandStartPoint;
                        $previousQuadraticControlPoint = null;
                        continue 2;

                    case 'S':
                        $smoothCubicBezierSegments = $this->smoothCubicBezierInterpreter->interpret(
                            $currentPoint,
                            $pathCommand,
                            $previousPathCommand,
                            $previousCommandStartPoint
                        );
                        foreach ($smoothCubicBezierSegments as $smoothCubicBezierSegment) {
                            $geometry->addSegment($smoothCubicBezierSegment);
                        }
                        $currentPoint = $smoothCubicBezierSegments[count($smoothCubicBezierSegments) - 1]->getTo();
                        $previousPathCommand = $pathCommand;
                        $previousCommandStartPoint = $commandStartPoint;
                        $previousQuadraticControlPoint = null;
                        continue 2;

                    case 'Q':
                        $quadraticBezierSegments = $this->quadraticBezierInterpreter->interpret(
                            $currentPoint,
                            $pathCommand
                        );
                        foreach ($quadraticBezierSegments as $quadraticBezierSegment) {
                            $geometry->addSegment($quadraticBezierSegment);
                        }
                        $currentPoint = $quadraticBezierSegments[count($quadraticBezierSegments) - 1]->getTo();
                        $previousQuadraticControlPoint = $this->quadraticBezierInterpreter->resolveControlPoint(
                            $commandStartPoint,
                            $pathCommand
                        );
                        $previousPathCommand = $pathCommand;
                        $previousCommandStartPoint = $commandStartPoint;
                        continue 2;

                    case 'T':
                        $smoothQuadraticBezierSegments = $this->smoothQuadraticBezierInterpreter->interpret(
                            $currentPoint,
                            $pathCommand,
                            $previousPathCommand,
                            $previousCommandStartPoint,
                            $previousQuadraticControlPoint
                        );
                        foreach ($smoothQuadraticBezierSegments as $smoothQuadraticBezierSegment) {
                            $geometry->addSegment($smoothQuadraticBezierSegment);
                        }
                        $currentPoint = $smoothQuadraticBezierSegments[
                            count($smoothQuadraticBezierSegments) - 1
                        ]->getTo();
                        $previousQuadraticControlPoint =
                            $this->smoothQuadraticBezierInterpreter->resolveControlPoint(
                                $commandStartPoint,
                                $previousPathCommand,
                                $previousQuadraticControlPoint
                            );
                        $previousPathCommand = $pathCommand;
                        $previousCommandStartPoint = $commandStartPoint;
                        continue 2;

                    case 'A':
                        $arcSegments = $this->arcInterpreter->interpret(
                            $currentPoint,
                            $pathCommand
                        );
                        foreach ($arcSegments as $arcSegment) {
                            $geometry->addSegment($arcSegment);
                        }
                        $currentPoint = $this->arcInterpreter->resolveEndPoint(
                            $commandStartPoint,
                            $pathCommand
                        );
                        $previousPathCommand = $pathCommand;
                        $previousCommandStartPoint = $commandStartPoint;
                        $previousQuadraticControlPoint = null;
                        continue 2;

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

            $previousPathCommand = $pathCommand;
            $previousCommandStartPoint = $commandStartPoint;
            if ($command !== 'Q' && $command !== 'T') {
                $previousQuadraticControlPoint = null;
            }
        }

        return $geometry;
    }
}
