<?php

/**
 * Handles cubic Bézier SVG path commands.
 */
class OliLetterConfiguratorCubicBezierInterpreter
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
            'SVG path command ' . $pathCommand->getCommand() . ' is not implemented yet.'
        );
    }
}
