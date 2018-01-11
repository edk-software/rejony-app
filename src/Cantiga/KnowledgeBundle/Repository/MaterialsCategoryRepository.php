<?php

namespace Cantiga\KnowledgeBundle\Repository;

use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\QueryBuilder;

/**
 * Materials category repository
 */
class MaterialsCategoryRepository extends BaseRepository
{
    public function createDataTable() : DataTable
    {
        $dataTable = new DataTable();
        $dataTable
            ->id('id', 'mc.id')
            ->searchableColumn('name', 'mc.name')
        ;

        return $dataTable;
    }

    public function listData(DataTable $dataTable, array $options = []) : array
    {
        $connection = $this->_em->getConnection();

        $qb = QueryBuilder::select()
            ->field('mc.id', 'id')
            ->field('mc.name', 'name')
            ->from('cantiga_materials_category', 'mc')
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
