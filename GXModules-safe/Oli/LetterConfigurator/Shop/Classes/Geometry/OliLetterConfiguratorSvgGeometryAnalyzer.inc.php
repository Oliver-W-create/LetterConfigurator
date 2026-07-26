<?php

/**
 * Extracts deterministic, server-side SVG metadata and basic geometry.
 *
 * Basic SVG primitives are measured exactly. Path contour length and area are
 * intentionally reported as unavailable until a production-grade path engine
 * is introduced; the service never substitutes the bounding-box perimeter.
 */
class OliLetterConfiguratorSvgGeometryAnalyzer
{
    /** @var array<string, int> */
    private $counts = [];

    /** @var array<int, string> */
    private $warnings = [];

    /** @var float */
    private $contourLength = 0.0;

    /** @var float */
    private $closedArea = 0.0;

    /** @var int */
    private $measuredElements = 0;

    /** @var int */
    private $unmeasuredElements = 0;

    /** @var array<string, int> */
    private $geometryStatistics = [];

    /** @var array<string, int> */
    private $unknownElementCounts = [];

    /** @var bool */
    private $hasNonRenderedDefinitionGeometry = false;

    /**
     * Analyzes the supported geometry contained in an SVG document.
     *
     * @param DOMElement  $root        Root svg element.
     * @param int|null    $sourceBytes Original source size in bytes.
     * @param string|null $filename    Original filename used as result metadata.
     *
     * @return array<string, mixed>
     */
    public function analyze(DOMElement $root, $sourceBytes = null, $filename = null)
    {
        $this->reset();

        $viewBox = $this->parseViewBox($root->getAttribute('viewBox'));
        $widthAttribute = $root->getAttribute('width');
        $heightAttribute = $root->getAttribute('height');
        $width = $this->parseLength($widthAttribute);
        $height = $this->parseLength($heightAttribute);
        $this->warnAboutUnsupportedLengthUnit($widthAttribute, 'width');
        $this->warnAboutUnsupportedLengthUnit($heightAttribute, 'height');
        $widthMm = $this->lengthToMillimetres($width);
        $heightMm = $this->lengthToMillimetres($height);

        $scaleX = null;
        $scaleY = null;
        if ($viewBox !== null && $widthMm !== null && $heightMm !== null) {
            // Convert one viewBox unit into millimetres independently per axis.
            $scaleX = $widthMm / $viewBox[2];
            $scaleY = $heightMm / $viewBox[3];
        }

        $hasUniformPhysicalScale = true;
        if ($viewBox === null) {
            $this->addWarning('missing_or_invalid_viewbox');
        }
        if ($widthMm === null || $heightMm === null) {
            $this->addWarning('physical_dimensions_unavailable');
        }
        if ($scaleX !== null && $scaleY !== null
            && abs($scaleX - $scaleY) > max($scaleX, $scaleY) * 0.01) {
            $this->addWarning('non_uniform_physical_scale');
            $hasUniformPhysicalScale = false;
        }

        $bounds = [
            'min_x' => INF,
            'min_y' => INF,
            'max_x' => -INF,
            'max_y' => -INF,
        ];

        $rootHasTransform = $root->hasAttribute('transform');
        if ($rootHasTransform) {
            $this->counts['transformed']++;
        }
        $rootStyle = $this->resolveStyle($root, ['fill' => 'black', 'stroke' => 'none']);
        $this->walk($root, $bounds, $rootHasTransform, $rootStyle, false);

        if ($this->unmeasuredElements > 0) {
            $this->addWarning('geometry_contains_unmeasured_elements');
        }
        if (($this->counts['text'] + $this->counts['image'] + $this->counts['use']
            + $this->counts['symbol'] + $this->counts['mask'] + $this->counts['clippath']) > 0) {
            $this->addWarning('unsupported_production_elements_present');
        }
        if (is_finite($bounds['min_x']) && ($bounds['min_x'] < 0 || $bounds['min_y'] < 0)) {
            $this->addWarning('negative_coordinates');
        }
        if ($this->geometryStatistics['unknown_elements'] > 0) {
            $this->addWarning('unknown_svg_elements_present');
        }

        $unitLength = $this->contourLength;
        $unitArea = $this->closedArea;
        $lengthMm = null;
        $areaMm2 = null;
        if ($scaleX !== null && $scaleY !== null) {
            // A contour can use one physical scale only when both axes agree.
            // Non-uniform scaling is direction-dependent and cannot be derived
            // from the aggregate SVG-unit length without segment geometry.
            if ($hasUniformPhysicalScale) {
                $lengthMm = $unitLength * (($scaleX + $scaleY) / 2);
            }
            // Area scales by the determinant of the axis-aligned scale matrix.
            $areaMm2 = $unitArea * $scaleX * $scaleY;
        }

        $unknownElementTypes = $this->unknownElementCounts;
        ksort($unknownElementTypes, SORT_STRING);
        $hasUnsupportedProductionElements = (
            $this->counts['text']
            + $this->counts['image']
            + $this->counts['use']
            + $this->counts['symbol']
            + $this->counts['mask']
            + $this->counts['clippath']
        ) > 0;
        $isComplete = $this->unmeasuredElements === 0
            && $this->geometryStatistics['unknown_elements'] === 0
            && !$hasUnsupportedProductionElements
            && !$this->hasNonRenderedDefinitionGeometry;

        return [
            'source' => [
                'filename' => $filename === null ? null : (string)$filename,
                'bytes' => $sourceBytes === null ? null : (int)$sourceBytes,
            ],
            'document' => [
                'width' => $width,
                'height' => $height,
                'width_mm' => $widthMm,
                'height_mm' => $heightMm,
                'view_box' => $viewBox,
            ],
            'elements' => $this->counts,
            'statistics' => [
                'closed_contours' => $this->geometryStatistics['closed_contours'],
                'open_contours' => $this->geometryStatistics['open_contours'],
                'filled_elements' => $this->geometryStatistics['filled_elements'],
                'unfilled_elements' => $this->geometryStatistics['unfilled_elements'],
                'elements_with_stroke' => $this->geometryStatistics['elements_with_stroke'],
                'elements_without_stroke' => $this->geometryStatistics['elements_without_stroke'],
                'unknown_elements' => $this->geometryStatistics['unknown_elements'],
                'unknown_element_types' => $unknownElementTypes,
            ],
            'geometry' => [
                'measured_elements' => $this->measuredElements,
                'unmeasured_elements' => $this->unmeasuredElements,
                'contour_length_svg_units' => $unitLength,
                'contour_length_mm' => $lengthMm,
                'closed_area_svg_units2' => $unitArea,
                'closed_area_mm2' => $areaMm2,
                'bounding_box_svg_units' => $this->normaliseBounds($bounds),
                'is_complete' => $isComplete,
            ],
            'warnings' => $this->warnings,
        ];
    }

