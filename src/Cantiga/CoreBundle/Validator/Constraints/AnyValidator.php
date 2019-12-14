<?php

namespace Cantiga\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AnyValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Any) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\Any');
        }

        $context = $this->context;
        $validator = $context->getValidator();
        foreach ($constraint->constraints as $currentConstraint) {
            $violations = $validator->validate($value, $currentConstraint);
            if ($violations->count() === 0) {
                return;
            }
        }

        $validator
            ->inContext($context)
            ->validate($value, $constraint->constraints)
        ;
    }
}
