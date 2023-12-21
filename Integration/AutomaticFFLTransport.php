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
    const API_PRODUCTION_URL = 'https://app.automaticffl.com/store-front/api';
    const API_SANDBOX_URL = 'https://app-stage.automaticffl.com/store-front/api';
    const GOOGLE_MAPS_API_URL = 'https://maps.googleapis.com/maps/api/js';

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

    public function getBaseUrl(bool $isSandBoxMode)
    {
        if ($isSandBoxMode) {
            return self::API_SANDBOX_URL;
        } else {
            return self::API_PRODUCTION_URL;
        }
    }

    public function getStoreHash()
    {
        return $this->settings->get('store_hash');
    }

    public function isSandBoxMode()
    {
        return $this->settings->get('test_mode');
    }

    public function getGoogleMapsApiKey()
    {
        return $this->settings->get('maps_api_key');
    }

    public function getFFLConfiguration()
    {
        return array(
            'store_hash' => $this->getStoreHash(),
            'test_mode' => $this->isSandBoxMode(),
            'maps_api_key' => $this->getGoogleMapsApiKey(),
            'google_maps_api_url' => self::GOOGLE_MAPS_API_URL,
            'dealers_endpoint' => $this->getDealersEndpoint()
        );
    }

    public function getDealersEndpoint()
    {
        return sprintf('%s/%s/%s', $this->getBaseUrl($this->isSandBoxMode()), $this->getStoreHash(), 'dealers');
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
