<?php

namespace Cantiga\KnowledgeBundle\Repository;

use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;

/**
 * Materials file repository
 */
class MaterialsFileRepository extends BaseRepository
{
    public function createDataTable() : DataTable
    {
        $dataTable = new DataTable();
        $dataTable
            ->id('id', 'mf.id')
            ->searchableColumn('name', 'mf.name')
        ;

        return $dataTable;
    }

    public function listData(DataTable $dataTable, array $options = []) : array
    {
        $connection = $this->_em->getConnection();

        $qb = QueryBuilder::select()
            ->field('mf.id', 'id')
            ->field('mf.name', 'name')
            ->field('mf.category_id', 'categoryId')
            ->from('cantiga_materials_file', 'mf')
            ->where(QueryClause::clause('mf.category_id = :categoryId', ':categoryId',
                $options['categoryId']))
        ;
        $countingCondition = $dataTable->buildCountingCondition($qb->getWhere());
        $countTotal = QueryBuilder::copyWithoutFields($qb)
            ->field('COUNT(id)', 'cnt')
            ->where($countingCondition)
            ->fetchCell($connection)
        ;
        $fetchingCondition = $dataTable->buildFetchingCondition($qb->getWhere());
        $countFiltered = QueryBuilder::copyWithoutFields($qb)
            ->field('COUNT(id)', 'cnt')
            ->where($fetchingCondition)
            ->fetchCell($connection)
        ;
        $dataTable->processQuery($qb);
        $results = $qb
            ->where($fetchingCondition)
            ->fetchAll($connection)
        ;

        return $dataTable->createAnswer(
            $countTotal,
            $countFiltered,
            $results
        );
    }
}
