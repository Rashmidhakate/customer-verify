<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


namespace Brainvire\CategoryBannerslider\Block;

use Magento\Framework\App\State;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Info extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $_layoutFactory;
    
    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory
     */
    private $cronFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\App\DeploymentConfig\Reader
     */
    private $reader;

    /**
     * Info constructor.
     *
     * @param \Magento\Backend\Block\Context                               $context
     * @param \Magento\Backend\Model\Auth\Session                          $authSession
     * @param \Magento\Framework\View\Helper\Js                            $jsHelper
     * @param \Magento\Framework\View\LayoutFactory                        $layoutFactory
     * @param \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $cronFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList              $directoryList
     * @param \Magento\Framework\App\DeploymentConfig\Reader               $reader
     * @param \Magento\Framework\App\ResourceConnection                    $resourceConnection
     * @param \Magento\Framework\App\ProductMetadataInterface              $productMetadata
     * @param array                                                        $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $cronFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\DeploymentConfig\Reader $reader,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);

        $this->_layoutFactory = $layoutFactory;
        $this->_scopeConfig   = $context->getScopeConfig();
        $this->cronFactory = $cronFactory;
        $this->directoryList = $directoryList;
        $this->resourceConnection = $resourceConnection;
        $this->productMetadata = $productMetadata;
        $this->reader = $reader;
    }

    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);

        $html .= "<div>{{block class='Brainvire\CategoryBannerslider\Block\Bannerslider' template='Brainvire_CategoryBannerslider::bannerslider.phtml'}}</div>";

        $html .= $this->_getFooterHtml($element);

        return $html;
    }
}
