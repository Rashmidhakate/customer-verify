<?php
namespace Brainvire\CategoryBannerslider\Model\ResourceModel\Banner;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection 
{
	protected $_idFieldName = 'banner_category_id';
    protected function _construct()
    {
        $this->_init('Brainvire\CategoryBannerslider\Model\Banner','Brainvire\CategoryBannerslider\Model\ResourceModel\Banner');
    }
 //    protected function _initSelect()
	// {
	// 	parent::_initSelect();
	// 	$this->getSelect()->joinLeft(
	// 	    ['selection' => $this->getTable('catalog_category_entity_varchar')],
	// 	    'main_table.category_id = selection.entity_id',
	// 	    ['*']
	// 	);
	// }
}