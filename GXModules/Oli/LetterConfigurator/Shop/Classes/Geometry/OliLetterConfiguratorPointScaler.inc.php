<?php

/**
 * Scales a two-dimensional point relative to the origin.
 */
class OliLetterConfiguratorPointScaler
{
    /**
     * @param OliLetterConfiguratorPoint $point
     * @param float                      $scaleX
     * @param float                      $scaleY
     *
     * @return OliLetterConfiguratorPoint
     */
    public function scale(
        OliLetterConfiguratorPoint $point,
        float $scaleX,
        float $scaleY
    ) {
        return new OliLetterConfiguratorPoint(
            $point->getX() * $scaleX,
            $point->getY() * $scaleY
        );
    }
}
