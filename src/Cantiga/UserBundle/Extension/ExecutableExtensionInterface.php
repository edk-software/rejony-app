<?php

namespace Cantiga\UserBundle\Extension;

use Cantiga\Components\Hierarchy\Entity\AbstractProfileView;
use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\CoreBundle\Entity\User;

/**
 * Executes extension's form.
 */
interface ExecutableExtensionInterface
{
    public function execute(AbstractProfileView $member, Membership $membership, User $user, callable $getForm,
        callable $reload);
}
