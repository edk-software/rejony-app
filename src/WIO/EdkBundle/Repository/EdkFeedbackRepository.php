<?php

namespace WIO\EdkBundle\Repository;

use Cantiga\Metamodel\Capabilities\EditableRepositoryInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\Form\EntityTransformerInterface;
use Cantiga\Metamodel\TimeFormatterInterface;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use ReflectionObject;
use WIO\EdkBundle\EdkTables;
use WIO\EdkBundle\Entity\EdkFeedback;

class EdkFeedbackRepository implements EditableRepositoryInterface, EntityTransformerInterface
{
	/**
	 * @var Connection 
	 */
	private $conn;
	/**
	 * @var Transaction
	 */
	private $transaction;
	/**
	 * @var TimeFormatterInterface
	 */
	private $timeFormatter;
	
	public function __construct(Connection $conn, Transaction $transaction, TimeFormatterInterface $timeFormatter)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->timeFormatter = $timeFormatter;
	}

	public function getItem($id) : EdkFeedback
	{
		// @TODO: Add this functionality
		throw new ItemNotFoundException('The specified route has not been found.');
	}

	public function insert(EdkFeedback $feedback) : self {
		if ($feedback->getId()) {
			throw new ModelException('Feedback record which already exists in database can not be added again.');
		}
		$affectedRowsNumber = $this->conn->insert(EdkTables::FEEDBACK_TBL, DataMappers::pick(
			$feedback,
			[
				'route',
				'content',
				'createdAt',
			]
		));
		if ($affectedRowsNumber !== 1) {
			throw new ModelException('An error occured during feedback record inserting.');
		}
		$reflection = new ReflectionObject($feedback);
		$property = $reflection->getProperty('id');
		$property->setAccessible(true);
		$property->setValue($feedback, $this->conn->lastInsertId());
		return $this;
	}
	
	public function update($item) : self
	{
		// @TODO: Add this functionality
		return $this;
	}

	public function transformToEntity($key)
	{
		if (null !== $key) {
			return $this->getItem($key);
		}
		return null;
	}

	public function transformToKey($entity)
	{
		if (!empty($entity)) {
			/** @var EdkFeedback $entity */
			return $entity->getId();
		}
		return null;
	}
}
