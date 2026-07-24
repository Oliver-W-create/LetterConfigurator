<?php

/**
 * Translates a two-dimensional point by a fixed offset.
 */
class OliLetterConfiguratorPointTranslator
{
    /**
     * @param OliLetterConfiguratorPoint $point
     * @param float                      $offsetX
     * @param float                      $offsetY
     *
     * @return OliLetterConfiguratorPoint
     */
    public function translate(
        OliLetterConfiguratorPoint $point,
        float $offsetX,
        float $offsetY
    ) {
        return new OliLetterConfiguratorPoint(
            $point->getX() + $offsetX,
            $point->getY() + $offsetY
        );
    }
}
