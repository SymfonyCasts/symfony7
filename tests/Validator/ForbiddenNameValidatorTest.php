<?php

namespace App\Tests\Validator;

use App\Validator\ForbiddenName;
use App\Validator\ForbiddenNameValidator;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ForbiddenNameValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): ConstraintValidatorInterface
    {
        return new ForbiddenNameValidator(['bad', 'forbidden']);
    }

    public function testAllowedName(): void
    {
        $this->validate('foo', new ForbiddenName());

        $this->assertNoViolation();
    }

    public function testForbiddenName(): void
    {
        $this->validate('bad', new ForbiddenName());

        $this->buildViolation('The name "{{ value }}" is not allowed to be registered on our shop - go away!')
            ->setParameter('{{ value }}', 'bad')
            ->assertRaised();
    }
}
