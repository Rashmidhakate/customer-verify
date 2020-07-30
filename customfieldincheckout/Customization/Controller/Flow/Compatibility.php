<?php
namespace Brainvire\Customization\Controller\Flow;
 
 
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
 
 
class Compatibility extends Action
{
 
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
 
    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;
 
 
    /**
     * View constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context, 
        \Magento\Catalog\Model\ProductFactory $productFactory,
        PageFactory $resultPageFactory, 
        JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    )
    {
        $this->productFactory = $productFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->storemanager =  $storemanager;
        $this->currencyFactory =  $currencyFactory;
        $this->priceHelper =  $priceHelper;
        $this->coreSession = $coreSession;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }
 
 
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        $resultPage = $this->_resultPageFactory->create();
        $currentProductId = $this->getRequest()->getParam('productid');
        $fieldId = $this->getRequest()->getParam('fieldid');
        $product = $this->loadProduct($currentProductId);
        $relatedProducts = $product->getRelatedProducts(); 
        $motherboardHtml = '';
        $coolingHtml = '';
        $i=0;
        $motherboardCku = $product->getDefaultMotherboard();
        $coolingSku = $product->getDefaultCooling();
        if($product->getName() == 'Intel® Core™ i5-9400F Processor'){
            $coolingHtml .= "<option value='' selected='selected'> N/A </option>";
        }
        if (!empty($relatedProducts)) {
            if($fieldId == 'cpu'){
                foreach ($relatedProducts as $relatedProduct) {
                    $relatedProduct = $this->loadProduct($relatedProduct->getId());
                    $optionLabel = $this->getOptionLabelById('part_type',$relatedProduct->getPartType());
                    if($optionLabel == 'Motherboard'){
                        if($motherboardCku == $relatedProduct->getSku()){
                            $motherboardHtml .= "<option value=".$relatedProduct->getId()." selected='selected'>".$relatedProduct->getName()."</option>";
                        }else{
                            $motherboardHtml .= "<option value=".$relatedProduct->getId().">".$relatedProduct->getName()."</option>";
                        }
                    }
                    if($optionLabel == 'Cooling'){
                        if($coolingSku == $relatedProduct->getSku()){
                            $coolingHtml .= "<option value=".$relatedProduct->getId()." selected='selected'>".$relatedProduct->getName()."</option>";
                        }else{
                            $coolingHtml .= "<option value=".$relatedProduct->getId().">".$relatedProduct->getName()."</option>";
                        }
                    }
                }
            }

            if($fieldId == 'case'){
                foreach ($relatedProducts as $relatedProduct) {
                    $relatedProduct = $this->loadProduct($relatedProduct->getId());
                    $optionLabel = $this->getOptionLabelById('part_type',$relatedProduct->getPartType());
                    if($optionLabel == 'Cooling'){
                        if($coolingSku == $relatedProduct->getSku()){
                            $coolingHtml .= "<option value=".$relatedProduct->getId()." selected='selected'>".$relatedProduct->getName()."</option>";
                        }else{
                            $coolingHtml .= "<option value=".$relatedProduct->getId().">".$relatedProduct->getName()."</option>";
                        }
                    }
                }
            }
        }  
        $result->setData(
            [
                'motherboardHtml' => $motherboardHtml,
                'coolingHtml' => $coolingHtml
            ]
        );
        return $result;
    }

    public function loadProduct($id){
        return $this->productFactory->create()->load($id);
    }
    
    public function getOptionLabelById($attributeCode, $optionId)
    {
        $_product = $this->productFactory->create();
        $isAttributeExist = $_product->getResource()->getAttribute($attributeCode);
        if ($isAttributeExist && $isAttributeExist->usesSource()) {
            $optionLabel = $isAttributeExist->getSource()->getOptionText($optionId);
        }
        return $optionLabel;
    }
}