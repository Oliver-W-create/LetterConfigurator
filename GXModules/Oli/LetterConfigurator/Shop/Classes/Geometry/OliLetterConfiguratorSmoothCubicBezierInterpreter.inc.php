<?php

/**
 * Handles smooth cubic Bézier SVG path commands.
 */
class OliLetterConfiguratorSmoothCubicBezierInterpreter
{
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
        throw new OliLetterConfiguratorGeometryException(
            'SVG path command S is not implemented yet.'
        );
    }
}
