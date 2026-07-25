<?php

/**
 * Handles quadratic Bézier SVG path commands.
 */
class OliLetterConfiguratorQuadraticBezierInterpreter
{
    private const APPROXIMATION_SEGMENTS = 20;

    private const QUADRATIC_TO_CUBIC_FACTOR = 2.0 / 3.0;

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
        $controlPoint = $this->resolveControlPoint($startPoint, $pathCommand);
        if ($pathCommand->isRelative()) {
            $endPoint = $this->pointTranslator->translate(
                $startPoint,
                $parameters[2],
                $parameters[3]
            );
        } else {
            $endPoint = new OliLetterConfiguratorPoint(
                $parameters[2],
                $parameters[3]
            );
        }

        $cubicControlPoint1 = new OliLetterConfiguratorPoint(
            $startPoint->getX()
                + self::QUADRATIC_TO_CUBIC_FACTOR * ($controlPoint->getX() - $startPoint->getX()),
            $startPoint->getY()
                + self::QUADRATIC_TO_CUBIC_FACTOR * ($controlPoint->getY() - $startPoint->getY())
        );
        $cubicControlPoint2 = new OliLetterConfiguratorPoint(
            $endPoint->getX()
                + self::QUADRATIC_TO_CUBIC_FACTOR * ($controlPoint->getX() - $endPoint->getX()),
            $endPoint->getY()
                + self::QUADRATIC_TO_CUBIC_FACTOR * ($controlPoint->getY() - $endPoint->getY())
        );

        return $this->cubicBezierApproximator->approximate(
            $startPoint,
            $cubicControlPoint1,
            $cubicControlPoint2,
            $endPoint,
            self::APPROXIMATION_SEGMENTS
        );
    }

    /**
     * @param OliLetterConfiguratorPoint          $startPoint
     * @param OliLetterConfiguratorSvgPathCommand $pathCommand
     *
     * @return OliLetterConfiguratorPoint
     */
    public function resolveControlPoint(
        OliLetterConfiguratorPoint $startPoint,
        OliLetterConfiguratorSvgPathCommand $pathCommand
    ) {
        $parameters = $pathCommand->getParameters();
        if ($pathCommand->isRelative()) {
            return $this->pointTranslator->translate(
                $startPoint,
                $parameters[0],
                $parameters[1]
            );
        }

        return new OliLetterConfiguratorPoint(
            $parameters[0],
            $parameters[1]
        );
    }
}
