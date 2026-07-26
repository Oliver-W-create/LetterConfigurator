<?php

/**
 * Handles smooth quadratic Bézier SVG path commands.
 */
class OliLetterConfiguratorSmoothQuadraticBezierInterpreter
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
     * @param OliLetterConfiguratorPoint              $startPoint
     * @param OliLetterConfiguratorSvgPathCommand     $pathCommand
     * @param OliLetterConfiguratorSvgPathCommand|null $previousPathCommand
     * @param OliLetterConfiguratorPoint|null          $previousCommandStartPoint
     * @param OliLetterConfiguratorPoint|null          $previousQuadraticControlPoint
     *
     * @return OliLetterConfiguratorLineSegment[]
     */
    public function interpret(
        OliLetterConfiguratorPoint $startPoint,
        OliLetterConfiguratorSvgPathCommand $pathCommand,
        ?OliLetterConfiguratorSvgPathCommand $previousPathCommand = null,
        ?OliLetterConfiguratorPoint $previousCommandStartPoint = null,
        ?OliLetterConfiguratorPoint $previousQuadraticControlPoint = null
    ) {
        $controlPoint = $this->resolveControlPoint(
            $startPoint,
            $previousPathCommand,
            $previousQuadraticControlPoint
        );
        $endPoint = $this->resolveEndPoint($startPoint, $pathCommand);

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
    public function resolveEndPoint(
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

    /**
     * @param OliLetterConfiguratorPoint              $startPoint
     * @param OliLetterConfiguratorSvgPathCommand|null $previousPathCommand
     * @param OliLetterConfiguratorPoint|null          $previousQuadraticControlPoint
     *
     * @return OliLetterConfiguratorPoint
     */
    public function resolveControlPoint(
        OliLetterConfiguratorPoint $startPoint,
        ?OliLetterConfiguratorSvgPathCommand $previousPathCommand = null,
        ?OliLetterConfiguratorPoint $previousQuadraticControlPoint = null
    ) {
        if ($previousPathCommand === null) {
            return $startPoint;
        }

        $previousCommand = $previousPathCommand->getCommand();
        if ($previousCommand !== 'Q' && $previousCommand !== 'T') {
            return $startPoint;
        }

        if ($previousQuadraticControlPoint === null) {
            return $startPoint;
        }

        return new OliLetterConfiguratorPoint(
            (2 * $startPoint->getX()) - $previousQuadraticControlPoint->getX(),
            (2 * $startPoint->getY()) - $previousQuadraticControlPoint->getY()
        );
    }
}
