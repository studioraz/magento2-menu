<?php
/**
 * Snowdog
 *
 * @author      Paweł Pisarek <pawel.pisarek@snow.dog>.
 * @category
 * @package
 * @copyright   Copyright Snowdog (http://snow.dog)
 */

namespace Snowdog\Menu\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.1.0', '<')) {
            $this->changeTitleType($setup);
        }

        if (version_compare($context->getVersion(), '0.2.0', '<')) {
            $this->addMenuCssClassField($setup);
        }

        if (version_compare($context->getVersion(), '0.2.1', '<')) {
            $this->addTargetAttribute($setup);
        }

        if (version_compare($context->getVersion(), '0.2.2', '<')) {
            $this->updateTargetAttribute($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    private function addMenuCssClassField(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('snowmenu_menu'),
            'css_class',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'after' => 'identifier',
                'default' => 'menu',
                'comment' => 'CSS Class'
            ]
        );

        return $this;
    }

    private function changeTitleType(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->modifyColumn(
            $setup->getTable('snowmenu_node'),
            'title',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false
            ],
            'Demo Title'
        );
    }

    private function addTargetAttribute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('snowmenu_node'),
            'target',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 10,
                'nullable' => true,
                'after' => 'title',
                'default' => '_self',
                'comment' => 'Link target',
            ]
        );

        return $this;
    }

    private function updateTargetAttribute(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('snowmenu_node');
        $connection = $setup->getConnection();

        $connection->update(
            $table,
            ['target' => 0],
            "target = '_self'"
        );
        $connection->update(
            $table,
            ['target' => 1],
            "target = '_blank'"
        );
        $connection->modifyColumn(
            $table,
            'target',
            [
                'type' => Table::TYPE_BOOLEAN,
                'default' => 0,
            ]
        );
    }
}
