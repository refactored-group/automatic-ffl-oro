<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class CreateAutomaticFFLIntegrationTable implements Migration
{
    const REFACTORED_GROUP_AUTOMATIC_FFL_TABLE_NAME = 'rfg_autoffl_transport_label';

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        /** Tables generation **/
        self::createAutoFFLLabelTable($schema);

        /** Foreign keys generation **/
        self::addAutoFFLTransportLabelForeignKeys($schema);
    }

    /**
     * Create rfg_autoffl_transport_label table
     */
    protected function createAutoFFLLabelTable(Schema $schema)
    {
        if (!$schema->hasTable(self::REFACTORED_GROUP_AUTOMATIC_FFL_TABLE_NAME)) {
            $table = $schema->createTable(self::REFACTORED_GROUP_AUTOMATIC_FFL_TABLE_NAME);
            $table->addColumn('transport_id', 'integer', []);
            $table->addColumn('localized_value_id', 'integer', []);
            $table->setPrimaryKey(['transport_id', 'localized_value_id']);
            $table->addUniqueIndex(['localized_value_id'], 'rfg_autoffl_transport_label_localized_value_id', []);
            $table->addIndex(['transport_id'], 'rfg_autoffl_transport_label_transport_id', []);
        }
    }

    /**
     * Add rfg_automaticffl_transport_label foreign keys.
     */
    protected function addAutoFFLTransportLabelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(self::REFACTORED_GROUP_AUTOMATIC_FFL_TABLE_NAME);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
