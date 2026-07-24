<?php

/**
 * Analyzes interpreted SVG path geometry.
 */
class OliLetterConfiguratorGeometryAnalyzer
{
    /**
     * @param OliLetterConfiguratorPathGeometry $geometry
     *
     * @return array
     *
     * @throws OliLetterConfiguratorGeometryException
     */
    public function analyze(OliLetterConfiguratorPathGeometry $geometry)
    {
        if ($geometry->isEmpty()) {
            return [];
        }

        foreach ($geometry->getSegments() as $segment) {
            throw new OliLetterConfiguratorGeometryException(
                'Geometry analysis not implemented yet.'
            );
        }

        return [];
    }
}
