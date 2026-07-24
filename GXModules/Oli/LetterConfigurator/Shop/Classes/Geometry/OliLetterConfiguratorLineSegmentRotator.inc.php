<?php

/**
 * Rotates both endpoints of a line segment around the origin.
 */
class OliLetterConfiguratorLineSegmentRotator
{
    /** @var OliLetterConfiguratorPointRotator */
    private $pointRotator;

    public function __construct(OliLetterConfiguratorPointRotator $pointRotator)
    {
        $this->pointRotator = $pointRotator;
    }

    /**
     * @param OliLetterConfiguratorLineSegment $segment
     * @param float                            $angleInRadians
     *
     * @return OliLetterConfiguratorLineSegment
     */
    public function rotate(
        OliLetterConfiguratorLineSegment $segment,
        float $angleInRadians
    ) {
        $rotatedFrom = $this->pointRotator->rotate(
            $segment->getFrom(),
            $angleInRadians
        );
        $rotatedTo = $this->pointRotator->rotate(
            $segment->getTo(),
            $angleInRadians
        );

        return new OliLetterConfiguratorLineSegment($rotatedFrom, $rotatedTo);
    }
}
