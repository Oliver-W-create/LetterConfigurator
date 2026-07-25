<?php

/**
 * Handles cubic Bézier SVG path commands.
 */
class OliLetterConfiguratorCubicBezierInterpreter
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

        throw new OliLetterConfiguratorGeometryException(
            'SVG path command ' . $pathCommand->getCommand() . ' is not implemented yet.'
        );
    }
}
