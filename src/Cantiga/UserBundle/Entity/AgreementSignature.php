<?php

namespace Cantiga\UserBundle\Entity;

use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use DateTime;
use Exception;

class AgreementSignature implements IdentifiableInterface
{
    const SEX_MALE = 'm';
    const SEX_FEMALE = 'f';

    private $id;
    private $agreementId;
    private $agreement;
    private $signerId;
    private $projectId;
    private $firstName;
    private $lastName;
    private $town;
    private $zipCode;
    private $street;
    private $houseNo;
    private $flatNo;
    private $pesel;
    private $dateOfBirth;
    private $signedAt;
    private $sentAt;
    private $createdAt;
    private $createdBy;
    private $updatedAt;
    private $updatedBy;

    public function __construct()
    {
        $this->createdAt = time();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAgreementId()
    {
        return $this->agreementId;
    }

    public function setAgreementId($agreementId) : self
    {
        $this->agreementId = $agreementId;

        return $this;
    }

    /** @return Agreement|null */
    public function getAgreement()
    {
        return $this->agreement;
    }

    public function setAgreement(Agreement $agreement) : self
    {
        $this->agreement = $agreement;

        return $this;
    }

    public function getSignerId()
    {
        return $this->signerId;
    }

    public function setSignerId($signerId) : self
    {
        $this->signerId = $signerId;

        return $this;
    }

    public function getProjectId()
    {
        return $this->projectId;
    }

    public function setProjectId($projectId) : self
    {
        $this->projectId = $projectId;

        return $this;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName) : self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName($lastName) : self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getTown()
    {
        return $this->town;
    }

    public function setTown($town) : self
    {
        $this->town = $town;

        return $this;
    }

    public function getZipCode()
    {
        return $this->zipCode;
    }

    public function setZipCode($zipCode) : self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function setStreet($street) : self
    {
        $this->street = $street;

        return $this;
    }

    public function getHouseNo()
    {
        return $this->houseNo;
    }

    public function setHouseNo($houseNo) : self
    {
        $this->houseNo = $houseNo;

        return $this;
    }

    public function getFlatNo()
    {
        return $this->flatNo;
    }

    public function setFlatNo($flatNo) : self
    {
        $this->flatNo = $flatNo;

        return $this;
    }

    public function getPesel()
    {
        return $this->pesel;
    }

    public function setPesel($pesel) : self
    {
        $this->pesel = $pesel;

        if (!empty($pesel)) {
            $this->setDateOfBirth($this->getDateOfBirthFromPesel());
        }

        return $this;
    }

    public function getDateOfBirthFromPesel()
    {
        if (empty($this->pesel)) {
            return null;
        }

        $year = (int) substr($this->pesel, 0, 2);
        $month = (int) substr($this->pesel, 2, 2);
        $day = (int) substr($this->pesel, 4, 2);
        if ($month > 20 && $month < 33) {
            $month -= 20;
            $year += 2000;
        } elseif ($month > 40 && $month < 53) {
            $month -= 40;
            $year += 2100;
        } elseif ($month > 60 && $month < 73) {
            $month -= 60;
            $year += 2200;
        } elseif ($month > 80 && $month < 93) {
            $month -= 80;
            $year += 1800;
        } else {
            $year += 1900;
        }
        try {
            $birthDate = new DateTime($year . '-' . $month . '-' . $day);
        } catch (Exception $e) {
            $birthDate = null;
        }

        return $birthDate;
    }

    public function getSexFromPesel()
    {
        if (empty($this->pesel)) {
            return null;
        }

        $sex = preg_match('#^[02468]$#', substr($this->pesel, 9, 1)) ? self::SEX_FEMALE : self::SEX_MALE;

        return $sex;
    }

    /** @return DateTime|null */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth($dateOfBirth) : self
    {
        if ($dateOfBirth instanceof DateTime) {
            $this->dateOfBirth = $dateOfBirth;
        } elseif (!empty($dateOfBirth) && is_string($dateOfBirth)) {
            $this->dateOfBirth = new DateTime($dateOfBirth);
        } else {
            $this->dateOfBirth = null;
        }

        return $this;
    }

    public function getSignedAt()
    {
        return $this->signedAt;
    }

    public function isSigned() : bool
    {
        return isset($this->signedAt);
    }

    public function setSignedAt($signedAt) : self
    {
        $this->signedAt = $signedAt;

        return $this;
    }

    public function getSentAt()
    {
        return $this->sentAt;
    }

    public function isSent() : bool
    {
        return isset($this->sentAt);
    }

    public function setSentAt($sentAt) : self
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt) : self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function setCreatedBy($createdBy) : self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($updatedAt) : self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy($updatedBy) : self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function canRemove() : bool
    {
        return true;
    }
}
