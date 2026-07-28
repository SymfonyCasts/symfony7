<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class ForbiddenNameValidator extends ConstraintValidator
{
    public function __construct(
        private array $forbiddenNames = [
            'Death Star',
            'Imperial Star Destroyer',
            'Executor',
        ],
    ) {

    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var ForbiddenName $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        if (!in_array(strtolower($value), array_map('strtolower', $this->forbiddenNames), true)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation()
        ;
    }
}
