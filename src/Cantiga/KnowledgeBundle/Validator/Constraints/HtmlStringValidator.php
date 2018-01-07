<?php

namespace Cantiga\KnowledgeBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class HtmlStringValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }
        /** @var HtmlString $constraint */
        if ($value != strip_tags($value, implode('', $constraint->allowableTags))) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ tags }}', implode(', ', $constraint->allowableTags))
                ->addViolation()
            ;
        }
    }
}
