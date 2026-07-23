<?php

/**
 * Parses untrusted SVG input without network or external-entity access.
 *
 * This parser deliberately validates rather than mutates the source. A future
 * upload workflow can therefore reject unsafe production files instead of
 * silently producing geometry from a different document.
 */
class OliLetterConfiguratorSvgDocumentParser
{
    const DEFAULT_MAX_BYTES = 2097152;

    /** @var int */
    private $maxBytes;

    public function __construct($maxBytes = self::DEFAULT_MAX_BYTES)
    {
        $this->maxBytes = max(1, (int)$maxBytes);
    }

    /**
     * @return DOMElement
     *
     * @throws OliLetterConfiguratorGeometryException
     */
    public function parse($svgSource)
    {
        if (!class_exists('DOMDocument')) {
            throw new OliLetterConfiguratorGeometryException('The PHP DOM extension is required for SVG analysis.');
        }

        $svgSource = (string)$svgSource;
        $size = strlen($svgSource);
        if ($size === 0) {
            throw new OliLetterConfiguratorGeometryException('The SVG document is empty.');
        }
        if ($size > $this->maxBytes) {
            throw new OliLetterConfiguratorGeometryException('The SVG document exceeds the configured size limit.');
        }
        if (preg_match('/<!DOCTYPE|<!ENTITY/i', $svgSource)) {
            throw new OliLetterConfiguratorGeometryException('DOCTYPE and ENTITY declarations are not allowed in SVG documents.');
        }

        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $document = new DOMDocument();
        $flags = defined('LIBXML_NONET') ? LIBXML_NONET : 0;
        if (defined('LIBXML_COMPACT')) {
            $flags |= LIBXML_COMPACT;
        }
        $loaded = $document->loadXML($svgSource, $flags);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded || !$document->documentElement instanceof DOMElement) {
            $message = 'The SVG document is not valid XML.';
            if ($errors) {
                $message .= ' ' . trim((string)$errors[0]->message);
            }
            throw new OliLetterConfiguratorGeometryException($message);
        }
        if ($document->doctype !== null) {
            throw new OliLetterConfiguratorGeometryException(
                'DOCTYPE declarations are not allowed in SVG documents.'
            );
        }

        $root = $document->documentElement;
        if (strtolower($root->localName) !== 'svg') {
            throw new OliLetterConfiguratorGeometryException('The XML root element must be svg.');
        }

        foreach ($document->childNodes as $documentChild) {
            if ($documentChild->nodeType === XML_PI_NODE) {
                throw new OliLetterConfiguratorGeometryException(
                    'XML processing instructions are not allowed in SVG documents.'
                );
            }
        }

        $this->assertSafeDocument($root);

        return $root;
    }

    /**
     * @throws OliLetterConfiguratorGeometryException
     */
    private function assertSafeDocument(DOMElement $root)
    {
        $forbiddenElements = [
            'script', 'style', 'foreignobject', 'iframe', 'object', 'embed', 'audio', 'video'
        ];

        $nodes = [$root];
        foreach ($root->getElementsByTagName('*') as $descendant) {
            $nodes[] = $descendant;
        }

        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            if (in_array(strtolower($node->localName), $forbiddenElements, true)) {
                throw new OliLetterConfiguratorGeometryException(
                    'The SVG document contains the forbidden element ' . $node->localName . '.'
                );
            }

            foreach ($node->attributes as $attribute) {
                $name = strtolower($attribute->nodeName);
                $value = trim((string)$attribute->nodeValue);
                if (strpos($name, 'on') === 0) {
                    throw new OliLetterConfiguratorGeometryException('Event handler attributes are not allowed in SVG documents.');
                }
                if (($name === 'href' || $name === 'xlink:href') && $value !== '' && strpos($value, '#') !== 0) {
                    throw new OliLetterConfiguratorGeometryException('External SVG references are not allowed.');
                }
                if (preg_match('/^(?:javascript|data|file|ftp):/i', $value)) {
                    throw new OliLetterConfiguratorGeometryException('Active or external SVG attribute content is not allowed.');
                }
                if (preg_match_all('/url\s*\(\s*([\'"]?)(.*?)\1\s*\)/i', $value, $urlMatches)) {
                    foreach ($urlMatches[2] as $urlReference) {
                        if (strpos(trim((string)$urlReference), '#') !== 0) {
                            throw new OliLetterConfiguratorGeometryException(
                                'External URL references are not allowed in SVG attributes.'
                            );
                        }
                    }
                }
            }
        }
    }
}
