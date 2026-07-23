<?php

/**
 * Interprets normalized SVG path commands as geometry.
 *
 * Geometry interpretation is intentionally deferred to later milestones.
 */
class OliLetterConfiguratorSvgPathInterpreter
{
    /**
     * @param OliLetterConfiguratorSvgPathCommand[] $commands
     *
     * @return array
     *
     * @throws OliLetterConfiguratorGeometryException
     */
    public function interpret(array $commands)
    {
        if (count($commands) === 0) {
            return [];
        }

        foreach ($commands as $command) {
            throw new OliLetterConfiguratorGeometryException(
                'SVG path interpretation not implemented yet.'
            );
        }

        return [];
    }
}