    private function reset()
    {
        $this->counts = [
            'path' => 0, 'rect' => 0, 'circle' => 0, 'ellipse' => 0,
            'line' => 0, 'polyline' => 0, 'polygon' => 0, 'group' => 0,
            'text' => 0, 'image' => 0, 'use' => 0, 'symbol' => 0,
            'marker' => 0, 'pattern' => 0, 'mask' => 0, 'clippath' => 0,
            'transformed' => 0,
        ];
        $this->warnings = [];
        $this->contourLength = 0.0;
        $this->closedArea = 0.0;
        $this->measuredElements = 0;
        $this->unmeasuredElements = 0;
        $this->geometryStatistics = [
            'closed_contours' => 0,
            'open_contours' => 0,
            'filled_elements' => 0,
            'unfilled_elements' => 0,
            'elements_with_stroke' => 0,
            'elements_without_stroke' => 0,
            'unknown_elements' => 0,
        ];
        $this->unknownElementCounts = [];
        $this->hasNonRenderedDefinitionGeometry = false;
    }

    /**
     * Walks the SVG tree while propagating inherited transforms and paint.
     *
     * A transform on a group affects every descendant. The analyzer records
     * that relationship but leaves the affected geometry unmeasured until a
     * dedicated transform engine is available.
     *
     * @param array<string, float> $bounds
     * @param array{fill: string, stroke: string} $inheritedStyle
     */
    private function walk(
        DOMElement $element,
        array &$bounds,
        $hasInheritedTransform,
        array $inheritedStyle,
        $isInsideNonRenderedContainer
    ) {
        foreach ($element->childNodes as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }

            $name = strtolower($child->localName);
            $style = $this->resolveStyle($child, $inheritedStyle);
            $isSvgElement = $child->namespaceURI === null
                || $child->namespaceURI === ''
                || $child->namespaceURI === 'http://www.w3.org/2000/svg';
            if ($isSvgElement && $name === 'g') {
                $this->counts['group']++;
            } elseif ($isSvgElement && array_key_exists($name, $this->counts)) {
                $this->counts[$name]++;
            } elseif (!$isSvgElement || !$this->isKnownSvgElement($name)) {
                $this->geometryStatistics['unknown_elements']++;
                $this->unknownElementCounts[$name] = isset($this->unknownElementCounts[$name])
                    ? $this->unknownElementCounts[$name] + 1
                    : 1;
            }
            $hasTransform = $hasInheritedTransform || $child->hasAttribute('transform');
            if ($child->hasAttribute('transform')) {
                $this->counts['transformed']++;
            }

            $childIsInsideNonRenderedContainer = $isInsideNonRenderedContainer
                || in_array($name, ['defs', 'symbol', 'marker', 'pattern', 'mask', 'clippath'], true);

            if ($isSvgElement
                && in_array($name, ['path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon'], true)) {
                if ($childIsInsideNonRenderedContainer) {
                    $this->hasNonRenderedDefinitionGeometry = true;
                    $this->addWarning('non_rendered_definition_geometry_present');
                } elseif ($this->measureElement($child, $name, $bounds, $hasTransform)) {
                    $this->collectElementStatistics($child, $name, $style);
                }
            }

            $this->walk(
                $child,
                $bounds,
                $hasTransform,
                $style,
                $childIsInsideNonRenderedContainer
            );
        }
    }

    /**
     * Collects contour and paint statistics independently of measurement.
     *
     * @param array{fill: string, stroke: string} $style
     */
    private function collectElementStatistics(DOMElement $element, $name, array $style)
    {
        $isClosed = in_array($name, ['rect', 'circle', 'ellipse', 'polygon'], true);
        if ($name === 'path') {
            // This only classifies the explicit closing command. It does not
            // estimate path length, area or any other unsupported geometry.
            $isClosed = preg_match('/[zZ]\s*$/', $element->getAttribute('d')) === 1;
        }

        if ($isClosed) {
            $this->geometryStatistics['closed_contours']++;
        } else {
            $this->geometryStatistics['open_contours']++;
        }

        if (strtolower(trim($style['fill'])) === 'none') {
            $this->geometryStatistics['unfilled_elements']++;
        } else {
            $this->geometryStatistics['filled_elements']++;
        }

        if (strtolower(trim($style['stroke'])) === 'none') {
            $this->geometryStatistics['elements_without_stroke']++;
        } else {
            $this->geometryStatistics['elements_with_stroke']++;
        }
    }

    /**
     * Resolves inherited fill/stroke presentation values for one element.
     *
     * Inline style declarations override presentation attributes, matching
     * normal CSS precedence for these inherited SVG properties.
     *
     * @param array{fill: string, stroke: string} $inheritedStyle
     *
     * @return array{fill: string, stroke: string}
     */
    private function resolveStyle(DOMElement $element, array $inheritedStyle)
    {
        $resolved = $inheritedStyle;
        foreach (['fill', 'stroke'] as $property) {
            if ($element->hasAttribute($property)) {
                $resolved[$property] = trim($element->getAttribute($property));
            }
        }

        $style = $element->getAttribute('style');
        if (trim($style) === '') {
            return $resolved;
        }

        $styleWithoutComments = preg_replace('/\/\*.*?\*\//s', '', $style);
        if ($styleWithoutComments === null) {
            return $resolved;
        }

        foreach (explode(';', $styleWithoutComments) as $declaration) {
            $separatorPosition = strpos($declaration, ':');
            if ($separatorPosition === false) {
                continue;
            }

            $property = strtolower(trim(substr($declaration, 0, $separatorPosition)));
            if ($property !== 'fill' && $property !== 'stroke') {
                continue;
            }

            $value = trim(substr($declaration, $separatorPosition + 1));
            $value = preg_replace('/\s*!important\s*$/i', '', $value);
            if ($value === null || trim($value) === '') {
                continue;
            }

            $resolved[$property] = trim($value);
        }

        return $resolved;
    }

    /**
     * Returns whether an element name belongs to the known SVG vocabulary.
     */
    private function isKnownSvgElement($name)
    {
        static $knownElements = [
            'svg', 'g', 'defs', 'desc', 'title', 'metadata', 'a', 'switch',
            'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon',
            'text', 'tspan', 'textpath', 'image', 'use', 'symbol', 'view',
            'marker', 'pattern', 'mask', 'clippath',
            'lineargradient', 'radialgradient', 'stop',
            'filter', 'feblend', 'fecolormatrix', 'fecomponenttransfer',
            'fecomposite', 'feconvolvematrix', 'fediffuselighting',
            'fedisplacementmap', 'fedistantlight', 'fedropshadow', 'feflood',
            'fefunca', 'fefuncb', 'fefuncg', 'fefuncr', 'fegaussianblur',
            'feimage', 'femerge', 'femergenode', 'femorphology', 'feoffset',
            'fepointlight', 'fespecularlighting', 'fespotlight', 'fetile',
            'feturbulence', 'animate', 'animatemotion', 'animatetransform',
            'mpath', 'set',
        ];

        return in_array(strtolower((string)$name), $knownElements, true);
    }

    /**
     * @param array<string, float> $bounds
     */
    private function measureElement(DOMElement $element, $name, array &$bounds, $hasTransform)
    {
        if ($name === 'path') {
            if (trim($element->getAttribute('d')) === '') {
                $this->unmeasuredElements++;
                $this->addWarning('invalid_geometry_attribute');
                return false;
            }
            $this->unmeasuredElements++;
            if ($hasTransform) {
                $this->addWarning('transformed_geometry_requires_path_engine');
            }
            $this->addWarning('path_geometry_requires_path_engine');
            return true;
        }

        if ($name === 'rect') {
            $x = $this->number($element, 'x', 0, $xIsValid);
            $y = $this->number($element, 'y', 0, $yIsValid);
            $width = $this->number($element, 'width', null, $widthIsValid);
            $height = $this->number($element, 'height', null, $heightIsValid);
            $rx = $this->number($element, 'rx', null, $rxIsValid);
            $ry = $this->number($element, 'ry', null, $ryIsValid);
            if (!$xIsValid || !$yIsValid || !$widthIsValid || !$heightIsValid
                || !$rxIsValid || !$ryIsValid
                || $width === null || $height === null || $width < 0 || $height < 0
                || $rx !== null && $rx < 0 || $ry !== null && $ry < 0) {
                $this->unmeasuredElements++;
                $this->addWarning('invalid_geometry_attribute');
                return false;
            }
            if ($this->skipTransformedGeometry($hasTransform)) {
                return true;
            }
            if ($width == 0.0 || $height == 0.0) {
                $this->measuredElements++;
                return true;
            }

            // SVG copies the specified corner radius to the missing counterpart.
            // If neither is present, both radii default to zero.
            if ($rx === null && $ry === null) {
                $rx = 0.0;
                $ry = 0.0;
            } elseif ($rx === null) {
                $rx = $ry;
            } elseif ($ry === null) {
                $ry = $rx;
            }

            // SVG clamps corner radii so opposing rounded corners never overlap.
            $rx = min($rx, $width / 2);
            $ry = min($ry, $height / 2);

            if ($rx > 0.0 && $ry > 0.0) {
                // Four quarter-ellipses form one complete ellipse. The straight
                // sections are the remaining horizontal and vertical edges.
                $cornerContour = $this->ellipseCircumference($rx, $ry);
                $straightContour = 2 * ($width - 2 * $rx) + 2 * ($height - 2 * $ry);
                $this->contourLength += $straightContour + $cornerContour;

                // Start with the full rectangle, remove four rx×ry corner boxes
                // and add back the four quarter-ellipses (one full ellipse).
                $this->closedArea += $width * $height - 4 * $rx * $ry + M_PI * $rx * $ry;
            } else {
                // A zero radius on either axis produces a regular rectangle.
                $this->contourLength += 2 * ($width + $height);
                $this->closedArea += $width * $height;
            }
            $this->includePoint($bounds, $x, $y);
            $this->includePoint($bounds, $x + $width, $y + $height);
            $this->measuredElements++;
            return true;
        }

        if ($name === 'circle') {
            $cx = $this->number($element, 'cx', 0, $cxIsValid);
            $cy = $this->number($element, 'cy', 0, $cyIsValid);
            $radius = $this->number($element, 'r', null, $radiusIsValid);
            if (!$cxIsValid || !$cyIsValid || !$radiusIsValid
                || $radius === null || $radius < 0) {
                $this->unmeasuredElements++;
                $this->addWarning('invalid_geometry_attribute');
                return false;
            }
            if ($this->skipTransformedGeometry($hasTransform)) {
                return true;
            }
            if ($radius == 0.0) {
                $this->measuredElements++;
                return true;
            }
            // Circle circumference and area.
            $this->contourLength += 2 * M_PI * $radius;
            $this->closedArea += M_PI * $radius * $radius;
            $this->includePoint($bounds, $cx - $radius, $cy - $radius);
            $this->includePoint($bounds, $cx + $radius, $cy + $radius);
            $this->measuredElements++;
            return true;
        }

        if ($name === 'ellipse') {
            $cx = $this->number($element, 'cx', 0, $cxIsValid);
            $cy = $this->number($element, 'cy', 0, $cyIsValid);
            $rx = $this->number($element, 'rx', null, $rxIsValid);
            $ry = $this->number($element, 'ry', null, $ryIsValid);
            if (!$cxIsValid || !$cyIsValid || !$rxIsValid || !$ryIsValid
                || $rx === null || $ry === null || $rx < 0 || $ry < 0) {
                $this->unmeasuredElements++;
                $this->addWarning('invalid_geometry_attribute');
                return false;
            }
            if ($this->skipTransformedGeometry($hasTransform)) {
                return true;
            }
            if ($rx == 0.0 || $ry == 0.0) {
                $this->measuredElements++;
                return true;
            }
            // Ellipse contour uses Ramanujan's second approximation.
            $this->contourLength += $this->ellipseCircumference($rx, $ry);
            // Exact ellipse area.
            $this->closedArea += M_PI * $rx * $ry;
            $this->includePoint($bounds, $cx - $rx, $cy - $ry);
            $this->includePoint($bounds, $cx + $rx, $cy + $ry);
            $this->measuredElements++;
            return true;
        }

        if ($name === 'line') {
            $x1 = $this->number($element, 'x1', 0, $x1IsValid);
            $y1 = $this->number($element, 'y1', 0, $y1IsValid);
            $x2 = $this->number($element, 'x2', 0, $x2IsValid);
            $y2 = $this->number($element, 'y2', 0, $y2IsValid);
            if (!$x1IsValid || !$y1IsValid || !$x2IsValid || !$y2IsValid) {
                $this->unmeasuredElements++;
                $this->addWarning('invalid_geometry_attribute');
                return false;
            }
            if ($this->skipTransformedGeometry($hasTransform)) {
                return true;
            }
            // Euclidean distance between both line endpoints.
            $this->contourLength += hypot($x2 - $x1, $y2 - $y1);
            $this->includePoint($bounds, $x1, $y1);
            $this->includePoint($bounds, $x2, $y2);
            $this->addWarning('open_contours_present');
            $this->measuredElements++;
            return true;
        }

        $points = $this->parsePoints($element->getAttribute('points'));
        if ($points === null) {
            $this->unmeasuredElements++;
            $this->addWarning('invalid_points_data');
            return false;
        }
        $pointCount = count($points);
        $minimumPointCount = $name === 'polygon' ? 3 : 2;
        if ($pointCount < $minimumPointCount) {
            $this->unmeasuredElements++;
            $this->addWarning('invalid_points_data');
            return false;
        }
        if ($this->skipTransformedGeometry($hasTransform)) {
            return true;
        }
        $closed = $name === 'polygon';
        $length = 0.0;
        $area = 0.0;
        for ($index = 0; $index < $pointCount; $index++) {
            $this->includePoint($bounds, $points[$index][0], $points[$index][1]);
            if ($index > 0) {
                // Add the Euclidean length of each consecutive edge.
                $length += hypot(
                    $points[$index][0] - $points[$index - 1][0],
                    $points[$index][1] - $points[$index - 1][1]
                );
            }
            if ($closed) {
                // Shoelace formula term; modulo closes the final polygon edge.
                $next = $points[($index + 1) % $pointCount];
                $area += $points[$index][0] * $next[1] - $next[0] * $points[$index][1];
            }
        }
        if ($closed) {
            $last = $points[$pointCount - 1];
            // Polygon contour includes the closing edge to the first point.
            $length += hypot($points[0][0] - $last[0], $points[0][1] - $last[1]);
            // Shoelace returns twice the signed area.
            $this->closedArea += abs($area / 2);
        } else {
            $this->addWarning('open_contours_present');
        }
        $this->contourLength += $length;
        $this->measuredElements++;
        return true;
    }

    /**
     * Marks valid but transformed geometry as currently unmeasurable.
     */
    private function skipTransformedGeometry($hasTransform)
    {
        if (!$hasTransform) {
            return false;
        }

        $this->unmeasuredElements++;
        $this->addWarning('transformed_geometry_requires_path_engine');
        return true;
    }

    /**
     * @return array<int, array{0: float, 1: float}>|null
     */
    private function parsePoints($value)
    {
        $value = trim((string)$value);
        $number = '[+-]?(?:\d+\.?\d*|\.\d+)(?:[eE][+-]?\d+)?';
        if ($value === ''
            || !preg_match('/^' . $number . '(?:(?:\s*,\s*|\s+)' . $number . ')*$/', $value)) {
            return null;
        }

        preg_match_all('/' . $number . '/', $value, $matches);
        $numbers = array_map('floatval', $matches[0]);
        if (count($numbers) % 2 !== 0
            || array_filter($numbers, static function ($number) {
                return !is_finite($number);
            })) {
            return null;
        }

        $points = [];
        for ($index = 0; $index + 1 < count($numbers); $index += 2) {
            $points[] = [$numbers[$index], $numbers[$index + 1]];
        }
        return $points;
    }

    private function number(DOMElement $element, $attribute, $default = null, &$isValid = null)
    {
        $isValid = true;
        if (!$element->hasAttribute($attribute)) {
            return $default;
        }
        $value = trim($element->getAttribute($attribute));
        $this->warnAboutUnsupportedLengthUnit($value, 'geometry_' . strtolower((string)$attribute));
        if (!preg_match('/^[+-]?(?:\d+\.?\d*|\.\d+)(?:[eE][+-]?\d+)?$/', $value)) {
            $isValid = false;
            return null;
        }

        $number = (float)$value;
        if (!is_finite($number)) {
            $isValid = false;
            return null;
        }

        return $number;
    }

    /**
     * @return array{value: float, unit: string}|null
     */
    private function parseLength($value)
    {
        if (!preg_match('/^([+-]?(?:\d+\.?\d*|\.\d+))(px|mm|cm|in|pt|pc)?$/i', trim((string)$value), $match)) {
            return null;
        }
        return ['value' => (float)$match[1], 'unit' => strtolower(isset($match[2]) ? $match[2] : '')];
    }

    private function lengthToMillimetres($length)
    {
        if ($length === null || !is_finite($length['value']) || $length['value'] <= 0) {
            return null;
        }
        $factors = [
            'mm' => 1.0, 'cm' => 10.0, 'in' => 25.4, 'pt' => 25.4 / 72,
            'pc' => 25.4 / 6, 'px' => 25.4 / 96, '' => 25.4 / 96,
        ];
        if (!array_key_exists($length['unit'], $factors)) {
            return null;
        }

        $millimetres = $length['value'] * $factors[$length['unit']];
        return is_finite($millimetres) && $millimetres > 0 ? $millimetres : null;
    }

    /**
     * Adds an explicit warning for context-dependent SVG/CSS length units.
     */
    private function warnAboutUnsupportedLengthUnit($value, $context)
    {
        if (!preg_match('/^[+-]?(?:\d+\.?\d*|\.\d+)(%|em|rem|ex)$/i', trim((string)$value), $match)) {
            return;
        }

        $unit = strtolower($match[1]);
        if ($unit === '%') {
            $unit = 'percent';
        }
        $this->addWarning('unsupported_length_unit_' . $context . '_' . $unit);
    }

    /**
     * Adds a warning code once while preserving its first occurrence order.
     */
    private function addWarning($warning)
    {
        if (!in_array($warning, $this->warnings, true)) {
            $this->warnings[] = $warning;
        }
    }

    /**
     * Approximates an ellipse circumference using Ramanujan's second formula.
     */
    private function ellipseCircumference($radiusX, $radiusY)
    {
        // h measures the squared eccentricity contribution in the approximation.
        $h = (($radiusX - $radiusY) * ($radiusX - $radiusY))
            / (($radiusX + $radiusY) * ($radiusX + $radiusY));

        return M_PI * ($radiusX + $radiusY)
            * (1 + (3 * $h) / (10 + sqrt(4 - 3 * $h)));
    }

    /**
     * @return array{0: float, 1: float, 2: float, 3: float}|null
     */
    private function parseViewBox($value)
    {
        $number = '[+-]?(?:\d+\.?\d*|\.\d+)(?:[eE][+-]?\d+)?';
        $separator = '(?:\s*,\s*|\s+)';
        $pattern = '/^\s*(' . $number . ')' . $separator
            . '(' . $number . ')' . $separator
            . '(' . $number . ')' . $separator
            . '(' . $number . ')\s*$/';
        if (!preg_match($pattern, (string)$value, $matches)) {
            return null;
        }

        $values = [
            (float)$matches[1],
            (float)$matches[2],
            (float)$matches[3],
            (float)$matches[4],
        ];
        foreach ($values as $numberValue) {
            if (!is_finite($numberValue)) {
                return null;
            }
        }

        return $values[2] > 0 && $values[3] > 0 ? $values : null;
    }

    /**
     * @param array<string, float> $bounds
     */
    private function includePoint(array &$bounds, $x, $y)
    {
        $bounds['min_x'] = min($bounds['min_x'], $x);
        $bounds['min_y'] = min($bounds['min_y'], $y);
        $bounds['max_x'] = max($bounds['max_x'], $x);
        $bounds['max_y'] = max($bounds['max_y'], $y);
    }

    /**
     * @param array<string, float> $bounds
     *
     * @return array<string, float>|null
     */
    private function normaliseBounds(array $bounds)
    {
        if (!is_finite($bounds['min_x'])) {
            return null;
        }
        return [
            'x' => $bounds['min_x'],
            'y' => $bounds['min_y'],
            'width' => $bounds['max_x'] - $bounds['min_x'],
            'height' => $bounds['max_y'] - $bounds['min_y'],
        ];
    }
}
