<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Brainvire\Ordercustomization\Model\ResourceModel\Order\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Order grid collection
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
{

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $tableDescription = $this->getConnection()->describeTable($this->getMainTable());
        foreach ($tableDescription as $columnInfo) {
            $this->addFilterToMap($columnInfo['COLUMN_NAME'], 'main_table.' . $columnInfo['COLUMN_NAME']);
        }
        $this->getSelect()->joinLeft(
            ['selection' => $this->getTable('sales_order')],
            'main_table.entity_id = selection.entity_id',
            ['*']
        );
        $this->getSelect()->columns(new \Zend_Db_Expr('selection.erp_order_number as erp_order_number'));
        $this->addFilterToMap(
            'erp_order_number',
            new \Zend_Db_Expr('selection.erp_order_number')
        );
        $this->addFilterToMap(
            'user_id',
            new \Zend_Db_Expr('selection.user_id')
        );
        $this->addFilterToMap(
            'sales_person_code',
            new \Zend_Db_Expr('selection.sales_person_code')
        );
        $this->addFilterToMap(
            'customer_account_number',
            new \Zend_Db_Expr('selection.customer_account_number')
        );
        $this->addFilterToMap(
            'device_unlock',
            new \Zend_Db_Expr('selection.device_unlock')
        );
        $this->addFilterToMap(
            'acknowleadge',
            new \Zend_Db_Expr('selection.acknowleadge')
        );
        $this->getSelect()->joinLeft(
            ['customer' => $this->getTable('customer_entity')],
            'customer.entity_id = selection.user_id',
            ['firstname','lastname']
        );
        $this->getSelect()->columns(new \Zend_Db_Expr('CONCAT_WS(" ", customer.firstname, customer.lastname) as user_name'));
        $this->addFilterToMap(
            'user_name',
            new \Zend_Db_Expr('CONCAT_WS(" ", customer.firstname, customer.lastname)')
        );
        //echo $this->getSelect()->__toString();die;
        return $this;
    }
}
