<?php

namespace Brainvire\Coreoverride\Block\Adminhtml\Category\Grid;

use Magento\Framework\DataObject;

class Gender extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productModel;
    /**
     * @param \Magento\Catalog\Model\Product $categoryFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->productFactory = $productFactory;
    }

    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $productId = $row->getEntityId();
        $product = $this->productFactory->create();
        $product->load($productId);
        $attribute_string = $product->getResource()->getAttribute('gender')->getFrontend()->getValue($product);
        return $attribute_string;
    }
}