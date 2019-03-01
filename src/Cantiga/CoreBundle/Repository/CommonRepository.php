<?php

namespace Cantiga\CoreBundle\Repository;

use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use ReflectionObject;

// Doctrine ORM should be used in whole project instead of working directly on DBAL
abstract class CommonRepository
{
    /** @var Connection */
    protected $conn;

    /** @var Transaction */
    protected $transaction;

    public function __construct(Connection $conn, Transaction $transaction)
    {
        $this->conn = $conn;
        $this->transaction = $transaction;
    }

    protected static function createFieldList(array $fields, string $alias, string $prefix) : string
    {
        return implode(', ', array_map(function ($name) use ($alias, $prefix) {
            return $alias . '.`' . $name . '` AS `' . $prefix . '_' . $name . '`';
        }, $fields));
    }

    protected static function setId(IdentifiableInterface $object, $id)
    {
        $reflection = new ReflectionObject($object);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($object, (int) $id);
    }
}
