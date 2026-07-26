<?php

/**
 * Applies path geometry transformations in a fixed sequence.
 */
class OliLetterConfiguratorGeometryTransformationService
{
    /** @var OliLetterConfiguratorPathGeometryScaler */
    private $geometryScaler;

    /** @var OliLetterConfiguratorPathGeometryRotator */
    private $geometryRotator;

    /** @var OliLetterConfiguratorPathGeometryTranslator */
    private $geometryTranslator;

    public function __construct(
        OliLetterConfiguratorPathGeometryScaler $geometryScaler,
        OliLetterConfiguratorPathGeometryRotator $geometryRotator,
        OliLetterConfiguratorPathGeometryTranslator $geometryTranslator
    ) {
        $this->geometryScaler = $geometryScaler;
        $this->geometryRotator = $geometryRotator;
        $this->geometryTranslator = $geometryTranslator;
    }

    /**
     * @param OliLetterConfiguratorPathGeometry $geometry
     * @param float                             $scaleX
     * @param float                             $scaleY
     * @param float                             $angleInRadians
     * @param float                             $offsetX
     * @param float                             $offsetY
     *
     * @return OliLetterConfiguratorPathGeometry
     */
    public function transform(
        OliLetterConfiguratorPathGeometry $geometry,
        float $scaleX,
        float $scaleY,
        float $angleInRadians,
        float $offsetX,
        float $offsetY
    ) {
        $scaledGeometry = $this->geometryScaler->scale($geometry, $scaleX, $scaleY);
        $rotatedGeometry = $this->geometryRotator->rotate($scaledGeometry, $angleInRadians);
        $translatedGeometry = $this->geometryTranslator->translate(
            $rotatedGeometry,
            $offsetX,
            $offsetY
        );

        return $translatedGeometry;
    }
}
