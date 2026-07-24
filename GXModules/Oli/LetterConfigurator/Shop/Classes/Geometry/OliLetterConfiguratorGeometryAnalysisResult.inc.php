<?php

/**
 * Immutable result of an SVG path geometry analysis.
 */
final class OliLetterConfiguratorGeometryAnalysisResult
{
    /** @var float */
    private $minX;

    /** @var float */
    private $minY;

    /** @var float */
    private $maxX;

    /** @var float */
    private $maxY;

    /** @var float */
    private $width;

    /** @var float */
    private $height;

    /** @var float */
    private $centerX;

    /** @var float */
    private $centerY;

    /** @var float */
    private $totalLength;

    /** @var int */
    private $segmentCount;

    public function __construct(
        float $minX,
        float $minY,
        float $maxX,
        float $maxY,
        float $width,
        float $height,
        float $centerX,
        float $centerY,
        float $totalLength,
        int $segmentCount
    ) {
        $this->minX = $minX;
        $this->minY = $minY;
        $this->maxX = $maxX;
        $this->maxY = $maxY;
        $this->width = $width;
        $this->height = $height;
        $this->centerX = $centerX;
        $this->centerY = $centerY;
        $this->totalLength = $totalLength;
        $this->segmentCount = $segmentCount;
    }

    /**
     * @return float
     */
    public function getMinX()
    {
        return $this->minX;
    }

    /**
     * @return float
     */
    public function getMinY()
    {
        return $this->minY;
    }

    /**
     * @return float
     */
    public function getMaxX()
    {
        return $this->maxX;
    }

    /**
     * @return float
     */
    public function getMaxY()
    {
        return $this->maxY;
    }

    /**
     * @return float
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return float
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return float
     */
    public function getCenterX()
    {
        return $this->centerX;
    }

    /**
     * @return float
     */
    public function getCenterY()
    {
        return $this->centerY;
    }

    /**
     * @return float
     */
    public function getTotalLength()
    {
        return $this->totalLength;
    }

    /**
     * @return int
     */
    public function getSegmentCount()
    {
        return $this->segmentCount;
    }
}
