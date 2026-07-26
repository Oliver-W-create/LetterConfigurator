<?php

/**
 * Versioned, Gambio-independent state of one contour-text configuration.
 *
 * M4-S deliberately keeps this aggregate compact. Geometry and pricing results
 * are stored as snapshots, while their calculation remains in dedicated services.
 */
final class OliLetterConfiguratorState implements JsonSerializable
{
    public const TYPE_CONTOUR_TEXT = 'contour_text';
    public const TEXT_MODE_SINGLE_LINE = 'single_line';
    public const TEXT_MODE_MULTI_LINE = 'multi_line';
    public const DIMENSION_MODE_HEIGHT = 'height';
    public const DIMENSION_MODE_MAX_WIDTH = 'max_width';

    /** @var array */
    private $data;

    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function create($productId = 0)
    {
        return new self(self::normalize(array(
            'schemaVersion' => OliLetterConfiguratorModuleInfo::CONFIGURATION_SCHEMA_VERSION,
            'configuratorType' => self::TYPE_CONTOUR_TEXT,
            'productId' => (int)$productId,
        )));
    }

    public static function fromArray(array $data)
    {
        return new self(self::normalize($data));
    }

    public static function fromJson($json)
    {
        $decoded = json_decode((string)$json, true);

        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid configurator JSON: ' . json_last_error_msg());
        }

