<?php
namespace Brainvire\Customization\Controller\Flow;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;

class Addtocart extends Action
{

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    protected  $_modelCart;
    protected $_checkoutSession;

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
        \Magento\Checkout\Model\Cart $cartModel,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        CheckoutSession $checkoutSession,
        Cart $modelCart
    ) {
        $this->productFactory = $productFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->storemanager = $storemanager;
        $this->currencyFactory = $currencyFactory;
        $this->priceHelper = $priceHelper;
        $this->cartModel = $cartModel;
        $this->formKey = $formKey;
        $this->_coreSession = $coreSession;
        $this->_modelCart = $modelCart;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        $resultPage = $this->_resultPageFactory->create();
        $cart = $this->_modelCart;
        $quoteItems = $this->_checkoutSession->getQuote()->getItemsCollection();
        foreach($quoteItems as $item)
        {
            $cart->removeItem($item->getId()); 
        }
       // $cart->save();
        $selectedproductid = $this->getRequest()->getParam('selectedproductid');
        $qtyData = $this->getRequest()->getParam('qty');
        $array = array_filter($selectedproductid);
        $cart = $this->cartModel;
        foreach ($array as $productId) {
            $product = $this->productFactory->create()->load($productId);
            $formKey = $this->formKey->getFormKey();
            $params = array(
                'form_key' => $formKey,
                'product' => $productId, //product Id
                'qty' => 1, //quantity of product
            );
            if($qtyData){
                foreach ($qtyData as $qty) {
                    if ($this->getOptionLabelById('part_type', $product->getPartType()) == $qty['label']) {
                        $params = array(
                            'form_key' => $formKey,
                            'product' => $qty['productId'], //product Id
                            'qty' => $qty['qty'], //quantity of product
                        );
                    }
                }   
            }
            $cart->addProduct($product, $params);
        }
        $cart->save();
        // Add rod price and mrp price save in session
        $rodrrqPrice = $this->getRequest()->getParam('rodpriceValue');
        $mrpreqPrice = $this->getRequest()->getParam('mrppriceValue');
        $this->_coreSession->start();
        $this->_coreSession->setrodPrice($rodrrqPrice);
        $this->_coreSession->setmrpPrice($mrpreqPrice);

        $data = array(
            'message' => "successfully added to cart.",
        );
        $result->setData(['output' => $data]);
        return $result;
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
