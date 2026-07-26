<?php

/**
 * Rotates all line segments of path geometry around the origin.
 */
class OliLetterConfiguratorPathGeometryRotator
{
    /** @var OliLetterConfiguratorLineSegmentRotator */
    private $lineSegmentRotator;

    public function __construct(
        OliLetterConfiguratorLineSegmentRotator $lineSegmentRotator
    ) {
        $this->lineSegmentRotator = $lineSegmentRotator;
    }

    /**
     * @param OliLetterConfiguratorPathGeometry $geometry
     * @param float                             $angleInRadians
     *
     * @return OliLetterConfiguratorPathGeometry
     */
    public function rotate(
        OliLetterConfiguratorPathGeometry $geometry,
        float $angleInRadians
    ) {
        $rotatedGeometry = new OliLetterConfiguratorPathGeometry();

        foreach ($geometry->getSegments() as $segment) {
            $rotatedGeometry->addSegment(
                $this->lineSegmentRotator->rotate($segment, $angleInRadians)
            );
        }

        return $rotatedGeometry;
    }
}
