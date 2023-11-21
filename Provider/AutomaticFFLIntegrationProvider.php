<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Provider;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use RefactoredGroup\Bundle\AutomaticFFLBundle\Integration\AutomaticFFLChannelType;
use RefactoredGroup\Bundle\AutomaticFFLBundle\Integration\AutomaticFFLTransport;

/**
 * RefactoredGroup AutomaticFFLIntegrationProvider class
 */
class AutomaticFFLIntegrationProvider
{
    protected DoctrineHelper $doctrineHelper;
    protected AutomaticFFLTransport $transport;

    public function __construct(
        DoctrineHelper $doctrineHelper,
        AutomaticFFLTransport $transport
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->transport = $transport;
    }

    /**
     * @return AutomaticFFLTransport
     * @throws \Exception
     */
    public function getIntegration()
    {
        $repository = $this->doctrineHelper->getEntityRepository(Channel::class);

        $channel = $repository->findOneBy([
            'type'    => AutomaticFFLChannelType::TYPE,
            'enabled' => true
        ]);

        if (!$channel) {
            throw new \Exception('There is no active Automatic FFL integration');
        }

        $this->transport->init($channel->getTransport());

        return $this->transport;
    }
}
