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
     *
     * @return void
     *
     * @throws OliLetterConfiguratorGeometryException
     */
    public function interpret(
        OliLetterConfiguratorPoint $startPoint,
        OliLetterConfiguratorSvgPathCommand $pathCommand,
        ?OliLetterConfiguratorSvgPathCommand $previousPathCommand = null,
        ?OliLetterConfiguratorPoint $previousCommandStartPoint = null
    ) {
        throw new OliLetterConfiguratorGeometryException(
            'SVG path command T is not implemented yet.'
        );
    }
}
