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
     */
    public function analyze(OliLetterConfiguratorPathGeometry $geometry)
    {
        if ($geometry->isEmpty()) {
            return [];
        }

        $minX = null;
        $minY = null;
        $maxX = null;
        $maxY = null;

        foreach ($geometry->getSegments() as $segment) {
            foreach ([$segment->getFrom(), $segment->getTo()] as $point) {
                $x = $point->getX();
                $y = $point->getY();

                if ($minX === null) {
                    $minX = $x;
                    $minY = $y;
                    $maxX = $x;
                    $maxY = $y;
                    continue;
                }

                $minX = min($minX, $x);
                $minY = min($minY, $y);
                $maxX = max($maxX, $x);
                $maxY = max($maxY, $y);
            }
        }

        return [
            'minX' => $minX,
            'minY' => $minY,
            'maxX' => $maxX,
            'maxY' => $maxY,
        ];
    }
}
