<?php

/**
 * Scales all line segments of path geometry relative to the origin.
 */
class OliLetterConfiguratorPathGeometryScaler
{
    /** @var OliLetterConfiguratorLineSegmentScaler */
    private $lineSegmentScaler;

    public function __construct(
        OliLetterConfiguratorLineSegmentScaler $lineSegmentScaler
    ) {
        $this->lineSegmentScaler = $lineSegmentScaler;
    }

    /**
     * @param OliLetterConfiguratorPathGeometry $geometry
     * @param float                             $scaleX
     * @param float                             $scaleY
     *
     * @return OliLetterConfiguratorPathGeometry
     */
    public function scale(
        OliLetterConfiguratorPathGeometry $geometry,
        float $scaleX,
        float $scaleY
    ) {
        $scaledGeometry = new OliLetterConfiguratorPathGeometry();

        foreach ($geometry->getSegments() as $segment) {
            $scaledGeometry->addSegment(
                $this->lineSegmentScaler->scale($segment, $scaleX, $scaleY)
            );
        }

        return $scaledGeometry;
    }
}
