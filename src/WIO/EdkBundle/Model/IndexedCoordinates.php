<?php

namespace WIO\EdkBundle\Model;

class IndexedCoordinates extends Coordinates
{
    /** @var int */
    private $index;

    public function __construct(int $index, float $latitude, float $longitude)
    {
        $this->index = $index;
        parent::__construct($latitude, $longitude);
    }

    public static function createFromDb(array $data)
    {
        return new self($data[0], $data[1], $data[2]);
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function jsonSerialize()
    {
        return [
            $this->getIndex(),
            $this->getLatitude(),
            $this->getLongitude(),
        ];
    }
}
