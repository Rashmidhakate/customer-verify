<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product in category grid
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Brainvire\Coreoverride\Block\Adminhtml\Category\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ObjectManager;
use Magento\Eav\Model\Config;

class Product extends \Magento\Catalog\Block\Adminhtml\Category\Tab\Product
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $modelConfig;

    /**
     * @var Status
     */
    private $status;

    /**
     * @var Visibility
     */
    private $visibility;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     * @param Visibility|null $visibility
     * @param Status|null $status
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = [],
        Visibility $visibility = null,
        Status $status = null
    ) {
        $this->_productFactory = $productFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->visibility = $visibility ?: ObjectManager::getInstance()->get(Visibility::class);
        $this->status = $status ?: ObjectManager::getInstance()->get(Status::class);
        parent::__construct($context,$backendHelper,$productFactory,$coreRegistry,$data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('catalog_category_products');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * @return array|null
     */
    public function getCategory()
    {
        return $this->_coreRegistry->registry('category');
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'in_category') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } elseif (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        if ($this->getCategory()->getId()) {
            $this->setDefaultFilter(['in_category' => 1]);
        }

        $collection = $this->_productFactory->create()->getCollection()->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'visibility'
        )->addAttributeToSelect(
            'status'
        )->addAttributeToSelect(
            'price'
        )->joinField(
            'position',
            'catalog_category_product',
            'position',
            'product_id=entity_id',
            'category_id=' . (int)$this->getRequest()->getParam('id', 0),
            'left'
        );
        $collection->getSelect()->joinLeft(
            ['product_varchar' => $collection->getTable('catalog_product_entity_varchar')],
            'product_id = product_varchar.entity_id AND product_varchar.attribute_id = '.$this->getAttributeId('metal'),
            []
        )->columns(['metal' => 'product_varchar.value']);
        $collection->getSelect()->joinLeft(
            ['product_varchar_style' => $collection->getTable('catalog_product_entity_varchar')],
            'product_id = product_varchar_style.entity_id AND product_varchar_style.attribute_id = '.$this->getAttributeId('style'),
            []
        )->columns(['style' => 'product_varchar_style.value']);
        $collection->getSelect()->joinLeft(
            ['product_varchar_finishes' => $collection->getTable('catalog_product_entity_varchar')],
            'product_id = product_varchar_finishes.entity_id AND product_varchar_finishes.attribute_id = '.$this->getAttributeId('finishes'),
            []
        )->columns(['finishes' => 'product_varchar_finishes.value']);
        $collection->getSelect()->joinLeft(
            ['product_varchar_color' => $collection->getTable('catalog_product_entity_varchar')],
            'product_id = product_varchar_color.entity_id AND product_varchar_color.attribute_id = '.$this->getAttributeId('color'),
            []
        )->columns(['color' => 'product_varchar_color.value']);
        $collection->getSelect()->joinLeft(
            ['product_varchar_gender' => $collection->getTable('catalog_product_entity_varchar')],
            'product_id = product_varchar_gender.entity_id AND product_varchar_gender.attribute_id = '.$this->getAttributeId('gender'),
            []
        )->columns(['gender' => 'product_varchar_gender.value']);
        //echo $collection->getSelect()->__toString();
        // echo "<pre>";
        // print_r($collection->getData());
        // echo "</pre>";
       // exit;
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        if ($storeId > 0) {
            $collection->addStoreFilter($storeId);
        }
        $this->setCollection($collection);

        if ($this->getCategory()->getProductsReadonly()) {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
        }

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        
        if (!$this->getCategory()->getProductsReadonly()) {
            $this->addColumn(
                'in_category',
                [
                    'type' => 'checkbox',
                    'name' => 'in_category',
                    'values' => $this->_getSelectedProducts(),
                    'index' => 'entity_id',
                    'header_css_class' => 'col-select col-massaction',
                    'column_css_class' => 'col-select col-massaction'
                ]
            );
        }
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name']);
        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku']);
        $this->addColumn(
            'visibility',
            [
                'header' => __('Visibility'),
                'index' => 'visibility',
                'type' => 'options',
                'options' => $this->visibility->getOptionArray(),
                'header_css_class' => 'col-visibility',
                'column_css_class' => 'col-visibility'
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->status->getOptionArray()
            ]
        );

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
                'index' => 'price'
            ]
        );
        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'type' => 'number',
                'index' => 'position',
                'editable' => !$this->getCategory()->getProductsReadonly()
            ]
        );
        $this->addColumnAfter(
            'metal',
            [
                'header' => __('Metal'),
                'name' => 'metal',
                'index' => 'metal',
                'type' => 'options',
                'options' => $this->getOptionArrays('metal'),
                'filter_condition_callback' => array($this, '_filterAttributesCondition'),
                'renderer'  => 'Brainvire\Coreoverride\Block\Adminhtml\Category\Grid\Metal'
            ],
            'position'
        );
        $this->addColumnAfter(
            'color',
            [
                'header' => __('Color'),
                'name' => 'color',
                'index' => 'color',
                'type' => 'options',
                'options' => $this->getOptionArrays('color'),
                'filter_condition_callback' => array($this, '_colorFilterAttributesCondition'),
                'renderer'  => 'Brainvire\Coreoverride\Block\Adminhtml\Category\Grid\Color'
            ],
            'metal'
        );
        $this->addColumnAfter(
            'style',
            [
                'header' => __('Style'),
                'name' => 'style',
                'index' => 'style',
                'type' => 'options',
                'options' => $this->getOptionArrays('style'),
                'filter_condition_callback' => array($this, '_styleFilterAttributesCondition'),
                'renderer'  => 'Brainvire\Coreoverride\Block\Adminhtml\Category\Grid\Style'
            ],
            'color'
        );
        $this->addColumnAfter(
            'finishes',
            [
                'header' => __('Finishes'),
                'name' => 'finishes',
                'index' => 'finishes',
                'type' => 'options',
                'options' => $this->getOptionArrays('finishes'),
                'filter_condition_callback' => array($this, '_finishesFilterAttributesCondition'),
                'renderer'  => 'Brainvire\Coreoverride\Block\Adminhtml\Category\Grid\Finishes'
            ],
            'style'
        );
        $this->addColumnAfter(
            'gender',
            [
                'header' => __('Gender'),
                'name' => 'gender',
                'index' => 'gender',
                'type' => 'options',
                'options' => $this->getOptionArrays('gender'),
                'filter_condition_callback' => array($this, '_genderFilterAttributesCondition'),
                'renderer'  => 'Brainvire\Coreoverride\Block\Adminhtml\Category\Grid\Gender'
            ],
            'finishes'
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('catalog/*/grid', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected_products');
        if ($products === null) {
            $products = $this->getCategory()->getProductsPosition();
            return array_keys($products);
        }
        return $products;
    }

    protected function _filterAttributesCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addFieldToFilter('metal', array('finset' => $value));
    }

    protected function _colorFilterAttributesCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addFieldToFilter('color', array('finset' => $value));
    }

    protected function _finishesFilterAttributesCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addFieldToFilter('finishes', array('finset' => $value));
    }

    protected function _styleFilterAttributesCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addFieldToFilter('style', array('finset' => $value));
    }
    protected function _genderFilterAttributesCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addFieldToFilter('gender', array('finset' => $value));
    }

   /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArrays($attributeCode)
    {
        $eavConfig = ObjectManager::getInstance()->get(Config::class);
        $attribute = $eavConfig->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions();

        $optionsExists = array();

        foreach($options as $option) {
            $optionsExists[$option['value']] = $option['label'];
        }
        return $optionsExists;
    }

    public function getAttributeId($attributeCode){
        $entityType = 'catalog_product';
        $attribute = ObjectManager::getInstance()->get(\Magento\Eav\Model\Entity\Attribute::class)
            ->loadByCode($entityType, $attributeCode);
        return $attribute->getAttributeId();
    }

}
