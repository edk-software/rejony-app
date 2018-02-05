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
use Cantiga\CoreBundle\Entity\User;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\Metamodel\Transaction;
use Cantiga\Metamodel\TimeFormatterInterface;
use Cantiga\UserBundle\UserTables;
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

    public function getLastCommentsFromAreasOfGroup(Project $project, User $user)
    {
        $items = $this->conn->fetchAll('
            SELECT a.`id` AS `areaId`, a.`name` AS `areaName`, u.`name` AS `username`, u.`avatar`,
                    MAX(c.`createdAt`) AS `createdAt`, c.`message`
                FROM `' . UserTables::PLACE_MEMBERS_TBL . '` pm
                JOIN `' . CoreTables::PLACE_TBL . '` p ON pm.`placeId` = p.`id`
                JOIN `' . CoreTables::GROUP_TBL . '` g ON g.`placeId` = p.`id`
                JOIN `' . CoreTables::AREA_TBL . '` a ON a.`groupId` = g.`id`
                JOIN  (SELECT MAX(`id`) as cid, `areaId` FROM `'.CoreTables::AREA_COMMENT_TBL.'` GROUP BY `areaId`) lastC ON lastC.`areaId` = a.`id` 
                JOIN `' . CoreTables::AREA_COMMENT_TBL . '` c ON c.`id` = lastC.`cid`
                JOIN `' . CoreTables::USER_TBL . '` u ON u.`id` = c.`userId`
                WHERE pm.`userId` = :userId && p.`type`=\'Group\' && p.`rootPlaceId`=:projectPlaceId 
                GROUP BY a.`id`
                ORDER BY a.`name` ASC
            
        ', [
            ':projectPlaceId' => $project->getPlace()->getId(),
            ':userId' => $user->getId(),
        ]);
        foreach ($items as $i => $item) {
            $items[$i] = $this->prepareRequestComment($item);
        }

        return $items;

    }
    private function prepareRequestComment(array $item): array
    {
        if (!array_key_exists('message', $item)) {
            $item['truncatedContent'] = null;
        } elseif (mb_strlen($item['message']) <= 150) {
            $item['truncatedContent'] = $item['message'];
        } else {
            $item['truncatedContent'] = substr($item['message'], 0, 150);
            if (ord($item['truncatedContent']{149}) > 200) {
                $item['truncatedContent'] = substr($item['message'], 0, 149);
            }
            $item['truncatedContent'] .= '...';
        }

        return $item;
    }

}
