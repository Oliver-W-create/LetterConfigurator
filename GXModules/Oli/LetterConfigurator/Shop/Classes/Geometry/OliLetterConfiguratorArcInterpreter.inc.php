<?php

/**
 * Handles elliptical arc SVG path commands.
 */
class OliLetterConfiguratorArcInterpreter
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
            'SVG path command A is not implemented yet.'
        );
    }
}
