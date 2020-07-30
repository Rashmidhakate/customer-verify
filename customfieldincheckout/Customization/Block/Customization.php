<?php
namespace Brainvire\Customization\Block;

use Magento\Framework\Stdlib\CookieManagerInterface;

class Customization extends \Magento\Framework\View\Element\Template
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        CookieManagerInterface $cookieManager,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Checkout\Model\Cart $cartModel,
        \Magento\Framework\View\Asset\Repository $moduleAssetDir,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->storemanager = $storemanager;
        $this->cookieManager = $cookieManager;
        $this->_coreSession = $coreSession;
        $this->cartModel =  $cartModel;
        $this->moduleAssetDir = $moduleAssetDir;
        $this->currencyFactory = $currencyFactory;
        $this->priceHelper =  $priceHelper;
        parent::__construct($context, $data);
    }


    public function getCacheLifetime()
    {
        return null;
    }

    public function getGames($game)
    {
        $optionId = $this->getOptionIdByLabel('game_group', $game);
        $productCollection = $this->productFactory->create()->getCollection()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('game', array('eq' => 1))
            ->addFieldToFilter('game_group', array('eq' => $optionId));
        return $productCollection;
    }

    public function getOptionIdByLabel($attributeCode, $optionLabel)
    {
        $_product = $this->productFactory->create();
        $isAttributeExist = $_product->getResource()->getAttribute($attributeCode);
        $optionId = '';
        if ($isAttributeExist && $isAttributeExist->usesSource()) {
            $optionId = $isAttributeExist->getSource()->getOptionId($optionLabel);
        }
        return $optionId;
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

    public function getImageUrl($imageurl)
    {
        $store = $this->storemanager->getStore();
        $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $imageurl;
        return $productImageUrl;
    }

    public function getSelectedGames()
    {
        //$selectedGamesIds = $this->cookieManager->getCookie('selected_game_ids');
        $this->_coreSession->start();
        $selectedGamesIds = $this->_coreSession->getGameId();
        $selectedGamesIdsArray = explode(',', $selectedGamesIds);
        return $selectedGamesIdsArray;
    }

    public function loadProductById($id)
    {
        $_product = $this->productFactory->create();
        $_product->load($id);
        return $_product;
    }

    public function getProductAsPartType($type, $specificTyepe = "", $optionalType = "")
    {
        // $optionalType = "true";
        // echo $optionalType;
        // exit;
        $selectedResolution = $this->_coreSession->getResolution();
        $selectedQuality = $this->_coreSession->getQuality();
        $qualityOptionId = $this->getOptionIdByLabel('quality', $selectedQuality);
        $resolutionOptionId = $this->getOptionIdByLabel('resolution', $selectedResolution);
        $optionId = $this->getOptionIdByLabel('part_type', $type);
        $productCollection = $this->productFactory->create()->getCollection();
        $productCollection->addAttributeToSelect('*');
        $productCollection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $productCollection->addFieldToFilter('part_type', array('eq' => $optionId));
        if ($specificTyepe) {
            $productCollection->addFieldToFilter('quality',
                array(
                    array('finset' => array($qualityOptionId)),
                )
            );
            $productCollection->addFieldToFilter('resolution',
                array(
                    array('finset' => array($resolutionOptionId)),
                )
            );
        }
        //if($optionalType){
            $productCollection->setOrder('price', 'asc');
        //}

        return $productCollection;
    }

    public function getAssemblerId(){
        $cart = $this->cartModel;
        $quote = $cart->getQuote();
        return $quote->getAssemblerId();
    }

    public function getPlacehodlerImageUrl(){
        $MageImage = $this->moduleAssetDir->getUrl("Brainvire_Customization::images/placeholder.png");
        return $MageImage;
    }

    public function getCurrencySymbol(){
        $currencyCode = $this->storemanager->getStore()->getCurrentCurrencyCode(); 
        $currency = $this->currencyFactory->create()->load($currencyCode); 
        $currencySymbol = $currency->getCurrencySymbol();
        return $currencySymbol;
    }

    public function getFormatedPrice($price){
        return $this->priceHelper->currency($price,true,false);
    }

}
