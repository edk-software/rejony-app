<?php

namespace WIO\EdkBundle\Repository;

use Cantiga\CoreBundle\Repository\CommonRepository;
use Cantiga\Metamodel\Capabilities\EditableRepositoryInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\Form\EntityTransformerInterface;
use Cantiga\Metamodel\TimeFormatterInterface;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use WIO\EdkBundle\EdkTables;
use WIO\EdkBundle\Entity\EdkFeedback;

class EdkFeedbackRepository extends CommonRepository implements EditableRepositoryInterface, EntityTransformerInterface
{
	const FIELDS = [
		'route',
		'content',
		'createdAt',
	];

	/** @var TimeFormatterInterface */
	private $timeFormatter;
	
	public function __construct(Connection $conn, Transaction $transaction, TimeFormatterInterface $timeFormatter)
	{
		parent::__construct($conn, $transaction);
		$this->timeFormatter = $timeFormatter;
	}

	public function getItem($id) : EdkFeedback
	{
		$feedback = new EdkFeedback();
		$item = $this->conn->fetchAssoc('
			SELECT id, ' . self::getFieldList() . '
			FROM ' . EdkTables::FEEDBACK_TBL . '
			WHERE id = :id
		', [
			':id' => $id,
		]);
		DataMappers::fromArray($feedback, $item, 'fb');
		self::setId($feedback, $item['id']);
		return $feedback;
	}

	public function insert(EdkFeedback $feedback) : self
	{
		if ($feedback->getId()) {
			throw new ModelException('Feedback record which already exists in database can not be added again.');
		}
		$affectedRowsNumber = $this->conn->insert(EdkTables::FEEDBACK_TBL, DataMappers::pick(
			$feedback, self::FIELDS
		));
		if ($affectedRowsNumber !== 1) {
			throw new ModelException('An error occurred during feedback record inserting.');
		}
		self::setId($feedback, $this->conn->lastInsertId());
		return $this;
	}
	
	public function update($feedback) : self
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

	public static function getFieldList()
	{
		return self::createFieldList(self::FIELDS, 'fb', 'fb');
	}
}
