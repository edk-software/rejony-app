<?php

use PHPUnit\Framework\TestCase;
use WIO\EdkBundle\Entity\EdkRoute;
use WIO\EdkBundle\Exception\RouteVerifierResultException;
use WIO\EdkBundle\Model\RouteVerifierResult;

final class RouteVerifierResultTest extends TestCase
{
    private function getRouteCharacteristics(array $patch = []): array
    {
        return array_merge([
            'elevationCharacteristics' => [
                ['distance' => 2, 'elevation' => 1.4],
                ['distance' => 4, 'elevation' => 3.14],
                ['distance' => 6, 'elevation' => 2],
                ['distance' => 8, 'elevation' => 0.8],
                ['distance' => 10, 'elevation' => -0.1],
            ],
            'pathCoordinates' => [
                ['latitude' => 2.2, 'longitude' => 4.4],
                ['latitude' => 4.4, 'longitude' => -1.1],
                ['latitude' => 5, 'longitude' => 0],
                ['latitude' => -1.1, 'longitude' => 2.2],
            ],
            'pathEnd' => ['latitude' => -1.1, 'longitude' => 2.2],
            'pathStart' => ['latitude' => 2.2, 'longitude' => 4.4],
            'stations' => [
                ['index' => 1, 'latitude' => 2.2, 'longitude' => 4.4],
                ['index' => 2, 'latitude' => 4.4, 'longitude' => -1.1],
                ['index' => 3, 'latitude' => -1.1, 'longitude' => 2.2],
            ],
        ], $patch);
    }

    private function getVerificationStatus(array $patch = []): array
    {
        return array_merge([
            'elevationGain' => ['valid' => true, 'value' => 10.3],
            'elevationLoss' => ['valid' => true, 'value' => 15],
            'elevationTotalChange' => ['valid' => true, 'value' => 25.3],
            'logs' => [],
            'numberOfStations' => ['valid' => true],
            'pathLength' => ['valid' => true, 'value' => 40.4],
            'routeType' => ['valid' => true, 'value' => EdkRoute::TYPE_FULL],
            'singlePath' => ['valid' => true],
            'stationsOnPath' => ['valid' => true],
            'stationsOrder' => ['valid' => true],
        ], $patch);
    }

    private function dataAsserts(RouteVerifierResult $result, array $routeCharacteristics, array $verificationStatus)
    {
        $this->assertEquals($routeCharacteristics['elevationCharacteristics'], $result->getElevationCharacteristic());
        $this->assertEquals($routeCharacteristics['pathCoordinates'], $result->getPathCoordinates());
        $this->assertEquals($routeCharacteristics['stations'], $result->getStations());
        $this->assertEquals($verificationStatus, array_merge($result->getVerificationStatus(), [
            'logs' => $result->getVerificationLogs(),
        ]));
    }

