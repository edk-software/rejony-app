<?php

namespace WIO\EdkBundle\Entity;

use Cantiga\Metamodel\DataMappers;
use Cantiga\CoreBundle\Entity\AreaStatus;

class AreaRoutesStatus
{
    private $areaId;
    private $areaName;
    private $profilePercent;
    private $statusName;
    private $statusId;
    private $activeRoutesTypes;
    private $activeRoutesCount;

    public function getAreaId()
    {
        return $this->areaId;
    }

    public function getAreaName()
    {
        return $this->areaName;
    }

    public function getProfilePercent()
    {
        return $this->profilePercent;
    }

    public function getStatusName()
    {
        return $this->statusName;
    }

    public function getStatusId()
    {
        return $this->statusId;
    }

    public function getActiveRoutesCount()
    {
        return $this->activeRoutesCount;
    }

    public function getActiveRoutesTypes()
    {
        return $this->activeRoutesTypes;
    }

    public function setAreaId($value)
    {
        $this->areaId = $value;
        return $this;
    }

    public function setAreaName($value)
    {
        $this->areaName = $value;
        return $this;
    }

    public function setProfilePercent($value)
    {
        $this->profilePercent = $value;
        return $this;
    }

    public function setStatusName($value)
    {
        $this->statusName = $value;
        return $this;
    }

    public function setStatusId($value)
    {
        $this->statusId = $value;
        return $this;
    }

    public function setActiveRoutesCount($value)
    {
        $this->activeRoutesCount = $value;
        return $this;
    }

    public function setActiveRoutesTypes($value)
    {
        $this->activeRoutesTypes = $value;
        return $this;
    }

    public function isReadyToBePublication($limit)
    {
        return $this->validateProfile($limit) && $this->validateRoutes();
    }

    public function isCorrectStatus(AreaStatus $publicationStatus, AreaStatus $publicationLikeStatus, $percentLimit)
    {
        if (!$this->isReadyToBePublication($percentLimit)) {
            return $this->statusId <> $publicationStatus->getId() && $this->statusId <> $publicationLikeStatus->getId();
        }
        else {
            $destinationStatus = $this->getNewAreaStatus($publicationStatus, $publicationStatus);
            return $this->statusId == $destinationStatus;
        }
    }

    public function getNewAreaStatus(AreaStatus $publicationStatus, AreaStatus $publicationLikeStatus)
    {
        if ($this->isFullArea())
        {
            return $publicationStatus;
        }

        return $publicationLikeStatus;
    }

    private function validateProfile($limit)
    {
        return $this->profilePercent >= $limit;
    }

    private function validateRoutes()
    {
        return $this->activeRoutesCount > 0;
    }

    private function isFullArea()
    {
        return $this->validateRoutes() && $this->activeRoutesCount > $this->activeRoutesTypes;
    }

    public static function fromArray($array, $prefix = '')
    {
        $item = new AreaRoutesStatus();
        DataMappers::fromArray($item, $array, $prefix);
        return $item;
    }
}