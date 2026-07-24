<?php

/**
 * Public facade for server-side SVG geometry analysis.
 *
 * The service has no knowledge of uploads, carts, prices or persistence.
 */
class OliLetterConfiguratorGeometryService
{
    /** @var OliLetterConfiguratorSvgDocumentParser */
    private $parser;

    /** @var OliLetterConfiguratorSvgGeometryAnalyzer */
    private $analyzer;

    /** @var OliLetterConfiguratorPathProcessingService */
    private $pathProcessingService;

    public function __construct(
        ?OliLetterConfiguratorSvgDocumentParser $parser = null,
        ?OliLetterConfiguratorSvgGeometryAnalyzer $analyzer = null,
        ?OliLetterConfiguratorPathProcessingService $pathProcessingService = null
    ) {
        $this->parser = $parser ?: new OliLetterConfiguratorSvgDocumentParser();
        $this->analyzer = $analyzer ?: new OliLetterConfiguratorSvgGeometryAnalyzer();
        $this->pathProcessingService = $pathProcessingService;

        if ($this->pathProcessingService === null) {
            $pointTranslator = new OliLetterConfiguratorPointTranslator();
            $lineSegmentTranslator = new OliLetterConfiguratorLineSegmentTranslator($pointTranslator);
            $pathGeometryTranslator = new OliLetterConfiguratorPathGeometryTranslator($lineSegmentTranslator);

            $pointScaler = new OliLetterConfiguratorPointScaler();
            $lineSegmentScaler = new OliLetterConfiguratorLineSegmentScaler($pointScaler);
            $pathGeometryScaler = new OliLetterConfiguratorPathGeometryScaler($lineSegmentScaler);

            $pointRotator = new OliLetterConfiguratorPointRotator();
            $lineSegmentRotator = new OliLetterConfiguratorLineSegmentRotator($pointRotator);
            $pathGeometryRotator = new OliLetterConfiguratorPathGeometryRotator($lineSegmentRotator);

            $transformationService = new OliLetterConfiguratorGeometryTransformationService(
                $pathGeometryScaler,
                $pathGeometryRotator,
                $pathGeometryTranslator
            );

            $this->pathProcessingService = new OliLetterConfiguratorPathProcessingService(
                new OliLetterConfiguratorSvgPathTokenizer(),
                new OliLetterConfiguratorSvgPathParser(),
                new OliLetterConfiguratorSvgPathInterpreter(),
                $transformationService,
                new OliLetterConfiguratorGeometryAnalyzer()
            );
        }
    }

    /**
     * @throws OliLetterConfiguratorGeometryException
     */
    public function analyzeSvg($svgSource, $filename = null)
    {
        $svgSource = (string)$svgSource;
        $root = $this->parser->parse($svgSource);

        $pathAnalysisResult = null;
        $pathElements = $root->getElementsByTagName('path');
        if ($pathElements->length > 0) {
            $pathData = trim((string)$pathElements->item(0)->getAttribute('d'));
            if ($pathData !== '') {
                $pathAnalysisResult = $this->pathProcessingService->processPath(
                    $pathData,
                    1.0,
                    1.0,
                    0.0,
                    0.0,
                    0.0
                );
            }
        }

        $data = $this->analyzer->analyze($root, strlen($svgSource), $filename);
        $domBoundingBox = isset($data['geometry']['bounding_box_svg_units'])
            && is_array($data['geometry']['bounding_box_svg_units'])
            && isset(
                $data['geometry']['bounding_box_svg_units']['x'],
                $data['geometry']['bounding_box_svg_units']['y'],
                $data['geometry']['bounding_box_svg_units']['width'],
                $data['geometry']['bounding_box_svg_units']['height']
            )
                ? $data['geometry']['bounding_box_svg_units']
                : null;
        $geometryMatches = $pathAnalysisResult !== null
            && is_array($domBoundingBox)
            && abs((float)$domBoundingBox['width'] - $pathAnalysisResult->getWidth()) <= 0.001
            && abs((float)$domBoundingBox['height'] - $pathAnalysisResult->getHeight()) <= 0.001
            && abs((float)$domBoundingBox['x'] - $pathAnalysisResult->getMinX()) <= 0.001
            && abs((float)$domBoundingBox['y'] - $pathAnalysisResult->getMinY()) <= 0.001
            && abs(
                (float)$domBoundingBox['x']
                + (float)$domBoundingBox['width']
                - $pathAnalysisResult->getMaxX()
            ) <= 0.001
            && abs(
                (float)$domBoundingBox['y']
                + (float)$domBoundingBox['height']
                - $pathAnalysisResult->getMaxY()
            ) <= 0.001;

        return new OliLetterConfiguratorGeometryResult($data);
    }
}
