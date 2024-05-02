<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class BanWordsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /** @var BanWords $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        foreach ($constraint->banWords as $banWord) {
            if (str_contains($value, $banWord)) {
                $this->context->buildViolation($constraint->message)
                ->setParameter('{{ banWord }}', $value)
                ->addViolation();
            }
        }

    }
}
