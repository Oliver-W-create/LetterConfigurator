<?php

/**
 * Handles elliptical arc SVG path commands.
 */
class OliLetterConfiguratorArcInterpreter
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
        if (count($parameters) !== 7) {
            throw new OliLetterConfiguratorGeometryException(
                'SVG arc command requires exactly 7 parameters.'
            );
        }

        $rx = abs($parameters[0]);
        $ry = abs($parameters[1]);
        $rotation = $parameters[2];
        $largeArcFlag = $parameters[3];
        $sweepFlag = $parameters[4];
        if (!in_array($largeArcFlag, [0.0, 1.0], true)
            || !in_array($sweepFlag, [0.0, 1.0], true)
        ) {
            throw new OliLetterConfiguratorGeometryException(
                'Invalid SVG arc flags.'
            );
        }
        $endPoint = $this->resolveEndPoint($startPoint, $pathCommand);

        throw new OliLetterConfiguratorGeometryException(
            'SVG path command A is not implemented yet.'
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
                $parameters[5],
                $parameters[6]
            );
        }

        return new OliLetterConfiguratorPoint(
            $parameters[5],
            $parameters[6]
        );
    }
}
