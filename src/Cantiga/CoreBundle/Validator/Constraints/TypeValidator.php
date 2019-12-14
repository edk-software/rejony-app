<?php

namespace Cantiga\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\TypeValidator as ParentTypeValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TypeValidator extends ParentTypeValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Type) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\Type');
        }

        $type = strtolower($constraint->type);
        if ($type !== 'number') {
            parent::validate($value, $constraint);
            return;
        }

        if (is_int($value) || is_float($value)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $this->formatValue($value))
            ->setParameter('{{ type }}', $constraint->type)
            ->setCode(Type::INVALID_TYPE_ERROR)
            ->addViolation();
    }
}
