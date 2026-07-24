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

    /** @var OliLetterConfiguratorPathAggregationService */
    private $pathAggregationService;

    /** @var OliLetterConfiguratorPathGeometryAdapter */
    private $pathGeometryAdapter;

    /** @var OliLetterConfiguratorBoundingBoxMergeService */
    private $boundingBoxMergeService;

    public function __construct(
        ?OliLetterConfiguratorSvgDocumentParser $parser = null,
        ?OliLetterConfiguratorSvgGeometryAnalyzer $analyzer = null,
        ?OliLetterConfiguratorPathProcessingService $pathProcessingService = null,
        ?OliLetterConfiguratorPathAggregationService $pathAggregationService = null,
        ?OliLetterConfiguratorPathGeometryAdapter $pathGeometryAdapter = null,
        ?OliLetterConfiguratorBoundingBoxMergeService $boundingBoxMergeService = null
    ) {
        $this->parser = $parser ?: new OliLetterConfiguratorSvgDocumentParser();
        $this->analyzer = $analyzer ?: new OliLetterConfiguratorSvgGeometryAnalyzer();
        $this->pathProcessingService = $pathProcessingService;
        $this->pathAggregationService = $pathAggregationService
            ?: new OliLetterConfiguratorPathAggregationService();
        $this->pathGeometryAdapter = $pathGeometryAdapter
            ?: new OliLetterConfiguratorPathGeometryAdapter();
        $this->boundingBoxMergeService = $boundingBoxMergeService
            ?: new OliLetterConfiguratorBoundingBoxMergeService();

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

        $pathAnalysisResults = [];
        $pathElements = $root->getElementsByTagName('path');
        foreach ($pathElements as $pathElement) {
            $pathData = trim((string)$pathElement->getAttribute('d'));
            if ($pathData === '') {
                continue;
            }

            $pathAnalysisResults[] = $this->pathProcessingService->processPath(
                $pathData,
                1.0,
                1.0,
                0.0,
                0.0,
                0.0
            );
        }

        $aggregatedPathAnalysisResult = count($pathAnalysisResults) > 0
            ? $this->pathAggregationService->aggregate($pathAnalysisResults)
            : null;
        $pathBoundingBox = $aggregatedPathAnalysisResult !== null
            ? $this->pathGeometryAdapter->toBoundingBox($aggregatedPathAnalysisResult)
            : null;

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
        $mergedBoundingBox = $this->boundingBoxMergeService->merge(
            $domBoundingBox,
            $pathBoundingBox
        );
        $geometryMatches = $aggregatedPathAnalysisResult !== null
            && is_array($domBoundingBox)
            && abs((float)$domBoundingBox['width'] - $aggregatedPathAnalysisResult->getWidth()) <= 0.001
            && abs((float)$domBoundingBox['height'] - $aggregatedPathAnalysisResult->getHeight()) <= 0.001
            && abs((float)$domBoundingBox['x'] - $aggregatedPathAnalysisResult->getMinX()) <= 0.001
            && abs((float)$domBoundingBox['y'] - $aggregatedPathAnalysisResult->getMinY()) <= 0.001
            && abs(
                (float)$domBoundingBox['x']
                + (float)$domBoundingBox['width']
                - $aggregatedPathAnalysisResult->getMaxX()
            ) <= 0.001
            && abs(
                (float)$domBoundingBox['y']
                + (float)$domBoundingBox['height']
                - $aggregatedPathAnalysisResult->getMaxY()
            ) <= 0.001
            && abs(
                (float)$domBoundingBox['x']
                + ((float)$domBoundingBox['width'] / 2)
                - $aggregatedPathAnalysisResult->getCenterX()
            ) <= 0.001
            && abs(
                (float)$domBoundingBox['y']
                + ((float)$domBoundingBox['height'] / 2)
                - $aggregatedPathAnalysisResult->getCenterY()
            ) <= 0.001;

        return new OliLetterConfiguratorGeometryResult($data);
    }
}
