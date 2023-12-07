<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Api\Client;

use RefactoredGroup\Bundle\AutomaticFFLBundle\Api\Client\Factory\ClientFactoryInterface;
use RefactoredGroup\Bundle\AutomaticFFLBundle\Integration\AutomaticFFLTransport;
use RefactoredGroup\Bundle\AutomaticFFLBundle\Provider\AutomaticFFLIntegrationProvider;

class BaseApiClient
{
    protected ClientFactoryInterface $clientFactory;
    protected AutomaticFFLIntegrationProvider $integrationProvider;
    protected AutomaticFFLTransport $transport;

    public function __construct(
        ClientFactoryInterface $clientFactory,
        AutomaticFFLIntegrationProvider $integrationProvider
    ) {
        $this->clientFactory = $clientFactory;
        $this->integrationProvider = $integrationProvider;
        $this->transport = $this->integrationProvider->getIntegration();
    }

    public function getBaseApiUrl()
    {
        return $this->transport->getBaseUrl($this->isSandBoxMode());
    }

    public function getStoreHash()
    {
        return $this->transport->getStoreHash();
    }

    public function getGoogleMapsApiKey()
    {
        return $this->transport->getGoogleMapsApiKey();
    }

    public function isSandBoxMode()
    {
        return $this->transport->isSandBoxMode();
    }

    public function createApiClient($options)
    {
        return $this->clientFactory->createClient($this->getBaseApiUrl(), $options);
    }
}
