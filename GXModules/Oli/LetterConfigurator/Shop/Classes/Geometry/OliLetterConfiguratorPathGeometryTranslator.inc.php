<?php

/**
 * Translates all line segments of path geometry by a fixed offset.
 */
class OliLetterConfiguratorPathGeometryTranslator
{
    /** @var OliLetterConfiguratorLineSegmentTranslator */
    private $lineSegmentTranslator;

    public function __construct(
        OliLetterConfiguratorLineSegmentTranslator $lineSegmentTranslator
    ) {
        $this->lineSegmentTranslator = $lineSegmentTranslator;
    }

    /**
     * @param OliLetterConfiguratorPathGeometry $geometry
     * @param float                             $offsetX
     * @param float                             $offsetY
     *
     * @return OliLetterConfiguratorPathGeometry
     */
    public function translate(
        OliLetterConfiguratorPathGeometry $geometry,
        float $offsetX,
        float $offsetY
    ) {
        $translatedGeometry = new OliLetterConfiguratorPathGeometry();

        foreach ($geometry->getSegments() as $segment) {
            $translatedGeometry->addSegment(
                $this->lineSegmentTranslator->translate($segment, $offsetX, $offsetY)
            );
        }

        return $translatedGeometry;
    }
}
