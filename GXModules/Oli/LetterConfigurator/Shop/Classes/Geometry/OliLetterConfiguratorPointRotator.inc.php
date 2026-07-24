<?php

/**
 * Rotates a two-dimensional point around the origin.
 */
class OliLetterConfiguratorPointRotator
{
    /**
     * @param OliLetterConfiguratorPoint $point
     * @param float                      $angleInRadians
     *
     * @return OliLetterConfiguratorPoint
     */
    public function rotate(
        OliLetterConfiguratorPoint $point,
        float $angleInRadians
    ) {
        $cosine = cos($angleInRadians);
        $sine = sin($angleInRadians);
        $x = $point->getX();
        $y = $point->getY();

        return new OliLetterConfiguratorPoint(
            ($x * $cosine) - ($y * $sine),
            ($x * $sine) + ($y * $cosine)
        );
    }
}
