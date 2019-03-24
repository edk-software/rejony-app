<?php

namespace WIO\EdkBundle\Entity;

use Cantiga\Metamodel\DataMappers;

class AggrementsStatus
{
    private $areaName;
    private $areaId;
    private $signedAgreementsCount;
    private $assignAgreementsCount;
    private $contract;

    public function getAreaName()
    {
        return $this->areaName;
    }
    public function getAreaId()
    {
        return $this->areaId;
    }
    public function getContract()
    {
        return $this->contract;
    }
    public function getSignedAgreementsCount()
    {
        return $this->signedAgreementsCount;
    }
    public function getAssignAgreementsCount()
    {
        return $this->assignAgreementsCount;
    }
    public function setAreaName($value)
    {
        $this->areaName = $value;
        return $this;
    }
    public function setAreaId($value)
    {
        $this->areaId = $value;
        return $this;
    }
    public function setContract($value)
    {
        $this->contract = $value;
        return $this;
    }
    public function setSignedAgreementsCount($value)
    {
        $this->signedAgreementsCount = $value;
        return $this;
    }
    public function setAssignAgreementsCount($value)
    {
        $this->assignAgreementsCount = $value;
        return $this;
    }

    public function isToBeSigned()
    {
        return $this->getSignedAgreementsCount()<1 && $this->getAssignAgreementsCount() > 0;
    }
    public function isToAddAgreements()
    {
        return $this->getAssignAgreementsCount() == 0;
    }
    public function isContractToBeUpdated()
    {
        return $this->getSignedAgreementsCount() > 0 && $this->contract == false;
    }
    public function isContractToBeDowngrade()
    {
        return $this->getSignedAgreementsCount() == 0 && $this->contract == true;
    }

    public static function fromArray($array, $prefix = '')
    {
        $item = new AggrementsStatus();
        DataMappers::fromArray($item, $array, $prefix);
        return $item;
    }
}