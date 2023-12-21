<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateOroIntegrationTable implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        /** Add new columns to Oro's Integration table */
        self::updateOroIntegrationTransportTable($schema);
    }

    protected function updateOroIntegrationTransportTable(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('autoffl_store_hash', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('autoffl_sandbox_mode', 'boolean', ['notnull' => false, 'default' => false]);
        $table->addColumn('autoffl_maps_api_key', 'string', ['notnull' => false, 'length' => 255]);
    }
}
