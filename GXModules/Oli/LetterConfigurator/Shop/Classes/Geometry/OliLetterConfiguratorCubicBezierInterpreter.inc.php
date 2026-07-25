<?php

/**
 * Handles cubic Bézier SVG path commands.
 */
class OliLetterConfiguratorCubicBezierInterpreter
{
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
     *
     * @return OliLetterConfiguratorLineSegment[]
     */
    public function interpret(
        OliLetterConfiguratorPoint $startPoint,
        OliLetterConfiguratorSvgPathCommand $pathCommand
    ) {
        $parameters = $pathCommand->getParameters();

        if ($pathCommand->isRelative()) {
            $controlPoint1 = $this->pointTranslator->translate(
                $startPoint,
                $parameters[0],
                $parameters[1]
            );
            $controlPoint2 = $this->pointTranslator->translate(
                $startPoint,
                $parameters[2],
                $parameters[3]
            );
            $endPoint = $this->pointTranslator->translate(
                $startPoint,
                $parameters[4],
                $parameters[5]
            );
        } else {
            $controlPoint1 = new OliLetterConfiguratorPoint(
                $parameters[0],
                $parameters[1]
            );
            $controlPoint2 = new OliLetterConfiguratorPoint(
                $parameters[2],
                $parameters[3]
            );
            $endPoint = new OliLetterConfiguratorPoint(
                $parameters[4],
                $parameters[5]
            );
        }

        return $this->cubicBezierApproximator->approximate(
            $startPoint,
            $controlPoint1,
            $controlPoint2,
            $endPoint,
            20
        );
    }
}
