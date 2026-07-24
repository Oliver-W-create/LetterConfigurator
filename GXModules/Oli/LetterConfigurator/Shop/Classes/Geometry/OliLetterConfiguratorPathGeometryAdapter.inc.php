<?php

/**
 * Adapts an SVG path geometry analysis result to the DOM bounding-box format.
 */
class OliLetterConfiguratorPathGeometryAdapter
{
    /**
     * @param OliLetterConfiguratorGeometryAnalysisResult $result
     *
     * @return array<string, float>
     */
    public function toBoundingBox(OliLetterConfiguratorGeometryAnalysisResult $result)
    {
        return [
            'x' => $result->getMinX(),
            'y' => $result->getMinY(),
            'width' => $result->getWidth(),
            'height' => $result->getHeight(),
        ];
    }
}
