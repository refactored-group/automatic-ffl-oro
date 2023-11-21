<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

// Adding new ffl_required boolean field to Product entity
class AddFFLRequiredBooFieldToOroProductTable implements Migration, ExtendExtensionAwareInterface
{
    const ORO_PRODUCT_TABLE_NAME = 'oro_product';

    const FFL_REQUIRED_ATTRIBUTE_NAME = 'ffl_required';
    const FFL_REQUIRED_ATTRIBUTE_LABEL = 'refactored_group.automatic_ffl.product.fields.ffl_required.label';

    private ExtendExtension $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addBoolFieldToProduct(
            $schema,
            self::FFL_REQUIRED_ATTRIBUTE_NAME,
            self::FFL_REQUIRED_ATTRIBUTE_LABEL
        );
    }

    /**
     * @param Schema $schema
     * @param string $attributeName
     * @param string $label
     * @return void
     * @throws SchemaException
     */
    protected static function addBoolFieldToProduct(Schema $schema, string $attributeName, string $label)
    {
        if ($schema->hasTable(self::ORO_PRODUCT_TABLE_NAME)) {
            $table = $schema->getTable(self::ORO_PRODUCT_TABLE_NAME);

            if (!$table->hasColumn($attributeName)) {
                $table->addColumn(
                    $attributeName,
                    'boolean',
                    [
                        'notnull' => false,
                        OroOptions::KEY => [
                            'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                            'attribute' => [
                                'is_attribute' => true
                            ],
                            'entity' => [
                                'label' => $label
                            ],
                            'frontend' => [
                                'is_displayable' => false,
                                'is_editable' => false
                            ]
                        ],
                    ],
                );
            }
        }
    }
}
