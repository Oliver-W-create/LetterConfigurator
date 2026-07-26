<?php

/**
 * Scales both endpoints of a line segment relative to the origin.
 */
class OliLetterConfiguratorLineSegmentScaler
{
    /** @var OliLetterConfiguratorPointScaler */
    private $pointScaler;

    public function __construct(OliLetterConfiguratorPointScaler $pointScaler)
    {
        $this->pointScaler = $pointScaler;
    }

    /**
     * @param OliLetterConfiguratorLineSegment $segment
     * @param float                            $scaleX
     * @param float                            $scaleY
     *
     * @return OliLetterConfiguratorLineSegment
     */
    public function scale(
        OliLetterConfiguratorLineSegment $segment,
        float $scaleX,
        float $scaleY
    ) {
        $scaledFrom = $this->pointScaler->scale(
            $segment->getFrom(),
            $scaleX,
            $scaleY
        );
        $scaledTo = $this->pointScaler->scale(
            $segment->getTo(),
            $scaleX,
            $scaleY
        );

        return new OliLetterConfiguratorLineSegment($scaledFrom, $scaledTo);
    }
}