    public function testEmptyInput()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([]);
    }

    public function testInputWithoutRouteCharacteristics()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'verificationStatus' => $this->getVerificationStatus(),
        ]);
    }

    public function testInputWithoutVerificationStatus()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics(),
        ]);
    }

    public function testInputWithElevationCharacteristicsWithUnexpectedParam()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics([
                'elevationCharacteristics' => [
                    ['unexpectedParam' => 23],
                ],
            ]),
            'verificationStatus' => $this->getVerificationStatus(),
        ]);
    }

    public function testInputWithElevationCharacteristicsWithInvalidParamType()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics([
                'elevationCharacteristics' => [
                    ['distance' => '2', 'elevation' => 1.4],
                ],
            ]),
            'verificationStatus' => $this->getVerificationStatus(),
        ]);
    }

    public function testInputWithElevationCharacteristicsWithExpectedAndUnexpectedParam()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics([
                'elevationCharacteristics' => [
                    ['distance' => 2, 'elevation' => 1.4, 'unexpectedParam' => 'abc'],
                ],
            ]),
            'verificationStatus' => $this->getVerificationStatus(),
        ]);
    }

    public function testInputWithoutValueInExpectedItem()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics(),
            'verificationStatus' => $this->getVerificationStatus([
                'elevationTotalChange' => ['valid' => true],
            ]),
        ]);
    }

    public function testInputWithInvalidElevationGainValueFormat()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics(),
            'verificationStatus' => $this->getVerificationStatus([
                'elevationGain' => ['valid' => true, 'value' => '45'],
            ]),
        ]);
    }

    public function testInputWithInvalidRouteTypeValueFormat()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics(),
            'verificationStatus' => $this->getVerificationStatus([
                'routeType' => ['valid' => true, 'value' => '1'],
            ]),
        ]);
    }

    public function testInputWithoutRouteType()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics(),
            'verificationStatus' => $this->getVerificationStatus([
                'routeType' => ['valid' => false],
            ]),
        ]);
    }

    public function testInputWithTooSmallRouteType()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics(),
            'verificationStatus' => $this->getVerificationStatus([
                'routeType' => ['valid' => true, 'value' => -1],
            ]),
        ]);
    }

    public function testInputWithTooLargeRouteType()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics(),
            'verificationStatus' => $this->getVerificationStatus([
                'routeType' => ['valid' => true, 'value' => 3],
            ]),
        ]);
    }

    public function testInputWithRouteCharacteristicsWithUnexpectedParams()
    {
        $routeCharacteristics = $this->getRouteCharacteristics([
            'otherParam1' => [],
            'otherParam2' => 123,
        ]);
        $verificationStatus = $this->getVerificationStatus();

        $result = new RouteVerifierResult([
            'otherParam3' => true,
            'routeCharacteristics' => $routeCharacteristics,
            'verificationStatus' => $verificationStatus,
        ]);
        $this->assertTrue($result->isValid());
        $this->dataAsserts($result, $routeCharacteristics, $verificationStatus);
    }

    public function testCorrectInvalidInput()
    {
        $routeCharacteristics = $this->getRouteCharacteristics();
        $verificationStatus = $this->getVerificationStatus([
            'logs' => [
                'Error log 1',
                'Error log 2',
                'Error log 3',
            ],
            'numberOfStations' => ['valid' => false],
            'routeType' => ['valid' => true, 'value' => EdkRoute::TYPE_INSPIRED],
        ]);

        $result = new RouteVerifierResult([
            'routeCharacteristics' => $routeCharacteristics,
            'verificationStatus' => $verificationStatus,
        ]);
        $this->assertFalse($result->isValid());
        $this->dataAsserts($result, $routeCharacteristics, $verificationStatus);
    }

    public function testCorrectValidInputWithEmptyElevationCharacteristic()
    {
        $routeCharacteristics = $this->getRouteCharacteristics([
            'elevationCharacteristics' => [],
        ]);
        $verificationStatus = $this->getVerificationStatus();

        $result = new RouteVerifierResult([
            'routeCharacteristics' => $routeCharacteristics,
            'verificationStatus' => $verificationStatus,
        ]);
        $this->assertTrue($result->isValid());
        $this->dataAsserts($result, $routeCharacteristics, $verificationStatus);
    }

    public function testCorrectValidInputWithUnexpectedParamsInStatusGroups()
    {
        $routeCharacteristics = $this->getRouteCharacteristics();
        $verificationStatus = $this->getVerificationStatus([
            'numberOfStations' => ['customParam' => 123, 'valid' => true],
            'routeType' => ['valid' => true, 'anotherCustomParam' => 'abc', 'value' => EdkRoute::TYPE_UNDEFINED],
            'singlePath' => ['valid' => true, 'value' => 34],
        ]);

        $result = new RouteVerifierResult([
            'routeCharacteristics' => $routeCharacteristics,
            'verificationStatus' => $verificationStatus,
        ]);
        $this->assertTrue($result->isValid());
        $this->dataAsserts($result, $routeCharacteristics, $verificationStatus);
    }

    public function testCorrectValidInput()
    {
        $routeCharacteristics = $this->getRouteCharacteristics();
        $verificationStatus = $this->getVerificationStatus();

        $result = new RouteVerifierResult([
            'routeCharacteristics' => $routeCharacteristics,
            'verificationStatus' => $verificationStatus,
        ]);
        $this->assertTrue($result->isValid());
        $this->dataAsserts($result, $routeCharacteristics, $verificationStatus);
    }
}
