<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Brainvire\CategoryBannerslider\Ui\Component\Listing\Banner;

class CategoryOptions implements \Magento\Framework\Option\ArrayInterface
{

    protected $_categories;

    public function __construct(\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collection)
    {
        $this->_categories = $collection;
    }

    public function toOptionArray()
    {

        $collection = $this->_categories->create();
        $collection->addAttributeToSelect('*')->addFieldToFilter('is_active', 1);
        $itemArray = array('value' => '', 'label' => '--Please Select--');
        $options = [];
        $options[] = $itemArray;
        foreach ($collection as $category) {
            $options[] = array('value' => $category->getId(), 'label' => $category->getName());
        }
        return $options;
    }

}