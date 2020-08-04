<?php

namespace Brainvire\Customerterm\Setup;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface {
    protected $customerSetupFactory;
    private $attributeSetFactory;

    public function __construct(
        CustomerSetupFactory $customerSetupFactory, 
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {

        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(Customer::ENTITY, 'net_terms_allowed', [
            'type' => 'text',
            'label' => 'Net Terms Allowed',
            'input' => 'select',
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'sort_order' => 101,
            'position' => 101,
            'system' => 0,
            'option' =>
            array (
                'values' =>
                array (
                    0 => 'No',
                    1 => 'Yes',
                ),
            ),
        ]);

        $customerSetup->addAttribute(Customer::ENTITY, 'customer_net_terms', [
            'type' => 'text',
            'label' => 'Customer Net Terms allowed',
            'input' => 'select',
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'sort_order' => 101,
            'position' => 101,
            'system' => 0,
            'option' =>
            array (
                'values' =>
                array (
                    0 => 'net30',
                    1 => 'net60',
                    2 => 'net90',
                ),
            ),
        ]);

        $nettermattribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'net_terms_allowed')
            ->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer','customer_account_edit'],
        ]);

        $nettermattribute->save();

        $customernettermattribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'customer_net_terms')
            ->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer','customer_account_edit'],
        ]);

        $customernettermattribute->save();

    }
}