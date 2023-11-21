<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Integration;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

class AutomaticFFLChannelType implements ChannelInterface, IconAwareIntegrationInterface
{
    const TYPE = 'automatic_ffl';

    /**
     * {@inheritDoc}
     */
    public function getLabel(): string
    {
        return 'refactored_group.automatic_ffl.channel_type.label';
    }

    /**
     * {@inheritDoc}
     */
    public function getIcon(): string
    {
        return 'bundles/refactoredgroupautomaticffl/img/automatic_ffl_400x400.jpg';
    }

}
