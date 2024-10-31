<?php

namespace CTDNikiru\DtoRequestResolver\Exceptions;

use Exception;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ConstraintViolationException extends Exception
{
    private ConstraintViolationListInterface $constraintViolationList;

    public function __construct(ConstraintViolationListInterface $constraintViolationList)
    {
        $this->constraintViolationList = $constraintViolationList;
    }

    public function getConstraintViolationList(): ConstraintViolationListInterface
    {
        return $this->constraintViolationList;
    }
}
