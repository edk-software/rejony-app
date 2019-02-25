<?php

namespace Cantiga\UserBundle\Repository;

use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Repository\CommonRepository;
use Cantiga\Metamodel\Capabilities\EditableRepositoryInterface;
use Cantiga\Metamodel\Capabilities\InsertableRepositoryInterface;
use Cantiga\Metamodel\Capabilities\RemovableRepositoryInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\TimeFormatterInterface;
use Cantiga\Metamodel\Transaction;
use Cantiga\UserBundle\Entity\Agreement;
use Cantiga\UserBundle\Entity\AgreementSignature;
use Cantiga\UserBundle\UserTables;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\InvalidArgumentException;

class AgreementRepository extends CommonRepository implements InsertableRepositoryInterface,
    EditableRepositoryInterface, RemovableRepositoryInterface
{
    const FIELDS = [
        'projectId',
        'title',
        'content',
        'url',
        'summary',
        'createdAt',
        'createdBy',
        'updatedAt',
        'updatedBy',
    ];

    /** @var TimeFormatterInterface */
    private $timeFormatter;

    /**
     * Construct
     *
     * @param Connection             $conn          connection
     * @param Transaction            $transaction   transaction
     * @param TimeFormatterInterface $timeFormatter time formatter
     */
    public function __construct(Connection $conn, Transaction $transaction, TimeFormatterInterface $timeFormatter)
    {
        parent::__construct($conn, $transaction);
        $this->timeFormatter = $timeFormatter;
    }

    /**
     * Get item
     *
     * @param int $id ID
     *
     * @return Agreement|null
     */
    public function getItem($id)
    {
        $agreement = new Agreement();
        $item = $this->conn->fetchAssoc('
            SELECT ag.id, ' . self::getFieldList() . '
            FROM ' . UserTables::AGREEMENTS_TBL . ' ag
            WHERE ag.id = :id
        ', [
            ':id' => $id,
        ]);
        if (!$item) {
            return null;
        }
        DataMappers::fromArray($agreement, $item, 'ag');
        self::setId($agreement, $item['id']);

        return $agreement;
    }

    /**
     * Get all by user in project
     *
     * @param int|string $userId    user ID
     * @param int|string $projectId project ID
     *
     * @return Agreement[]
     */
    public function getAllByUserInProject($userId, $projectId) : array
    {
        $items = $this->conn->fetchAll('
            SELECT ag.id AS ag_id, ' . self::getFieldList() . ',
                ags.id AS ags_id, ' . AgreementSignatureRepository::getFieldList() . '
            FROM ' . UserTables::AGREEMENTS_TBL . ' ag
            LEFT OUTER JOIN ' . UserTables::AGREEMENTS_SIGNATURES_TBL . ' ags
            ON ag.id = ags.agreementId AND ags.projectId = :projectId AND ags.signerId = :signerId
            WHERE ag.projectId = :projectId OR ag.projectId IS NULL
            ORDER BY ag.title ASC, ag.createdAt ASC
        ', [
            ':projectId' => (int) $projectId,
            ':signerId' => (int) $userId,
        ]);

        return array_map(function ($item) {
            $agreement = new Agreement();
            DataMappers::fromArray($agreement, $item, 'ag');
            self::setId($agreement, $item['ag_id']);
            if (isset($item['ags_id'])) {
                $agreementSignature = new AgreementSignature();
                DataMappers::fromArray($agreementSignature, $item, 'ags');
                self::setId($agreementSignature, $item['ags_id']);
                $agreement->addSignature($agreementSignature);
            }
            return $agreement;
        }, $items);
    }

    /**
     * Insert
     *
     * @param Agreement $agreement agreement
     *
     * @return int
     *
     * @throws ModelException
     */
    public function insert($agreement)
    {
        if ($agreement->getId()) {
            throw new ModelException('Agreement record which already exists in database can not be added again.');
        }
        $agreement->setCreatedAt(time());
        $affectedRowsNumber = $this->conn->insert(UserTables::AGREEMENTS_TBL, DataMappers::pick(
            $agreement, self::FIELDS
        ));
        if ($affectedRowsNumber !== 1) {
            throw new ModelException('An error occurred during agreement record inserting.');
        }
        self::setId($agreement, $this->conn->lastInsertId());

        return $agreement->getId();
    }

    /**
     * Update
     *
     * @param Agreement $agreement agreement
     *
     * @return self
     */
    public function update($agreement) : self
    {
        $agreement->setUpdatedAt(time());
        $this->conn->update(UserTables::AGREEMENTS_TBL, DataMappers::pick(
            $agreement, self::FIELDS
        ), DataMappers::pick(
            $agreement, [ 'id' ]
        ));

        return $this;
    }

    /**
     * Delete
     *
     * @param Agreement $agreement agreement
     *
     * @return self
     *
     * @throws InvalidArgumentException
     */
    public function delete($agreement) : self
    {
        $this->conn->delete(UserTables::AGREEMENTS_TBL, DataMappers::pick($agreement, [ 'id' ]));

        return $this;
    }

    /**
     * Remove
     *
     * @param Agreement $agreement agreement
     *
     * @return self
     *
     * @throws InvalidArgumentException
     */
    public function remove($agreement) : self
    {
        return $this->delete($agreement);
    }

    /**
     * Create data table
     *
     * @return DataTable
     */
    public function createDataTable()
    {
        $dt = new DataTable();
        $dt
            ->id('id', 'i.id')
            ->searchableColumn('title', 'i.title')
            ->column('createdAt', 'i.createdAt')
            ->column('updatedAt', 'i.updatedAt')
        ;

        return $dt;
    }

    /**
     * List data
     *
     * @param DataTable    $dataTable data table
     * @param Project|null $project   project
     *
     * @return array
     */
    public function listData(DataTable $dataTable, Project $project = null)
    {
        $qb = QueryBuilder::select()
            ->field('i.id', 'id')
            ->field('i.title', 'title')
            ->field('i.createdAt', 'createdAt')
            ->field('i.updatedAt', 'updatedAt')
            ->from(UserTables::AGREEMENTS_TBL, 'i')
        ;
        if (null === $project) {
            $where = QueryClause::clause('i.`projectId` IS NULL');
        } else {
            $where = QueryClause::clause('i.`projectId` = :projectId', ':projectId', $project->getId());
        }
        $qb->where($where);

        $qb->postprocess(function ($row) {
            $row['createdAtText'] = $this->timeFormatter->ago($row['createdAt']);
            $row['updatedAtText'] = $this->timeFormatter->ago($row['updatedAt']);
            return $row;
        });

        $recordsTotal = QueryBuilder::copyWithoutFields($qb)
            ->field('COUNT(id)', 'cnt')
            ->where($dataTable->buildCountingCondition($qb->getWhere()))
            ->fetchCell($this->conn)
        ;
        $recordsFiltered = QueryBuilder::copyWithoutFields($qb)
            ->field('COUNT(id)', 'cnt')
            ->where($dataTable->buildFetchingCondition($qb->getWhere()))
            ->fetchCell($this->conn)
        ;
        $dataTable->processQuery($qb);

        return $dataTable->createAnswer(
            $recordsTotal,
            $recordsFiltered,
            $qb->where($dataTable->buildFetchingCondition($qb->getWhere()))->fetchAll($this->conn)
        );
    }

    /**
     * Get field list
     *
     * @return string
     */
    public static function getFieldList()
    {
        return self::createFieldList(self::FIELDS, 'ag', 'ag');
    }
}
