<?php

namespace Cantiga\UserBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class ContainsPesel extends Constraint
{
    /** @var string */
    public $message = 'This is not a valid PESEL number.';
}
