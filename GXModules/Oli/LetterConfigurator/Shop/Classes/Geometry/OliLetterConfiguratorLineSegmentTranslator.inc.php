<?php

/**
 * Translates both endpoints of a line segment by a fixed offset.
 */
class OliLetterConfiguratorLineSegmentTranslator
{
    /** @var OliLetterConfiguratorPointTranslator */
    private $pointTranslator;

    public function __construct(OliLetterConfiguratorPointTranslator $pointTranslator)
    {
        $this->pointTranslator = $pointTranslator;
    }

    /**
     * @param OliLetterConfiguratorLineSegment $segment
     * @param float                            $offsetX
     * @param float                            $offsetY
     *
     * @return OliLetterConfiguratorLineSegment
     */
    public function translate(
        OliLetterConfiguratorLineSegment $segment,
        float $offsetX,
        float $offsetY
    ) {
        $translatedFrom = $this->pointTranslator->translate(
            $segment->getFrom(),
            $offsetX,
            $offsetY
        );
        $translatedTo = $this->pointTranslator->translate(
            $segment->getTo(),
            $offsetX,
            $offsetY
        );

        return new OliLetterConfiguratorLineSegment($translatedFrom, $translatedTo);
    }
}
