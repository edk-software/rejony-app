<?php

namespace Cantiga\KnowledgeBundle\Repository;

use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\QueryBuilder;

class FaqCategoryRepository extends BaseRepository
{
    public function createDataTable() : DataTable
    {
        $dataTable = new DataTable();
        $dataTable
            ->id('id', 'fc.id')
            ->searchableColumn('name', 'fc.name')
        ;

        return $dataTable;
    }

    public function listData(DataTable $dataTable, array $options = []) : array
    {
        $connection = $this->_em->getConnection();

        $qb = QueryBuilder::select()
            ->field('fc.id', 'id')
            ->field('fc.name', 'name')
            ->from('cantiga_faq_category', 'fc')
        ;
        $countTotal = QueryBuilder::copyWithoutFields($qb)
            ->field('COUNT(id)', 'cnt')
            ->where($dataTable->buildCountingCondition($qb->getWhere()))
            ->fetchCell($connection)
        ;
        $countFiltered = QueryBuilder::copyWithoutFields($qb)
            ->field('COUNT(id)', 'cnt')
            ->where($dataTable->buildFetchingCondition($qb->getWhere()))
            ->fetchCell($connection)
        ;
        $dataTable->processQuery($qb);
        $results = $qb
            ->where($dataTable->buildFetchingCondition($qb->getWhere()))
            ->fetchAll($connection)
        ;

        return $dataTable->createAnswer(
            $countTotal,
            $countFiltered,
            $results
        );
    }
}
