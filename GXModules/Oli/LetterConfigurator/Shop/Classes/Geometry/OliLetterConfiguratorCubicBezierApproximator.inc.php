<?php

/**
 * Approximates cubic Bézier curves with line segments.
 */
class OliLetterConfiguratorCubicBezierApproximator
{
    /**
     * @param OliLetterConfiguratorPoint $startPoint
     * @param OliLetterConfiguratorPoint $controlPoint1
     * @param OliLetterConfiguratorPoint $controlPoint2
     * @param OliLetterConfiguratorPoint $endPoint
     * @param int                        $segmentCount
     *
     * @return OliLetterConfiguratorPoint[]
     */
    public function approximate(
        OliLetterConfiguratorPoint $startPoint,
        OliLetterConfiguratorPoint $controlPoint1,
        OliLetterConfiguratorPoint $controlPoint2,
        OliLetterConfiguratorPoint $endPoint,
        int $segmentCount
    ) {
        $points = [];

        for ($index = 0; $index <= $segmentCount; $index++) {
            $t = $index / $segmentCount;
            $oneMinusT = 1.0 - $t;
            $startFactor = $oneMinusT * $oneMinusT * $oneMinusT;
            $controlFactor1 = 3.0 * $oneMinusT * $oneMinusT * $t;
            $controlFactor2 = 3.0 * $oneMinusT * $t * $t;
            $endFactor = $t * $t * $t;

            $x = ($startFactor * $startPoint->getX())
                + ($controlFactor1 * $controlPoint1->getX())
                + ($controlFactor2 * $controlPoint2->getX())
                + ($endFactor * $endPoint->getX());
            $y = ($startFactor * $startPoint->getY())
                + ($controlFactor1 * $controlPoint1->getY())
                + ($controlFactor2 * $controlPoint2->getY())
                + ($endFactor * $endPoint->getY());

            $points[] = new OliLetterConfiguratorPoint($x, $y);
        }

        return $points;
    }
}
