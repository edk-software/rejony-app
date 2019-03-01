<?php

namespace Cantiga\UserBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class ContainsPeselValidator extends ConstraintValidator
{
    /**
     * Validate
     *
     * @param string     $value      value
     * @param Constraint $constraint constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }
        try {
            if (!preg_match('#^[0-9]{11}$#', $value)) {
                throw new InvalidArgumentException($constraint->message);
            }
            $step = [ 1, 3, 7, 9, 1, 3, 7, 9, 1, 3 ];
            $sum1 = 0;
            for ($i = 0; $i < 10; $i++) {
                $sum1 += $step[$i] * $value[$i];
            }
            $sum2 = 10 - $sum1 % 10;
            if (($sum2 == 10 ? 0 : $sum2) != $value[10]) {
                throw new InvalidArgumentException($constraint->message);
            }
        } catch (InvalidArgumentException $e) {
            $this->context->buildViolation($e->getMessage())
                ->addViolation();
        }
    }
}
