<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Validator\Constraints;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use RefactoredGroup\Bundle\AutomaticFFLBundle\Integration\AutomaticFFLChannelType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Checks that there is only one Automatic FFL integration is activated for organization
 */
class UniqueActiveIntegrationValidator extends ConstraintValidator
{
    public const ALIAS = 'refactored_group.automatic_ffl.validator.unique_active_integration_validator';

    protected DoctrineHelper $doctrineHelper;
    protected TranslatorInterface $translator;

    public function __construct(
        DoctrineHelper $doctrineHelper,
        TranslatorInterface $translator
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueActiveIntegration) {
            throw new UnexpectedTypeException($constraint, UniqueActiveIntegration::class);
        }

        if (!($value instanceof Channel)) {
            return;
        }

        if ($value->getType() !== AutomaticFFLChannelType::TYPE) {
            return;
        }

        $this->validateIntegration($value, $constraint);
    }

    /**
     * @param Channel $integration
     * @param UniqueActiveIntegration $constraint
     */
    protected function validateIntegration(
        Channel $integration,
        UniqueActiveIntegration $constraint
    ): void {
        if (!$this->isUnique($integration)) {
            $this->context->buildViolation(
                $this->translator->trans(
                    $constraint->message
                )
            )->addViolation();
        }
    }

    /**
     * @param Channel $integration
     *
     * @return bool
     */
    public function isUnique(Channel $integration)
    {
        $repository = $this->doctrineHelper->getEntityRepository(Channel::class);

        $activatedIntegration = $repository->findOneBy([
            'type'    => AutomaticFFLChannelType::TYPE,
            'enabled' => true
        ]);

        if ($activatedIntegration && $integration->isEnabled()) {

            if ($activatedIntegration->getId() == $integration->getId()) {
                return true;
            }

            return $integration->getType() !== $activatedIntegration->getType();
        }

        return true;
    }
}
