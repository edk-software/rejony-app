<?php

use PHPUnit\Framework\TestCase;
use WIO\EdkBundle\Model\IndexedCoordinates;

final class IndexedCoordinatesTest extends TestCase
{
    public function testCreatingCoordinatesPairUsingConstructorAndGetItUsingGetters()
    {
        $coordinates = new IndexedCoordinates(3, -2, 5.3);
        $this->assertEquals(3, $coordinates->getIndex());
        $this->assertEquals(-2, $coordinates->getLatitude());
        $this->assertEquals(5.3, $coordinates->getLongitude());
    }

    public function testCreatingCoordinatesPairUsingStaticMethodAndGetItUsingJsonMethod()
    {
        $coordinates = IndexedCoordinates::createFromDb([3, -2, 5.3]);
        $this->assertEquals([3, -2, 5.3], $coordinates->jsonSerialize());
    }
}
