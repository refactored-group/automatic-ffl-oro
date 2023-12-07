<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Api\Client\Factory\Basic;

use GuzzleHttp\Cookie\CookieJar;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientFactoryInterface;
use RefactoredGroup\Bundle\AutomaticFFLBundle\Api\Client\Factory\ClientFactoryInterface;

class BasicClientFactory implements ClientFactoryInterface
{
    private RestClientFactoryInterface $restClientFactory;

    public function __construct(
        RestClientFactoryInterface $restClientFactory
    ) {
        $this->restClientFactory = $restClientFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function createClient($url, array $options)
    {
        return $this->restClientFactory->createRestClient($url, $options);
    }
}
