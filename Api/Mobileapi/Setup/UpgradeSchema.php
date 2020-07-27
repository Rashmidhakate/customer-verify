<?php

namespace Brainvire\Mobileapi\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface {

	/**
	 * {@inheritdoc}
	 */
	public function upgrade(
		SchemaSetupInterface $setup, ModuleContextInterface $context) {

		$installer = $setup;
		$installer->startSetup();
		if (version_compare($context->getVersion(), '1.0.1', '<')) {
			$table = $setup->getConnection()->newTable(
				$installer->getTable('bv_auth_mobile_token')
			)->addColumn(
				'id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id'
			)->addColumn(
				'customer_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['nullable' => false], 'Customer Id'
			)->addColumn(
				'device_token', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => 'NULL'], 'Device Token'
			)->addColumn(
				'platform', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => 'NULL'], 'Platform'
			)->addColumn(
				'login_status', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['nullable' => false, 'default' => '0'], 'Login Status'
			)->addColumn(
				'created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE], 'Created At'
			)->setComment(
				'Device Auth Token'
			);
			$installer->getConnection()->createTable($table);
		}
		if (version_compare($context->getVersion(), '1.0.2', '<')) {
			$installer->getConnection()->addColumn(
				$installer->getTable('bv_auth_mobile_token'),
				'fcm_token',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => true,
					'comment' => 'FCM Token',
				]
			);
		}
		$setup->endSetup();
	}

}
