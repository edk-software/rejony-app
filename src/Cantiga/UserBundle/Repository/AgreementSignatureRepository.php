<?php

namespace Cantiga\UserBundle\Repository;

use Cantiga\CoreBundle\Repository\CommonRepository;
use Cantiga\Metamodel\Capabilities\EditableRepositoryInterface;
use Cantiga\Metamodel\Capabilities\InsertableRepositoryInterface;
use Cantiga\Metamodel\Capabilities\RemovableRepositoryInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\UserBundle\Entity\Agreement;
use Cantiga\UserBundle\Entity\AgreementSignature;
use Cantiga\UserBundle\UserTables;
use DateTime;
use Doctrine\DBAL\Exception\InvalidArgumentException;

class AgreementSignatureRepository extends CommonRepository implements InsertableRepositoryInterface,
    EditableRepositoryInterface, RemovableRepositoryInterface
{
    const FIELDS = [
        'agreementId',
        'signerId',
        'projectId',
        'firstName',
        'lastName',
        'town',
        'zipCode',
        'street',
        'houseNo',
        'flatNo',
        'pesel',
        'dateOfBirth',
        'signedAt',
        'createdAt',
        'createdBy',
        'updatedAt',
        'updatedBy',
    ];

    /**
     * Get item
     *
     * @param int $id ID
     *
     * @return AgreementSignature|null
     */
    public function getItem($id)
    {
        $agreementSignature = new AgreementSignature();
        $item = $this->conn->fetchAssoc('
            SELECT ags.id, ' . self::getFieldList() . '
            FROM ' . UserTables::AGREEMENTS_SIGNATURES_TBL . ' ags
            WHERE ags.id = :id
        ', [
            ':id' => $id,
        ]);
        if (!$item) {
            return null;
        }
        DataMappers::fromArray($agreementSignature, $item, 'ags');
        self::setId($agreementSignature, $item['id']);

        return $agreementSignature;
    }

    /**
     * Get unsigned by user in project
     *
     * @param int|string $userId    user ID
     * @param int|string $projectId project ID
     *
     * @return AgreementSignature[]
     */
    public function getUnsignedByUserInProject($userId, $projectId) : array
    {
        $items = $this->conn->fetchAll('
            SELECT ags.id, ' . self::getFieldList() . ', ' . AgreementRepository::getFieldList() . '
            FROM ' . UserTables::AGREEMENTS_SIGNATURES_TBL . ' ags
            INNER JOIN ' . UserTables::AGREEMENTS_TBL . ' ag
            ON ags.agreementId = ag.id
            WHERE ags.signerId = :signerId AND ags.projectId = :projectId AND ags.signedAt IS NULL AND
                (ag.projectId = :projectId OR ag.projectId IS NULL)
            ORDER BY ag.title ASC, ag.createdAt ASC
        ', [
            ':projectId' => (int) $projectId,
            ':signerId' => (int) $userId,
        ]);

        return array_map(function ($item) {
            $agreementSignature = new AgreementSignature();
            DataMappers::fromArray($agreementSignature, $item, 'ags');
            self::setId($agreementSignature, $item['id']);
            $agreement = new Agreement();
            DataMappers::fromArray($agreement, $item, 'ag');
            self::setId($agreement, $item['ags_agreementId']);
            $agreementSignature->setAgreement($agreement);
            return $agreementSignature;
        }, $items);
    }

    /**
     * Get last signed
     *
     * @param int|string $userId user ID
     *
     * @return AgreementSignature|null
     */
    public function getLastSigned($userId)
    {
        $item = $this->conn->fetchAssoc('
            SELECT ags.id, ' . self::getFieldList() . '
            FROM ' . UserTables::AGREEMENTS_SIGNATURES_TBL . ' ags
            WHERE ags.signerId = :signerId AND ags.signedAt IS NOT NULL
            ORDER BY ags.signedAt DESC
            LIMIT 1
        ', [
            ':signerId' => (int) $userId,
        ]);
        if (empty($item)) {
            return null;
        }
        $agreementSignature = new AgreementSignature();
        DataMappers::fromArray($agreementSignature, $item, 'ags');
        self::setId($agreementSignature, $item['id']);

        return $agreementSignature;
    }

    /**
     * Get last signed
     *
     * @param int|string $userId user ID
     *
     * @param int|string $projectId project ID
     *
     * @return AgreementSignature|null
     */
    public function getLastSignedByProject($userId, $projectId)
    {
        $item = $this->conn->fetchAssoc('
            SELECT ags.id, ' . self::getFieldList() . '
            FROM ' . UserTables::AGREEMENTS_SIGNATURES_TBL . ' ags
            WHERE ags.signerId = :signerId AND ags.signedAt IS NOT NULL AND ags.projectId = :projectId
            ORDER BY ags.signedAt DESC
            LIMIT 1
        ', [
            ':signerId' => (int) $userId,
            ':projectId' => (int) $projectId,
        ]);
        if (empty($item)) {
            return null;
        }
        $agreementSignature = new AgreementSignature();
        DataMappers::fromArray($agreementSignature, $item, 'ags');
        self::setId($agreementSignature, $item['id']);

        return $agreementSignature;
    }

    /**
     * Insert
     *
     * @param AgreementSignature $agreementSignature agreement signature
     *
     * @return int
     *
     * @throws ModelException
     */
    public function insert($agreementSignature)
    {
        if ($agreementSignature->getId()) {
            throw new ModelException('Agreement signature record which already exists in database can not be added again.');
        }
        $agreementSignature->setCreatedAt(time());
        $affectedRowsNumber = $this->conn->insert(UserTables::AGREEMENTS_SIGNATURES_TBL, DataMappers::pick(
            $agreementSignature, self::FIELDS
        ));
        if ($affectedRowsNumber !== 1) {
            throw new ModelException('An error occurred during agreement signature record inserting.');
        }
        self::setId($agreementSignature, $this->conn->lastInsertId());

        return $agreementSignature->getId();
    }

    /**
     * Update
     *
     * @param AgreementSignature $agreementSignature agreement signature
     *
     * @return self
     */
    public function update($agreementSignature) : self
    {
        $agreementSignature->setUpdatedAt(time());
        $this->conn->update(UserTables::AGREEMENTS_SIGNATURES_TBL, array_map(function ($value) {
            if ($value instanceof DateTime) {
                return $value->format('Y-m-d');
            }
            return $value;
        }, DataMappers::pick($agreementSignature, self::FIELDS)), DataMappers::pick($agreementSignature, [ 'id' ]));

        return $this;
    }

    /**
     * Delete
     *
     * @param AgreementSignature $agreementSignature agreement signature
     *
     * @return self
     *
     * @throws InvalidArgumentException
     */
    public function delete($agreementSignature) : self
    {
        $this->conn->delete(UserTables::AGREEMENTS_SIGNATURES_TBL, DataMappers::pick($agreementSignature, [ 'id' ]));

        return $this;
    }

    /**
     * Delete by agreement ID
     *
     * @param int $agreementId agreement ID
     *
     * @return self
     *
     * @throws InvalidArgumentException
     */
    public function deleteByAgreementId($agreementId)
    {
        $this->conn->delete(UserTables::AGREEMENTS_SIGNATURES_TBL, [ 'agreementId' => $agreementId ]);

        return $this;
    }

    /**
     * Remove
     *
     * @param AgreementSignature $agreementSignature agreement signature
     *
     * @return self
     *
     * @throws InvalidArgumentException
     */
    public function remove($agreementSignature) : self
    {
        return $this->delete($agreementSignature);
    }

    /**
     * Get field list
     *
     * @return string
     */
    public static function getFieldList()
    {
        return self::createFieldList(self::FIELDS, 'ags', 'ags');
    }
}
