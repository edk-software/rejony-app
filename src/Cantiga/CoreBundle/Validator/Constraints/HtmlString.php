<?php

namespace Cantiga\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class HtmlString extends Constraint
{
    public $messageImproperTags = 'The only allowed HTML tags are {{ tags }}.';
    public $messageTagIncorrectlyClosed = 'Tag {{ tag }} has been closed before opening.';
    public $messageTagUnclosed = 'Tag {{ tag }} hasn\'t been closed.';
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
