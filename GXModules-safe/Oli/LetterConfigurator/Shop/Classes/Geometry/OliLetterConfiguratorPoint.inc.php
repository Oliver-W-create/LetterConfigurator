<?php

/**
 * Immutable two-dimensional point.
 */
final class OliLetterConfiguratorPoint
{
    /** @var float */
    private $x;

    /** @var float */
    private $y;

    public function __construct(float $x, float $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @return float
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @return float
     */
    public function getY()
    {
        return $this->y;
    }
}
