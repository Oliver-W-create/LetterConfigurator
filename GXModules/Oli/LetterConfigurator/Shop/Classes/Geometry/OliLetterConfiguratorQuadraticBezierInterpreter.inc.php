<?php

/**
 * Handles quadratic Bézier SVG path commands.
 */
class OliLetterConfiguratorQuadraticBezierInterpreter
{
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

        throw new OliLetterConfiguratorGeometryException(
            'SVG path command Q is not implemented yet.'
        );
    }
}
