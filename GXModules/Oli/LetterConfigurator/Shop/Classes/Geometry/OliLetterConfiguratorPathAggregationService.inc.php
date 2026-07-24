<?php

/**
 * Aggregates multiple SVG path geometry analysis results.
 */
class OliLetterConfiguratorPathAggregationService
{
    /**
     * @param OliLetterConfiguratorGeometryAnalysisResult[] $results
     *
     * @return OliLetterConfiguratorGeometryAnalysisResult
     *
     * @throws InvalidArgumentException
     */
    public function aggregate(array $results)
    {
        if (count($results) === 0) {
            throw new InvalidArgumentException(
                'At least one geometry analysis result is required.'
            );
        }

        $minX = null;
        $minY = null;
        $maxX = null;
        $maxY = null;
        $totalLength = 0.0;
        $segmentCount = 0;

        foreach ($results as $result) {
            if (!$result instanceof OliLetterConfiguratorGeometryAnalysisResult) {
                throw new InvalidArgumentException(
                    'All results must be geometry analysis results.'
                );
            }

            if ($minX === null) {
                $minX = $result->getMinX();
                $minY = $result->getMinY();
                $maxX = $result->getMaxX();
                $maxY = $result->getMaxY();
            } else {
                $minX = min($minX, $result->getMinX());
                $minY = min($minY, $result->getMinY());
                $maxX = max($maxX, $result->getMaxX());
                $maxY = max($maxY, $result->getMaxY());
            }

            $totalLength += $result->getTotalLength();
            $segmentCount += $result->getSegmentCount();
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
