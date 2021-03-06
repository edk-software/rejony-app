<?php

use PHPUnit\Framework\TestCase;
use WIO\EdkBundle\Entity\EdkRoute;
use WIO\EdkBundle\Exception\RouteVerifierResultException;
use WIO\EdkBundle\Model\Coordinates;
use WIO\EdkBundle\Model\IndexedCoordinates;
use WIO\EdkBundle\Model\RouteVerifierResult;

final class RouteVerifierResultTest extends TestCase
{
    /** @var int */
    const MAX_TEXT_FIELD_LENGTH = 65535;

    /** @var int */
    const MAX_MEDIUMTEXT_FIELD_LENGTH = 16777215;

    private function getRouteCharacteristics(array $patch = []): array
    {
        return array_filter(array_merge([
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
        ], $patch), function ($value) {
            return isset($value);
        });
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

    private function assertsCheck(RouteVerifierResult $result, array $routeCharacteristics, array $verificationStatus)
    {
        $this->assertEquals($routeCharacteristics['elevationCharacteristics'], $result->getElevationCharacteristic());
        $this->assertEqualsCoordinatesList(
            $routeCharacteristics['pathCoordinates'], $result->getPathCoordinates(), 'path coordinates'
        );
        $this->assertEqualsCoordinatesList(
            $routeCharacteristics['stations'], $result->getStations(), 'stations'
        );
        $this->assertEqualsCoordinates(
            $routeCharacteristics['pathStart'], $result->getPathStart(), 'path start'
        );
        $this->assertEqualsCoordinates(
            $routeCharacteristics['pathEnd'], $result->getPathEnd(), 'path end'
        );
        $this->assertEquals($verificationStatus, array_merge($result->getVerificationStatus(), [
            'logs' => $result->getVerificationLogs(),
        ]));
    }

    private function assertEqualsCoordinatesList(array $expectedList, array $actualList, $name)
    {
        foreach ($actualList as $index => $actual) {
            $this->assertEqualsCoordinates(
                $expectedList[$index], $actual, sprintf('array element %d in %s', $index, $name)
            );
        }
    }

    private function assertEqualsCoordinates(array $expected, Coordinates $actual, $name)
    {
        if ($actual instanceof IndexedCoordinates) {
            $this->assertEquals(
                (string) $expected['index'], (string) $actual->getIndex(), sprintf('Invalid index of %s.', $name)
            );
        }
        $this->assertEquals(
            (string) $expected['latitude'], (string) $actual->getLatitude(), sprintf('Invalid latitude of %s.', $name)
        );
        $this->assertEquals(
            (string) $expected['longitude'], (string) $actual->getLongitude(),
            sprintf('Invalid longitude of %s.', $name)
        );
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

    public function testInputWithoutElevationCharacteristics()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics([
                'elevationCharacteristics' => null,
            ]),
            'verificationStatus' => $this->getVerificationStatus(),
        ]);
    }

    public function testInputWithoutPathCoordinates()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics([
                'pathCoordinates' => null,
            ]),
            'verificationStatus' => $this->getVerificationStatus(),
        ]);
    }

    public function testInputWithoutPathStart()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics([
                'pathStart' => null,
            ]),
            'verificationStatus' => $this->getVerificationStatus(),
        ]);
    }

    public function testInputWithoutPathEnd()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics([
                'pathEnd' => null,
            ]),
            'verificationStatus' => $this->getVerificationStatus(),
        ]);
    }

    public function testInputWithoutStations()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics([
                'stations' => null,
            ]),
            'verificationStatus' => $this->getVerificationStatus(),
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

    public function testInputWithPathCoordinatesWithoutExpectedParams()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics([
                'pathCoordinates' => [
                    ['latitude' => 2, 'unexpectedParam' => 'abc'],
                ],
            ]),
            'verificationStatus' => $this->getVerificationStatus(),
        ]);
    }

    public function testInputWithStationsWithoutExpectedParams()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics([
                'stations' => [
                    ['latitude' => 2, 'longitude' => 3.2],
                ],
            ]),
            'verificationStatus' => $this->getVerificationStatus(),
        ]);
    }

    public function testInputWithTooManyElevationCharacteristics()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics([
                'elevationCharacteristics' => array_fill(
                    0, RouteVerifierResult::MAX_ELEVATION_CHARACTERISTICS_NUMBER + 1,
                    ['distance' => 2, 'elevation' => 3.2]
                ),
            ]),
            'verificationStatus' => $this->getVerificationStatus(),
        ]);
    }

    public function testInputWithTooManyPathCoordinates()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics([
                'pathCoordinates' => array_fill(
                    0, RouteVerifierResult::MAX_PATH_COORDINATES_NUMBER + 1,
                    ['latitude' => 2, 'longitude' => 3.2]
                ),
            ]),
            'verificationStatus' => $this->getVerificationStatus(),
        ]);
    }

    public function testInputWithTooManyStations()
    {
        $this->expectException(RouteVerifierResultException::class);
        new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics([
                'stations' => array_fill(
                    0, RouteVerifierResult::MAX_STATIONS_NUMBER + 1,
                    ['index' => 12, 'latitude' => 2, 'longitude' => 3.2]
                ),
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
        $this->assertsCheck($result, $routeCharacteristics, $verificationStatus);
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
        $this->assertsCheck($result, $routeCharacteristics, $verificationStatus);
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
        $this->assertsCheck($result, $routeCharacteristics, $verificationStatus);
    }

    public function testCorrectValidInputWithEmptyPathCoordinates()
    {
        $routeCharacteristics = $this->getRouteCharacteristics([
            'pathCoordinates' => [],
        ]);
        $verificationStatus = $this->getVerificationStatus();

        $result = new RouteVerifierResult([
            'routeCharacteristics' => $routeCharacteristics,
            'verificationStatus' => $verificationStatus,
        ]);
        $this->assertTrue($result->isValid());
        $this->assertsCheck($result, $routeCharacteristics, $verificationStatus);
    }

    public function testCorrectValidInputWithEmptyStations()
    {
        $routeCharacteristics = $this->getRouteCharacteristics([
            'stations' => [],
        ]);
        $verificationStatus = $this->getVerificationStatus();

        $result = new RouteVerifierResult([
            'routeCharacteristics' => $routeCharacteristics,
            'verificationStatus' => $verificationStatus,
        ]);
        $this->assertTrue($result->isValid());
        $this->assertsCheck($result, $routeCharacteristics, $verificationStatus);
    }

    public function testCorrectValidInputWithPathStartWithUnexpectedParam()
    {
        $routeCharacteristics = $this->getRouteCharacteristics([
            'pathStart' => ['latitude' => 2.2, 'longitude' => 4.4, 'unexpectedParam' => 23],
        ]);
        $verificationStatus = $this->getVerificationStatus();

        $result = new RouteVerifierResult([
            'routeCharacteristics' => $routeCharacteristics,
            'verificationStatus' => $verificationStatus,
        ]);
        $this->assertTrue($result->isValid());
        $this->assertsCheck($result, $routeCharacteristics, $verificationStatus);
    }

    public function testCorrectValidInputWithStationWithUnexpectedParam()
    {
        $routeCharacteristics = $this->getRouteCharacteristics([
            'stations' => [
                ['index' => 4, 'latitude' => 2.2, 'longitude' => 4.4, 'unexpectedParam' => 23]
            ],
        ]);
        $verificationStatus = $this->getVerificationStatus();

        $result = new RouteVerifierResult([
            'routeCharacteristics' => $routeCharacteristics,
            'verificationStatus' => $verificationStatus,
        ]);
        $this->assertTrue($result->isValid());
        $this->assertsCheck($result, $routeCharacteristics, $verificationStatus);
    }

    public function testCorrectValidInputWithMaximumNumberOfElevationCharacteristics()
    {
        $result = new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics([
                'elevationCharacteristics' => array_fill(
                    0, RouteVerifierResult::MAX_ELEVATION_CHARACTERISTICS_NUMBER,
                    ['distance' => 262.736251263748652615236, 'elevation' => 391.202385176355107982381]
                ),
            ]),
            'verificationStatus' => $this->getVerificationStatus(),
        ]);
        $this->assertTrue($result->isValid());
        $jsonLength = strlen(json_encode($result->getElevationCharacteristic()));
        $this->assertEquals(62001, $jsonLength);
        $this->assertLessThanOrEqual(self::MAX_TEXT_FIELD_LENGTH, $jsonLength);
    }

    public function testCorrectValidInputWithMaximumNumberOfPathCoordinates()
    {
        $result = new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics([
                'pathCoordinates' => array_fill(
                    0, RouteVerifierResult::MAX_PATH_COORDINATES_NUMBER,
                    ['latitude' => 92.034718273439, 'longitude' => -43.28475619284]
                ),
            ]),
            'verificationStatus' => $this->getVerificationStatus(),
        ]);
        $this->assertTrue($result->isValid());
        $jsonLength = strlen(json_encode($result->getPathCoordinates()));
        $this->assertEquals(2900001, $jsonLength);
        $this->assertLessThanOrEqual(self::MAX_MEDIUMTEXT_FIELD_LENGTH, $jsonLength);
    }

    public function testCorrectValidInputWithMaximumNumberOfStations()
    {
        $result = new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics([
                'stations' => array_fill(
                    0, RouteVerifierResult::MAX_STATIONS_NUMBER,
                    ['index' => 12, 'latitude' => 92.034718273439, 'longitude' => -43.28475619284]
                ),
            ]),
            'verificationStatus' => $this->getVerificationStatus(),
        ]);
        $this->assertTrue($result->isValid());
        $jsonLength = strlen(json_encode($result->getStations()));
        $this->assertEquals(57601, $jsonLength);
        $this->assertLessThanOrEqual(self::MAX_TEXT_FIELD_LENGTH, $jsonLength);
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
        $this->assertsCheck($result, $routeCharacteristics, $verificationStatus);
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
        $this->assertsCheck($result, $routeCharacteristics, $verificationStatus);
    }

    public function testCorrectValidInputWithTooLongFloatValues()
    {
        $verificationStatus = $this->getVerificationStatus();

        $result = new RouteVerifierResult([
            'routeCharacteristics' => $this->getRouteCharacteristics([
                'elevationCharacteristics' => [
                    ['distance' => 23.32, 'elevation' => 45.231],
                    ['distance' => 27.938461523746521748597, 'elevation' => 53.93846576873612798376],
                ],
                'pathCoordinates' => [
                    ['latitude' => 2.736512437489, 'longitude' => -23.3847129638],
                    ['latitude' => 37.983, 'longitude' => -9.117324891264],
                ],
                'pathEnd' => ['latitude' => 37.98384756789, 'longitude' => -9.117324891264],
                'pathStart' => ['latitude' => 2.736512437489, 'longitude' => -23.384712963834],
                'stations' => [
                    ['index' => 1, 'latitude' => 9.6235, 'longitude' => 76.97840768271],
                    ['index' => 2, 'latitude' => -8.826194768234, 'longitude' => -23.8172],
                ],
            ]),
            'verificationStatus' => $verificationStatus,
        ]);
        $this->assertTrue($result->isValid());
        $this->assertsCheck($result, $this->getRouteCharacteristics([
            'elevationCharacteristics' => [
                ['distance' => 23.32, 'elevation' => 45.231],
                ['distance' => 27.9384615237465217486, 'elevation' => 53.93846576873612798376],
            ],
            'pathCoordinates' => [
                ['latitude' => 2.736512437, 'longitude' => -23.384712964],
                ['latitude' => 37.983, 'longitude' => -9.117324891],
            ],
            'pathEnd' => ['latitude' => 37.983847568, 'longitude' => -9.117324891],
            'pathStart' => ['latitude' => 2.736512437, 'longitude' => -23.384712964],
            'stations' => [
                ['index' => 1, 'latitude' => 9.6235, 'longitude' => 76.978407683],
                ['index' => 2, 'latitude' => -8.826194768, 'longitude' => -23.8172],
            ],
        ]), $verificationStatus);
    }
}
