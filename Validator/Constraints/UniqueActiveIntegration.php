<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Automatic FFL UniqueActiveIntegration class
 */
class UniqueActiveIntegration extends Constraint
{
    /**
     * @var string
     */
    public $message = 'refactored_group.automatic_ffl.integration.validator.unique_active_integration';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return UniqueActiveIntegrationValidator::ALIAS;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
