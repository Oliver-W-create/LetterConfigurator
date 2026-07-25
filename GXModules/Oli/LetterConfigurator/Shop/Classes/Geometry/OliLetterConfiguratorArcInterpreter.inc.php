<?php

/**
 * Handles elliptical arc SVG path commands.
 */
class OliLetterConfiguratorArcInterpreter
{
    /** @var OliLetterConfiguratorPointTranslator */
    private $pointTranslator;

    public function __construct(
        ?OliLetterConfiguratorPointTranslator $pointTranslator = null
    ) {
        $this->pointTranslator = $pointTranslator
            ?: new OliLetterConfiguratorPointTranslator();
    }

    /**
     * @param OliLetterConfiguratorPoint          $startPoint
     * @param OliLetterConfiguratorSvgPathCommand $pathCommand
     *
     * @return void
     *
     * @throws OliLetterConfiguratorGeometryException
     */
    public function interpret(
        OliLetterConfiguratorPoint $startPoint,
        OliLetterConfiguratorSvgPathCommand $pathCommand
    ) {
        $parameters = $pathCommand->getParameters();
        if (count($parameters) !== 7) {
            throw new OliLetterConfiguratorGeometryException(
                'SVG arc command requires exactly 7 parameters.'
            );
        }

        $rx = abs($parameters[0]);
        $ry = abs($parameters[1]);
        $rotation = $parameters[2];
        $largeArcFlag = $parameters[3];
        $sweepFlag = $parameters[4];
        if (!in_array($largeArcFlag, [0.0, 1.0], true)
            || !in_array($sweepFlag, [0.0, 1.0], true)
        ) {
            throw new OliLetterConfiguratorGeometryException(
                'Invalid SVG arc flags.'
            );
        }
        $endPoint = $this->resolveEndPoint($startPoint, $pathCommand);
        $ellipseCoordinates = $this->rotateToEllipseSpace(
            $startPoint,
            $endPoint,
            $rotation
        );
        $x1Prime = $ellipseCoordinates['x1Prime'];
        $y1Prime = $ellipseCoordinates['y1Prime'];
        $normalizedRadii = $this->normalizeRadii(
            $rx,
            $ry,
            $x1Prime,
            $y1Prime
        );
        $rx = $normalizedRadii['rx'];
        $ry = $normalizedRadii['ry'];

        throw new OliLetterConfiguratorGeometryException(
            'SVG path command A is not implemented yet.'
        );
    }

    /**
     * @param OliLetterConfiguratorPoint          $startPoint
     * @param OliLetterConfiguratorSvgPathCommand $pathCommand
     *
     * @return OliLetterConfiguratorPoint
     */
    public function resolveEndPoint(
        OliLetterConfiguratorPoint $startPoint,
        OliLetterConfiguratorSvgPathCommand $pathCommand
    ) {
        $parameters = $pathCommand->getParameters();
        if ($pathCommand->isRelative()) {
            return $this->pointTranslator->translate(
                $startPoint,
                $parameters[5],
                $parameters[6]
            );
        }

        return new OliLetterConfiguratorPoint(
            $parameters[5],
            $parameters[6]
        );
    }

    /**
     * @param float $degrees
     *
     * @return float
     */
    private function degreesToRadians(float $degrees)
    {
        return deg2rad($degrees);
    }

    /**
     * @param OliLetterConfiguratorPoint $startPoint
     * @param OliLetterConfiguratorPoint $endPoint
     * @param float                      $rotation
     *
     * @return array<string, float>
     */
    private function rotateToEllipseSpace(
        OliLetterConfiguratorPoint $startPoint,
        OliLetterConfiguratorPoint $endPoint,
        float $rotation
    ) {
        $rotationInRadians = $this->degreesToRadians($rotation);
        $cosRotation = cos($rotationInRadians);
        $sinRotation = sin($rotationInRadians);
        $dx = ($startPoint->getX() - $endPoint->getX()) / 2;
        $dy = ($startPoint->getY() - $endPoint->getY()) / 2;

        return [
            'x1Prime' => ($cosRotation * $dx) + ($sinRotation * $dy),
            'y1Prime' => (-$sinRotation * $dx) + ($cosRotation * $dy),
        ];
    }

    /**
     * @param float $rx
     * @param float $ry
     * @param float $x1Prime
     * @param float $y1Prime
     *
     * @return array<string, float>
     */
    private function normalizeRadii(
        float $rx,
        float $ry,
        float $x1Prime,
        float $y1Prime
    ) {
        if ($rx == 0.0 || $ry == 0.0) {
            return ['rx' => $rx, 'ry' => $ry];
        }

        $lambda = (($x1Prime * $x1Prime) / ($rx * $rx))
            + (($y1Prime * $y1Prime) / ($ry * $ry));
        if ($lambda <= 1.0) {
            return ['rx' => $rx, 'ry' => $ry];
        }

        $scale = sqrt($lambda);

        return [
            'rx' => $rx * $scale,
            'ry' => $ry * $scale,
        ];
    }
}
