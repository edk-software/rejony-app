<?php

namespace Cantiga\KnowledgeBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class HtmlString extends Constraint
{
    public $message = 'The only allowed HTML tags are {{ tags }}.';
    public $allowableTags = [];

    public function getDefaultOption()
    {
        return 'allowableTags';
    }

    public function getRequiredOptions()
    {
        return [
            'allowableTags',
        ];
    }
}
