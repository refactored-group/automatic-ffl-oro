<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Api\Client\Factory;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;

interface ClientFactoryInterface
{
    /**
     * @param string $url
     *
     * @return RestClientInterface
     */
    public function createClient($url, array $options);
}
