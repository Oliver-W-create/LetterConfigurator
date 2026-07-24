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
        $totalLength = 0.0;

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

            $dx = $segment->getTo()->getX() - $segment->getFrom()->getX();
            $dy = $segment->getTo()->getY() - $segment->getFrom()->getY();
            $totalLength += sqrt(($dx * $dx) + ($dy * $dy));
        }

        $width = $maxX - $minX;
        $height = $maxY - $minY;
        $centerX = $minX + ($width / 2);
        $centerY = $minY + ($height / 2);

        return [
            'minX' => $minX,
            'minY' => $minY,
            'maxX' => $maxX,
            'maxY' => $maxY,
            'width' => $width,
            'height' => $height,
            'centerX' => $centerX,
            'centerY' => $centerY,
            'totalLength' => $totalLength,
        ];
    }
}
