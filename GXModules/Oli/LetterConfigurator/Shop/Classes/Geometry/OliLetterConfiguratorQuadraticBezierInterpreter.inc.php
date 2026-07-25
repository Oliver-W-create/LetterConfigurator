<?php

/**
 * Handles quadratic Bézier SVG path commands.
 */
class OliLetterConfiguratorQuadraticBezierInterpreter
{
    private const QUADRATIC_TO_CUBIC_FACTOR = 2.0 / 3.0;

    /** @var OliLetterConfiguratorPointTranslator */
    private $pointTranslator;

    public function __construct(
        ?OliLetterConfiguratorPointTranslator $pointTranslator = null
    ) {
        $this->pointTranslator = $pointTranslator
            ?: new OliLetterConfiguratorPointTranslator();
    }

    /**
     * @param OliLetterConfiguratorPoint          $startPoint
     * @param OliLetterConfiguratorSvgPathCommand $pathCommand
     *
     * @return void
     *
     * @throws OliLetterConfiguratorGeometryException
     */
    public function interpret(
        OliLetterConfiguratorPoint $startPoint,
        OliLetterConfiguratorSvgPathCommand $pathCommand
    ) {
        $parameters = $pathCommand->getParameters();
        if ($pathCommand->isRelative()) {
            $controlPoint = $this->pointTranslator->translate(
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
            $controlPoint = new OliLetterConfiguratorPoint(
                $parameters[0],
                $parameters[1]
            );
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

        throw new OliLetterConfiguratorGeometryException(
            'SVG path command Q is not implemented yet.'
        );
    }
}
