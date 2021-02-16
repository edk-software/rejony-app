<?php

namespace WIO\EdkBundle\Exception;

use Cantiga\Metamodel\Exception\ModelException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

class RouteVerifierResultException extends ModelException
{
    /** @var ConstraintViolationListInterface */
    private $violations;

    /**
     * Construct
     *
     * @param ConstraintViolationListInterface $violations violations
     * @param int                              $code       code
     * @param Throwable|null                   $previous   previous
     */
    public function __construct($violations, $code = 0, Throwable $previous = null)
    {
        parent::__construct('Constraint violations occurred.', $code, $previous);
        $this->violations = $violations;
    }

    /**
     * Get violations
     *
     * @return ConstraintViolationListInterface
     */
    public function getViolations()
    {
        return $this->violations;
    }
}
