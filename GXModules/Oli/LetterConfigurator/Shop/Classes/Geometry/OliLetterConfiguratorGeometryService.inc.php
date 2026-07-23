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

    public function __construct(
        ?OliLetterConfiguratorSvgDocumentParser $parser = null,
        ?OliLetterConfiguratorSvgGeometryAnalyzer $analyzer = null
    ) {
        $this->parser = $parser ?: new OliLetterConfiguratorSvgDocumentParser();
        $this->analyzer = $analyzer ?: new OliLetterConfiguratorSvgGeometryAnalyzer();
    }

    /**
     * @throws OliLetterConfiguratorGeometryException
     */
    public function analyzeSvg($svgSource, $filename = null)
    {
        $svgSource = (string)$svgSource;
        $root = $this->parser->parse($svgSource);
        $data = $this->analyzer->analyze($root, strlen($svgSource), $filename);

        return new OliLetterConfiguratorGeometryResult($data);
    }
}