        return self::fromArray($decoded);
    }

    /**
     * Returns a changed copy and leaves the current object untouched.
     */
    public function with(array $changes)
    {
        return self::fromArray(self::mergeRecursive($this->data, $changes));
    }

    public function getSchemaVersion()
    {
        return (int)$this->data['schemaVersion'];
    }

    public function getConfiguratorType()
    {
        return (string)$this->data['configuratorType'];
    }

    public function getProductId()
    {
        return (int)$this->data['productId'];
    }

    public function getText()
    {
        return $this->data['text'];
    }

    public function getFont()
    {
        return $this->data['font'];
    }

    public function getMaterial()
    {
        return $this->data['material'];
    }

    public function getDimensions()
    {
        return $this->data['dimensions'];
    }

    public function getGeometry()
    {
        return $this->data['geometry'];
    }

    public function getPricing()
    {
        return $this->data['pricing'];
    }

    public function validate()
    {
        $errors = array();
        $warnings = array();

        if ($this->getSchemaVersion() < 1) {
            $errors[] = self::message('INVALID_SCHEMA_VERSION', 'schemaVersion');
        }

        if ($this->getConfiguratorType() !== self::TYPE_CONTOUR_TEXT) {
            $errors[] = self::message('UNSUPPORTED_CONFIGURATOR_TYPE', 'configuratorType');
        }

        if ($this->getProductId() <= 0) {
            $errors[] = self::message('PRODUCT_REQUIRED', 'productId');
        }

        $text = $this->getText();
        if (trim($text['content']) === '') {
            $errors[] = self::message('TEXT_REQUIRED', 'text.content');
        }
        if (!in_array($text['mode'], array(self::TEXT_MODE_SINGLE_LINE, self::TEXT_MODE_MULTI_LINE), true)) {
            $errors[] = self::message('INVALID_TEXT_MODE', 'text.mode');
        }
        if ($text['mode'] === self::TEXT_MODE_SINGLE_LINE && preg_match('/\R/u', $text['content'])) {
            $errors[] = self::message('LINE_BREAK_NOT_ALLOWED', 'text.content');
        }

        if ($this->getFont()['fontId'] === '') {
            $errors[] = self::message('FONT_REQUIRED', 'font.fontId');
        }

        $material = $this->getMaterial();
        if ($material['materialId'] <= 0) {
            $errors[] = self::message('MATERIAL_REQUIRED', 'material.materialId');
        }
        if ($material['thicknessId'] <= 0) {
            $errors[] = self::message('THICKNESS_REQUIRED', 'material.thicknessId');
        }
        if ($material['colorId'] <= 0) {
            $errors[] = self::message('COLOR_REQUIRED', 'material.colorId');
        }

        $dimensions = $this->getDimensions();
        if (!in_array($dimensions['inputMode'], array(self::DIMENSION_MODE_HEIGHT, self::DIMENSION_MODE_MAX_WIDTH), true)) {
            $errors[] = self::message('INVALID_DIMENSION_MODE', 'dimensions.inputMode');
        }
        if ($dimensions['requestedValueMm'] <= 0) {
            $errors[] = self::message('DIMENSION_REQUIRED', 'dimensions.requestedValueMm');
        }

        if ($this->getGeometry()['calculatedWidthMm'] <= 0 || $this->getGeometry()['calculatedHeightMm'] <= 0) {
            $warnings[] = self::message('GEOMETRY_NOT_CALCULATED', 'geometry');
        }
        if ($this->getPricing()['grossAmount'] <= 0) {
            $warnings[] = self::message('PRICE_NOT_CALCULATED', 'pricing');
        }

        return new OliLetterConfiguratorValidationResult($errors, $warnings);
    }

    public function toArray()
    {
        return $this->data;
    }

    public function toJson()
    {
        $json = json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new RuntimeException('Unable to encode configurator state: ' . json_last_error_msg());
        }

        return $json;
    }

    /**
     * Stable content hash for cart separation and snapshot comparison.
     */
    public function getHash()
    {
        return hash('sha256', self::encodeCanonical($this->data));
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    private static function normalize(array $data)
    {
        $defaults = array(
            'schemaVersion' => OliLetterConfiguratorModuleInfo::CONFIGURATION_SCHEMA_VERSION,
            'configuratorType' => self::TYPE_CONTOUR_TEXT,
            'productId' => 0,
            'text' => array(
                'content' => '',
                'mode' => self::TEXT_MODE_SINGLE_LINE,
                'alignment' => 'center',
                'lineSpacing' => 1.0,
            ),
            'font' => array(
                'fontId' => '',
                'name' => '',
            ),
            'material' => array(
                'materialId' => 0,
                'materialName' => '',
                'thicknessId' => 0,
                'thicknessName' => '',
                'colorId' => 0,
                'colorName' => '',
            ),
            'dimensions' => array(
                'inputMode' => self::DIMENSION_MODE_HEIGHT,
                'requestedValueMm' => 0.0,
            ),
            'geometry' => array(
                'calculatedWidthMm' => 0.0,
                'calculatedHeightMm' => 0.0,
                'areaMm2' => 0.0,
                'pathCount' => 0,
                'geometryVersion' => '',
            ),
            'pricing' => array(
                'currency' => 'EUR',
                'netAmount' => 0.0,
                'grossAmount' => 0.0,
                'pricingVersion' => '',
                'components' => array(),
            ),
        );

        $normalized = self::mergeRecursive($defaults, $data);
        $normalized['schemaVersion'] = max(1, (int)$normalized['schemaVersion']);
        $normalized['configuratorType'] = trim((string)$normalized['configuratorType']);
        $normalized['productId'] = (int)$normalized['productId'];
        $normalized['text']['content'] = str_replace(array("\r\n", "\r"), "\n", (string)$normalized['text']['content']);
        $normalized['text']['mode'] = (string)$normalized['text']['mode'];
        $normalized['text']['alignment'] = (string)$normalized['text']['alignment'];
        $normalized['text']['lineSpacing'] = (float)$normalized['text']['lineSpacing'];
        $normalized['font']['fontId'] = trim((string)$normalized['font']['fontId']);
        $normalized['font']['name'] = trim((string)$normalized['font']['name']);

        foreach (array('materialId', 'thicknessId', 'colorId') as $idField) {
            $normalized['material'][$idField] = (int)$normalized['material'][$idField];
        }
        foreach (array('materialName', 'thicknessName', 'colorName') as $nameField) {
            $normalized['material'][$nameField] = trim((string)$normalized['material'][$nameField]);
        }

        $normalized['dimensions']['inputMode'] = (string)$normalized['dimensions']['inputMode'];
        $normalized['dimensions']['requestedValueMm'] = (float)$normalized['dimensions']['requestedValueMm'];
        foreach (array('calculatedWidthMm', 'calculatedHeightMm', 'areaMm2') as $numberField) {
            $normalized['geometry'][$numberField] = (float)$normalized['geometry'][$numberField];
        }
        $normalized['geometry']['pathCount'] = (int)$normalized['geometry']['pathCount'];
        $normalized['geometry']['geometryVersion'] = (string)$normalized['geometry']['geometryVersion'];
        $normalized['pricing']['currency'] = strtoupper(trim((string)$normalized['pricing']['currency']));
        $normalized['pricing']['netAmount'] = (float)$normalized['pricing']['netAmount'];
        $normalized['pricing']['grossAmount'] = (float)$normalized['pricing']['grossAmount'];
        $normalized['pricing']['pricingVersion'] = (string)$normalized['pricing']['pricingVersion'];
        $normalized['pricing']['components'] = is_array($normalized['pricing']['components']) ? $normalized['pricing']['components'] : array();

        return $normalized;
    }

    private static function mergeRecursive(array $base, array $changes)
    {
        foreach ($changes as $key => $value) {
            if (isset($base[$key]) && is_array($base[$key]) && is_array($value)) {
                $base[$key] = self::mergeRecursive($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }

    private static function message($code, $field)
    {
        return array('code' => $code, 'field' => $field);
    }

    private static function encodeCanonical(array $data)
    {
        self::sortRecursive($data);
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new RuntimeException('Unable to hash configurator state: ' . json_last_error_msg());
        }

        return $json;
    }

    private static function sortRecursive(array &$data)
    {
        foreach ($data as &$value) {
            if (is_array($value)) {
                self::sortRecursive($value);
            }
        }
        unset($value);

        if (self::isAssociative($data)) {
            ksort($data);
        }
    }

    private static function isAssociative(array $data)
    {
        return array_keys($data) !== range(0, count($data) - 1);
    }
}
