<?php

use PHPUnit\Framework\TestCase;
use WIO\EdkBundle\Model\Coordinates;

final class CoordinatesTest extends TestCase
{
    public function testCreatingCoordinatesPairUsingConstructorAndGetItUsingGetters()
    {
        $coordinates = new Coordinates(-2, 5.3);
        $this->assertEquals(-2, $coordinates->getLatitude());
        $this->assertEquals(5.3, $coordinates->getLongitude());
    }

    public function testCreatingCoordinatesPairUsingStaticMethodAndGetItUsingJsonMethod()
    {
        $coordinates = Coordinates::createFromDb([-2, 5.3]);
        $this->assertEquals([-2, 5.3], $coordinates->jsonSerialize());
    }

    public function testEmptyListOfCoordinatesPairsToCreateAverageCoordinates()
    {
        $avgCoordinates = Coordinates::createAvg();
        $this->assertEquals([0, 0], $avgCoordinates->jsonSerialize());
    }

    public function testOneElementListOfCoordinatesPairsToCreateAverageCoordinates()
    {
        $avgCoordinates = Coordinates::createAvg(
            new Coordinates(3.5, 8)
        );
        $this->assertEquals([3.5, 8], $avgCoordinates->jsonSerialize());
    }

    public function testTwoElementListOfCoordinatesPairsToCreateAverageCoordinates()
    {
        $avgCoordinates = Coordinates::createAvg(
            new Coordinates(-2, 3.3),
            new Coordinates(4, -6.6)
        );
        $this->assertEquals([1, -1.65], $avgCoordinates->jsonSerialize());
    }

    public function testFourElementListOfCoordinatesPairsToCreateAverageCoordinates()
    {
        $avgCoordinates = Coordinates::createAvg(
            new Coordinates(4, 4),
            new Coordinates(-2, -2),
            new Coordinates(-3, 4),
            new Coordinates(5, -2)
        );
        $this->assertEquals([1, 1], $avgCoordinates->jsonSerialize());
    }
}
