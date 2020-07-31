<?php
namespace Brainvire\CategoryBannerslider\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (!$installer->tableExists('manage_category_banner')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('manage_category_banner'))
                ->addColumn(
                    'banner_category_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true]
                )
                ->addColumn('category_id', Table::TYPE_TEXT, 255, ['nullable' => false])
                ->addColumn('status', Table::TYPE_TEXT, 255, ['nullable' => false])
                ->addColumn('store',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,['nullable' => true,'default' => null],'Store')
                ->addColumn('image', Table::TYPE_TEXT, 255, ['nullable' => false])
                ->addColumn('position', Table::TYPE_INTEGER, null, ['nullable' => false])
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT]
                )
                ->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE]
                )
                ->setComment('Manage Category Banner');
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}
