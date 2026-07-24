<?php

/**
 * Immutable line segment between two points.
 */
final class OliLetterConfiguratorLineSegment
{
    /** @var OliLetterConfiguratorPoint */
    private $from;

    /** @var OliLetterConfiguratorPoint */
    private $to;

    public function __construct(
        OliLetterConfiguratorPoint $from,
        OliLetterConfiguratorPoint $to
    ) {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @return OliLetterConfiguratorPoint
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return OliLetterConfiguratorPoint
     */
    public function getTo()
    {
        return $this->to;
    }
}
