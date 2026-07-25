<?php

/**
 * Handles smooth cubic Bézier SVG path commands.
 */
class OliLetterConfiguratorSmoothCubicBezierInterpreter
{
    private const APPROXIMATION_SEGMENTS = 20;

    /** @var OliLetterConfiguratorPointTranslator */
    private $pointTranslator;

    /** @var OliLetterConfiguratorCubicBezierApproximator */
    private $cubicBezierApproximator;

    public function __construct(
        ?OliLetterConfiguratorPointTranslator $pointTranslator = null,
        ?OliLetterConfiguratorCubicBezierApproximator $cubicBezierApproximator = null
    ) {
        $this->pointTranslator = $pointTranslator
            ?: new OliLetterConfiguratorPointTranslator();
        $this->cubicBezierApproximator = $cubicBezierApproximator
            ?: new OliLetterConfiguratorCubicBezierApproximator();
    }

    /**
     * @param OliLetterConfiguratorPoint          $startPoint
     * @param OliLetterConfiguratorSvgPathCommand $pathCommand
     * @param OliLetterConfiguratorSvgPathCommand|null $previousPathCommand
     * @param OliLetterConfiguratorPoint|null          $previousCommandStartPoint
     *
     * @return OliLetterConfiguratorLineSegment[]
     */
    public function interpret(
        OliLetterConfiguratorPoint $startPoint,
        OliLetterConfiguratorSvgPathCommand $pathCommand,
        ?OliLetterConfiguratorSvgPathCommand $previousPathCommand = null,
        ?OliLetterConfiguratorPoint $previousCommandStartPoint = null
    ) {
        $parameters = $pathCommand->getParameters();
        if ($pathCommand->isRelative()) {
            $controlPoint2 = $this->pointTranslator->translate(
                $startPoint,
                $parameters[0],
                $parameters[1]
            );
            $endPoint = $this->pointTranslator->translate(
                $startPoint,
                $parameters[2],
                $parameters[3]
            );
        } else {
            $controlPoint2 = new OliLetterConfiguratorPoint(
                $parameters[0],
                $parameters[1]
            );
            $endPoint = new OliLetterConfiguratorPoint(
                $parameters[2],
                $parameters[3]
            );
        }

        $controlPoint1 = new OliLetterConfiguratorPoint(
            $startPoint->getX(),
            $startPoint->getY()
        );
        if ($previousPathCommand !== null && $previousCommandStartPoint !== null) {
            $previousCommand = $previousPathCommand->getCommand();
            if ($previousCommand === 'C' || $previousCommand === 'S') {
                $previousParameters = $previousPathCommand->getParameters();
                $controlPoint2Index = $previousCommand === 'C' ? 2 : 0;

                if ($previousPathCommand->isRelative()) {
                    $previousControlPoint2 = $this->pointTranslator->translate(
                        $previousCommandStartPoint,
                        $previousParameters[$controlPoint2Index],
                        $previousParameters[$controlPoint2Index + 1]
                    );
                } else {
                    $previousControlPoint2 = new OliLetterConfiguratorPoint(
                        $previousParameters[$controlPoint2Index],
                        $previousParameters[$controlPoint2Index + 1]
                    );
                }

                $controlPoint1 = new OliLetterConfiguratorPoint(
                    (2 * $startPoint->getX()) - $previousControlPoint2->getX(),
                    (2 * $startPoint->getY()) - $previousControlPoint2->getY()
                );
            }
        }

        return $this->cubicBezierApproximator->approximate(
            $startPoint,
            $controlPoint1,
            $controlPoint2,
            $endPoint,
            self::APPROXIMATION_SEGMENTS
        );
    }
}
