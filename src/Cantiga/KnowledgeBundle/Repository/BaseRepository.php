<?php

namespace Cantiga\KnowledgeBundle\Repository;

use Cantiga\CoreBundle\Entity\EntityInterface;
use Cantiga\Metamodel\DataTable;
use Doctrine\ORM\EntityRepository as Repository;

abstract class BaseRepository extends Repository
{
    public function insert(EntityInterface $item, bool $flush = false) : self
    {
        $this->save($item, $flush);

        return $this;
    }

    public function update(EntityInterface $item, bool $flush = false) : self
    {
        $this->save($item, $flush);

        return $this;
    }

    public function delete(EntityInterface $item, bool $flush = false) : self
    {
        $this->_em->remove($item);
        if ($flush) {
            $this->_em->flush($item);
        }

        return $this;
    }

    private function save(EntityInterface $item, bool $flush = false) : self
    {
        $this->_em->persist($item);
        if ($flush) {
            $this->_em->flush($item);
        }

        return $this;
    }

    abstract public function createDataTable() : DataTable;

    abstract public function listData(DataTable $dataTable, array $options = []) : array;
}
