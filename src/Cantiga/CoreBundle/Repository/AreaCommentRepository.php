<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Cantiga contributors.
 *
 * Cantiga Project is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cantiga Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
declare(strict_types=1);
namespace Cantiga\CoreBundle\Repository;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Message;
use Cantiga\Metamodel\Transaction;
use Cantiga\Metamodel\TimeFormatterInterface;
use Doctrine\DBAL\Connection;
use Exception;
use PDO;


/**
 * Manages the areas in the given parent place (project or group).
 */
class AreaCommentRepository
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
	
	public function getFeedback($id)
    {
        $this->transaction->requestTransaction();
        try {
            return [
                'status' => 1,
                'messageNum' => 0,
                'messages' => $this->getFeedbackById($id, $this->conn, $this->timeFormatter)
            ];
        } catch(Exception $ex) {
            $this->transaction->requestRollback();
            throw $ex;
        }
    }

    public function addMessage($id, Message $message)
    {
        $this->addMessageById($id, $this->conn, $message);
    }

    private function getFeedbackById($id, Connection $conn, TimeFormatterInterface $timeFormatter)
    {
        $stmt = $conn->prepare(
            'SELECT m.`createdAt`, m.`message`, u.`id` AS `user_id`, u.`name`, u.`avatar` FROM `'.CoreTables::AREA_COMMENT_TBL.'` m '
            .'INNER JOIN `'.CoreTables::USER_TBL.'` u ON u.`id` = m.`userId` '
            .'WHERE m.`areaId` = :id ORDER BY m.`id`'
        );
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $result = [];
        $direction = 1;
        $previousUser = null;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($previousUser != $row['user_id']) {
                $direction = ($direction == 1 ? 0 : 1);
            }
            $result[] = [
                'message' => $row['message'],
                'time' => $timeFormatter->ago($row['createdAt']),
                'author' => $row['name'],
                'avatar' => $row['avatar'],
                'dir' => $direction,
            ];
            $previousUser = $row['user_id'];
        }
        $stmt->closeCursor();

        return $result;
    }

    private function addMessageById($id, Connection $connection, Message $message)
    {
        if (null !== $message) {
            $connection->insert(
                CoreTables::AREA_COMMENT_TBL,
                [
                    'areaId' => $id,
                    'userId' => $message->getUser()->getId(),
                    'createdAt' => $message->getCreatedAt(),
                    'message' => $message->getMessage(),
                ]
            );
        }
    }

}
