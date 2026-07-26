<?php

/**
 * Mutable container for interpreted SVG path segments.
 */
class OliLetterConfiguratorPathGeometry
{
    /** @var OliLetterConfiguratorLineSegment[] */
    private $segments;

    public function __construct()
    {
        $this->segments = [];
    }

    /**
     * @param OliLetterConfiguratorLineSegment $segment
     *
     * @return void
     */
    public function addSegment(OliLetterConfiguratorLineSegment $segment)
    {
        $this->segments[] = $segment;
    }

    /**
     * @return OliLetterConfiguratorLineSegment[]
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return count($this->segments) === 0;
    }
}
