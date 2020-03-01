<?php

namespace WIO\EdkBundle\Model;

use JsonSerializable;

class Coordinates implements JsonSerializable
{
    /** @var float */
    private $latitude;

    /** @var float */
    private $longitude;

    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public static function createFromDb(array $data)
    {
        return new self($data[0], $data[1]);
    }

    public static function createAvg(...$list): self
    {
        $count = count($list);
        if ($count === 0) {
            return new self(0, 0);
        }
        $latitudeSum = 0;
        $longitudeSum = 0;
        /** @var Coordinates $coordinates */
        foreach ($list as $coordinates) {
            $latitudeSum += $coordinates->getLatitude();
            $longitudeSum += $coordinates->getLongitude();
        }
        return new self($latitudeSum / $count, $longitudeSum / $count);
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function jsonSerialize()
    {
        return [
            $this->getLatitude(),
            $this->getLongitude(),
        ];
    }
}
