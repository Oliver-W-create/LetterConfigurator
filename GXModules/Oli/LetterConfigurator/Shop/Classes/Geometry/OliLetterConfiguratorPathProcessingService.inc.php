<?php

/**
 * Processes and analyzes one SVG path data attribute.
 */
class OliLetterConfiguratorPathProcessingService
{
    /** @var OliLetterConfiguratorSvgPathTokenizer */
    private $tokenizer;

    /** @var OliLetterConfiguratorSvgPathParser */
    private $parser;

    /** @var OliLetterConfiguratorSvgPathInterpreter */
    private $interpreter;

    /** @var OliLetterConfiguratorGeometryTransformationService */
    private $transformationService;

    /** @var OliLetterConfiguratorGeometryAnalyzer */
    private $geometryAnalyzer;

    public function __construct(
        OliLetterConfiguratorSvgPathTokenizer $tokenizer,
        OliLetterConfiguratorSvgPathParser $parser,
        OliLetterConfiguratorSvgPathInterpreter $interpreter,
        OliLetterConfiguratorGeometryTransformationService $transformationService,
        OliLetterConfiguratorGeometryAnalyzer $geometryAnalyzer
    ) {
        $this->tokenizer = $tokenizer;
        $this->parser = $parser;
        $this->interpreter = $interpreter;
        $this->transformationService = $transformationService;
        $this->geometryAnalyzer = $geometryAnalyzer;
    }

    /**
     * @param string $pathData
     * @param float  $scaleX
     * @param float  $scaleY
     * @param float  $angleInRadians
     * @param float  $offsetX
     * @param float  $offsetY
     *
     * @return OliLetterConfiguratorGeometryAnalysisResult
     */
    public function processPath(
        string $pathData,
        float $scaleX,
        float $scaleY,
        float $angleInRadians,
        float $offsetX,
        float $offsetY
    ) {
        $tokens = $this->tokenizer->tokenize($pathData);
        $commands = $this->parser->parse($tokens);
        $geometry = $this->interpreter->interpret($commands);
        $geometry = $this->transformationService->transform(
            $geometry,
            $scaleX,
            $scaleY,
            $angleInRadians,
            $offsetX,
            $offsetY
        );

        return $this->geometryAnalyzer->analyze($geometry);
    }
}
