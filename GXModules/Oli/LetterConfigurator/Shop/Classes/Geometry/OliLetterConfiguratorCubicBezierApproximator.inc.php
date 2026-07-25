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
     * @return void
     *
     * @throws OliLetterConfiguratorGeometryException
     */
    public function approximate(
        OliLetterConfiguratorPoint $startPoint,
        OliLetterConfiguratorPoint $controlPoint1,
        OliLetterConfiguratorPoint $controlPoint2,
        OliLetterConfiguratorPoint $endPoint,
        int $segmentCount
    ) {
        throw new OliLetterConfiguratorGeometryException(
            'Cubic Bézier approximation is not implemented yet.'
        );
    }
}
