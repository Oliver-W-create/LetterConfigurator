<?php

/**
 * Handles smooth quadratic Bézier SVG path commands.
 */
class OliLetterConfiguratorSmoothQuadraticBezierInterpreter
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
     * @param OliLetterConfiguratorPoint              $startPoint
     * @param OliLetterConfiguratorSvgPathCommand     $pathCommand
     * @param OliLetterConfiguratorSvgPathCommand|null $previousPathCommand
     * @param OliLetterConfiguratorPoint|null          $previousCommandStartPoint
     * @param OliLetterConfiguratorPoint|null          $previousQuadraticControlPoint
     *
     * @return void
     *
     * @throws OliLetterConfiguratorGeometryException
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

        throw new OliLetterConfiguratorGeometryException(
            'SVG path command T is not implemented yet.'
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
