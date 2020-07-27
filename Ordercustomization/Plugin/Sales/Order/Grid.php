<?php
namespace Brainvire\Ordercustomization\Plugin\Sales\Order;
class Grid
{
    public static $table = 'sales_order_grid';
    public static $leftJoinTable = 'sales_order';
    public static $customerEntityTable = 'customer_entity';

    public function afterSearch($intercepter, $collection)
    {
        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table)) {

            $leftJoinTableName = $collection->getConnection()->getTableName(self::$leftJoinTable);

            $customerEntityTableName = $collection->getConnection()->getTableName(self::$customerEntityTable);

            $collection
            ->getSelect()
            ->join(
                ['co'=>$leftJoinTableName],
                "co.entity_id = main_table.entity_id",
                [
                    'erp_order_number' => 'co.erp_order_number',
                    'sales_person_code' => 'co.sales_person_code',
                    'exported' => 'co.exported',
                    'acknowleadge' => 'co.acknowleadge',
                    'customer_account_number' => 'co.customer_account_number',
                    'device_unlock' => 'co.device_unlock',
                ]
            );

            $collection
            ->getSelect()
            ->joinLeft(
                ['customer'=>$customerEntityTableName],
                "customer.entity_id = co.user_id",
                [
                    'firstname'=>'CONCAT(customer.firstname," ",customer.lastname) AS Fullname'
                    //'user_name' => 'customer.firstname'
                ]
            );
            $collection->addFilterToMap('user_name', 'Fullname');
            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);
            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where);
            //echo $collection->getSelect()->__toString();die;
        }
        return $collection;
    }
}