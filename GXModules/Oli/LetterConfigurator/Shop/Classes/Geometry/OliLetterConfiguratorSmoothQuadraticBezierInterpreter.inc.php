<?php

/**
 * Handles smooth quadratic Bézier SVG path commands.
 */
class OliLetterConfiguratorSmoothQuadraticBezierInterpreter
{
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
        throw new OliLetterConfiguratorGeometryException(
            'SVG path command T is not implemented yet.'
        );
    }
}
