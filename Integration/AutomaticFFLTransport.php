<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Integration;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Psr\Log\LoggerInterface;
use RefactoredGroup\Bundle\AutomaticFFLBundle\Entity\AutomaticFFLSettings;
use RefactoredGroup\Bundle\AutomaticFFLBundle\Form\Type\AutomaticFFLTransportSettingsType;
use Symfony\Component\HttpFoundation\ParameterBag;

/** Transport for Automatic FFL integration */
class AutomaticFFLTransport implements TransportInterface
{
    /** @var ParameterBag */
    protected $settings;

    public function __construct(
        protected SymmetricCrypterInterface $crypter,
        protected LoggerInterface $logger
    ) {
    }

    public function init(Transport $transportEntity)
    {
        $this->settings = $transportEntity->getSettingsBag();
    }

    /**
     * {@inheritDoc}
     */
    public function getSettingsFormType(): string
    {
        return AutomaticFFLTransportSettingsType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getSettingsEntityFQCN(): string
    {
        return AutomaticFFLSettings::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel(): string
    {
        return 'refactored_group.automatic_ffl.transport.label';
    }
}
