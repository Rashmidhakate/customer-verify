<?php
namespace Brainvire\Customerverify\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface {

	private $customerSetupFactory;

	/**
	 * Constructor
	 *
	 * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
	 */
	public function __construct(
		CustomerSetupFactory $customerSetupFactory
	) {
		$this->customerSetupFactory = $customerSetupFactory;
	}

	/**
	 * {@inheritdoc}
	 */
	public function install(
		ModuleDataSetupInterface $setup,
		ModuleContextInterface $context
	) {

		$customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
		$customerSetup->removeAttribute('customer', 'is_verified');

		$customerSetup->addAttribute('customer', 'is_verified', [
			'type' => 'int',
			'label' => 'Is Verified',
			'input' => 'boolean',
			'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
			'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
			'required' => false,
			'visible' => true,
			'position' => 333,
			'system' => false,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute('customer', 'is_verified')
			->addData(['used_in_forms' => [
				'adminhtml_customer',
			]]);
		$attribute->save();
	}
}
