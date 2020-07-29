<?php
 namespace Brainvire\Coreoverride\Plugin\Product\ProductList;
 class Toolbar
 {
 /**
  * Plugin
  *
  * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
  * @param \Closure $proceed
  * @param \Magento\Framework\Data\Collection $collection
  * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
  */
  public function aroundSetCollection(
    \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar,
    \Closure $proceed,
    $collection
  ) {
      $currentOrder = $toolbar->getCurrentOrder();
      $result = $proceed($collection);

      if ($currentOrder) {
        switch($currentOrder){
          case 'price_desc':
                $toolbar->getCollection()->setOrder('price', 'desc');
          case 'price_asc':
                $toolbar->getCollection()->setOrder('price', 'asc');
          case 'bestseller':
                $toolbar->getCollection()->getSelect()->joinLeft(
                  'sales_order_item',
                  'e.entity_id = sales_order_item.product_id',
                  array('qty_ordered'=>'SUM(sales_order_item.qty_ordered)')
                )
                ->group('e.entity_id')
                ->order('qty_ordered '.$toolbar->getCurrentDirection());
          case 'recently_added':
              $toolbar->getCollection()->setOrder('entity_id','desc');
          default:
              $toolbar->getCollection()->setOrder($currentOrder, $toolbar->getCurrentDirection());
              break;
        }
      }
      return $this;
  }
}