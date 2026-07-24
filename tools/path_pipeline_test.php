<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$geometryDirectory = dirname(__DIR__)
    . '/GXModules/Oli/LetterConfigurator/Shop/Classes/Geometry';

$classFiles = [
    'OliLetterConfiguratorGeometryException.inc.php',
    'OliLetterConfiguratorPoint.inc.php',
    'OliLetterConfiguratorLineSegment.inc.php',
    'OliLetterConfiguratorPathGeometry.inc.php',
    'OliLetterConfiguratorSvgPathToken.inc.php',
    'OliLetterConfiguratorSvgPathCommand.inc.php',
    'OliLetterConfiguratorGeometryAnalysisResult.inc.php',
    'OliLetterConfiguratorPointTranslator.inc.php',
    'OliLetterConfiguratorLineSegmentTranslator.inc.php',
    'OliLetterConfiguratorPathGeometryTranslator.inc.php',
    'OliLetterConfiguratorPointScaler.inc.php',
    'OliLetterConfiguratorLineSegmentScaler.inc.php',
    'OliLetterConfiguratorPathGeometryScaler.inc.php',
    'OliLetterConfiguratorPointRotator.inc.php',
    'OliLetterConfiguratorLineSegmentRotator.inc.php',
    'OliLetterConfiguratorPathGeometryRotator.inc.php',
    'OliLetterConfiguratorGeometryTransformationService.inc.php',
    'OliLetterConfiguratorSvgPathTokenizer.inc.php',
    'OliLetterConfiguratorSvgPathParser.inc.php',
    'OliLetterConfiguratorSvgPathInterpreter.inc.php',
    'OliLetterConfiguratorGeometryAnalyzer.inc.php',
    'OliLetterConfiguratorPathProcessingService.inc.php',
];

foreach ($classFiles as $classFile) {
    require_once $geometryDirectory . '/' . $classFile;
}

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

print_r($result);
