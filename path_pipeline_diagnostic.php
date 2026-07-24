<?php

$expectedKey = '9e7b2c6d1f8a4b53c0e6f1a9d7b2c84e';

$key = isset($_GET['key']) ? (string) $_GET['key'] : '';

if (!hash_equals($expectedKey, $key)) {
    http_response_code(404);
    exit;
}

header('Content-Type: text/plain; charset=utf-8');

try {
    require_once __DIR__ . '/includes/application_top.php';

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

    $tokenizer = new OliLetterConfiguratorSvgPathTokenizer();
    $parser = new OliLetterConfiguratorSvgPathParser();
    $interpreter = new OliLetterConfiguratorSvgPathInterpreter();
    $geometryAnalyzer = new OliLetterConfiguratorGeometryAnalyzer();

    $pathProcessingService = new OliLetterConfiguratorPathProcessingService(
        $tokenizer,
        $parser,
        $interpreter,
        $transformationService,
        $geometryAnalyzer
    );

    $pathData = "M 0 0\nL 100 0\nL 100 50\nZ";

    $result = $pathProcessingService->processPath(
        $pathData,
        1.0,
        1.0,
        0.0,
        0.0,
        0.0
    );

    echo "PATH PIPELINE OK\n";
    print_r($result);
} catch (Throwable $exception) {
    http_response_code(500);
    echo "PATH PIPELINE ERROR\n";
    echo 'Exception class: ' . get_class($exception) . "\n";
    echo 'Message: ' . $exception->getMessage() . "\n";
}
