<?php

namespace Cantiga\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Composite;

class Any extends Composite
{
    public $constraints = [];

    public function getDefaultOption()
    {
        return 'constraints';
    }

    public function getRequiredOptions()
    {
        return ['constraints'];
    }

    protected function getCompositeOption()
    {
        return 'constraints';
    }
}
