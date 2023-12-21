<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint for checking if there are mixed products with FFL products.
 */
class CheckForMixedFFLProducts extends Constraint
{
    public string $hasMixedProducts = '';

    public function getTargets(): array
    {
        return [self::CLASS_CONSTRAINT];
    }
}
