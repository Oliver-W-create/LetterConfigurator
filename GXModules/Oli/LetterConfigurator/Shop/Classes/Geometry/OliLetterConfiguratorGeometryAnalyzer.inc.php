<?php

/**
 * Analyzes interpreted SVG path geometry.
 */
class OliLetterConfiguratorGeometryAnalyzer
{
    /**
     * @param OliLetterConfiguratorPathGeometry $geometry
     *
     * @return OliLetterConfiguratorGeometryAnalysisResult
     */
    public function analyze(OliLetterConfiguratorPathGeometry $geometry)
    {
        if ($geometry->isEmpty()) {
            return new OliLetterConfiguratorGeometryAnalysisResult(
                0.0,
                0.0,
                0.0,
                0.0,
                0.0,
                0.0,
                0.0,
                0.0,
                0.0,
                0
            );
        }

        $minX = null;
        $minY = null;
        $maxX = null;
        $maxY = null;
        $totalLength = 0.0;
        $segmentCount = 0;

        foreach ($geometry->getSegments() as $segment) {
            $segmentCount++;

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

            $dx = $segment->getTo()->getX() - $segment->getFrom()->getX();
            $dy = $segment->getTo()->getY() - $segment->getFrom()->getY();
            $totalLength += sqrt(($dx * $dx) + ($dy * $dy));
        }

        $width = $maxX - $minX;
        $height = $maxY - $minY;
        $centerX = $minX + ($width / 2);
        $centerY = $minY + ($height / 2);

        return new OliLetterConfiguratorGeometryAnalysisResult(
            $minX,
            $minY,
            $maxX,
            $maxY,
            $width,
            $height,
            $centerX,
            $centerY,
            $totalLength,
            $segmentCount
        );
    }
}
