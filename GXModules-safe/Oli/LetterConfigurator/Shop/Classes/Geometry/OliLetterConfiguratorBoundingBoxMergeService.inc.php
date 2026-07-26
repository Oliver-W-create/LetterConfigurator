<?php

/**
 * Merges two DOM-format bounding boxes.
 */
class OliLetterConfiguratorBoundingBoxMergeService
{
    /**
     * @param array<string, float>|null $firstBoundingBox
     * @param array<string, float>|null $secondBoundingBox
     *
     * @return array<string, float>|null
     */
    public function merge(?array $firstBoundingBox, ?array $secondBoundingBox)
    {
        if ($firstBoundingBox === null) {
            return $secondBoundingBox;
        }
        if ($secondBoundingBox === null) {
            return $firstBoundingBox;
        }

        $minX = min($firstBoundingBox['x'], $secondBoundingBox['x']);
        $minY = min($firstBoundingBox['y'], $secondBoundingBox['y']);
        $maxX = max(
            $firstBoundingBox['x'] + $firstBoundingBox['width'],
            $secondBoundingBox['x'] + $secondBoundingBox['width']
        );
        $maxY = max(
            $firstBoundingBox['y'] + $firstBoundingBox['height'],
            $secondBoundingBox['y'] + $secondBoundingBox['height']
        );

        return [
            'x' => $minX,
            'y' => $minY,
            'width' => $maxX - $minX,
            'height' => $maxY - $minY,
        ];
    }
}
