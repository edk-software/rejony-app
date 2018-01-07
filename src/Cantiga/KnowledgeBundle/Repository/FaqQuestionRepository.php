<?php

namespace Cantiga\KnowledgeBundle\Repository;

use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;

class FaqQuestionRepository extends BaseRepository
{
    public function createDataTable() : DataTable
    {
        $dataTable = new DataTable();
        $dataTable
            ->id('id', 'fq.id')
            ->searchableColumn('topic', 'fq.topic')
        ;

        return $dataTable;
    }

    public function listData(DataTable $dataTable, array $options = []) : array
    {
        $connection = $this->_em->getConnection();

        $qb = QueryBuilder::select()
            ->field('fq.id', 'id')
            ->field('fq.topic', 'topic')
            ->field('fq.category_id', 'categoryId')
            ->from('cantiga_faq_question', 'fq')
            ->where(QueryClause::clause('fq.category_id = :categoryId', ':categoryId',
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
