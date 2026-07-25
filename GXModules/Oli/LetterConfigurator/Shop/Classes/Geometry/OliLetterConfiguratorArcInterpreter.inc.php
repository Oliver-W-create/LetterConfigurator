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
        $centerData = $this->calculateCenter(
            $startPoint,
            $endPoint,
            $rx,
            $ry,
            $rotation,
            $x1Prime,
            $y1Prime,
            (bool)$largeArcFlag,
            (bool)$sweepFlag
        );
        $center = $centerData['center'];
        $cxPrime = $centerData['cxPrime'];
        $cyPrime = $centerData['cyPrime'];
        $angleData = $this->calculateAngles(
            $rx,
            $ry,
            $x1Prime,
            $y1Prime,
            $cxPrime,
            $cyPrime,
            (bool)$sweepFlag
        );
        $startAngle = $angleData['startAngle'];
        $deltaAngle = $angleData['deltaAngle'];

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

    /**
     * @return array<string, mixed>
     */
    private function calculateCenter(
        OliLetterConfiguratorPoint $startPoint,
        OliLetterConfiguratorPoint $endPoint,
        float $rx,
        float $ry,
        float $rotation,
        float $x1Prime,
        float $y1Prime,
        bool $largeArc,
        bool $sweep
    ) {
        if ($rx == 0.0 || $ry == 0.0) {
            return [
                'center' => new OliLetterConfiguratorPoint(
                    ($startPoint->getX() + $endPoint->getX()) / 2,
                    ($startPoint->getY() + $endPoint->getY()) / 2
                ),
                'cxPrime' => 0.0,
                'cyPrime' => 0.0,
            ];
        }

        $rxSquared = $rx * $rx;
        $rySquared = $ry * $ry;
        $x1PrimeSquared = $x1Prime * $x1Prime;
        $y1PrimeSquared = $y1Prime * $y1Prime;
        $numerator = ($rxSquared * $rySquared)
            - ($rxSquared * $y1PrimeSquared)
            - ($rySquared * $x1PrimeSquared);
        $denominator = ($rxSquared * $y1PrimeSquared)
            + ($rySquared * $x1PrimeSquared);
        $factor = 0.0;
        if ($denominator > 0.0) {
            $sign = $largeArc === $sweep ? -1.0 : 1.0;
            $factor = $sign * sqrt(max(0.0, $numerator / $denominator));
        }

        $cxPrime = $factor * (($rx * $y1Prime) / $ry);
        $cyPrime = $factor * (-($ry * $x1Prime) / $rx);
        $rotationInRadians = $this->degreesToRadians($rotation);
        $cosRotation = cos($rotationInRadians);
        $sinRotation = sin($rotationInRadians);
        $centerX = ($cosRotation * $cxPrime)
            - ($sinRotation * $cyPrime)
            + (($startPoint->getX() + $endPoint->getX()) / 2);
        $centerY = ($sinRotation * $cxPrime)
            + ($cosRotation * $cyPrime)
            + (($startPoint->getY() + $endPoint->getY()) / 2);

        return [
            'center' => new OliLetterConfiguratorPoint($centerX, $centerY),
            'cxPrime' => $cxPrime,
            'cyPrime' => $cyPrime,
        ];
    }

    /**
     * @return array<string, float>
     */
    private function calculateAngles(
        float $rx,
        float $ry,
        float $x1Prime,
        float $y1Prime,
        float $cxPrime,
        float $cyPrime,
        bool $sweep
    ) {
        if ($rx == 0.0 || $ry == 0.0) {
            return ['startAngle' => 0.0, 'deltaAngle' => 0.0];
        }

        $startVectorX = ($x1Prime - $cxPrime) / $rx;
        $startVectorY = ($y1Prime - $cyPrime) / $ry;
        $endVectorX = (-$x1Prime - $cxPrime) / $rx;
        $endVectorY = (-$y1Prime - $cyPrime) / $ry;
        $startAngle = $this->vectorAngle(
            1.0,
            0.0,
            $startVectorX,
            $startVectorY
        );
        $deltaAngle = $this->vectorAngle(
            $startVectorX,
            $startVectorY,
            $endVectorX,
            $endVectorY
        );

        if (!$sweep && $deltaAngle > 0.0) {
            $deltaAngle -= 2 * M_PI;
        } elseif ($sweep && $deltaAngle < 0.0) {
            $deltaAngle += 2 * M_PI;
        }

        return [
            'startAngle' => $startAngle,
            'deltaAngle' => $deltaAngle,
        ];
    }

    /**
     * @return float
     */
    private function vectorAngle(
        float $ux,
        float $uy,
        float $vx,
        float $vy
    ) {
        return atan2(
            ($ux * $vy) - ($uy * $vx),
            ($ux * $vx) + ($uy * $vy)
        );
    }
}
