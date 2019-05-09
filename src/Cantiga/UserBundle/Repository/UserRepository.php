<?php

namespace Cantiga\UserBundle\Repository;

use Cantiga\CoreBundle\Entity\User;
use Cantiga\CoreBundle\Repository\CommonRepository;

class UserRepository extends CommonRepository
{
    /**
     * Get items
     *
     * @param int[] $ids IDs
     *
     * @return User[]
     */
    public function getItems(array $ids)
    {
        $user = User::fetchByIds($this->conn, $ids);

        return $user;
    }

    /**
     * Get item
     *
     * @param int $id ID
     *
     * @return User|null
     */
    public function getItem($id)
    {
        $users = $this->getItems([ $id ]);
        $user = array_shift($users);

        return $user;
    }
}
