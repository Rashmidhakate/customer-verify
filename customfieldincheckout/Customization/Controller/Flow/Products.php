<?php
namespace Brainvire\Customization\Controller\Flow;
 
 
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
 
 
class Products extends Action
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
        $fpsgame = $this->getRequest()->getParam('fpsgame');
        $qty = $this->getRequest()->getParam('qty');
        $productCollection = $this->productFactory->create()->load($currentProductId);

        if($productCollection->getSpecialPrice()){
            $specialPrice = $this->getFormatedPrice($productCollection->getSpecialPrice());
            $rodPrice = $productCollection->getSpecialPrice();
        }else{
            $specialPrice = $this->getFormatedPrice($productCollection->getPrice());
            $rodPrice = $productCollection->getPrice();
        }

        if($productCollection->getImage()){
            $imagepath = $this->getImageUrl($productCollection->getImage());   
        }else{
            $imagepath = '';
        }
        
        $data = [];
        if($fpsgame){
            $fpsGameArray = explode(",",$fpsgame);
            $array = [];
           $selectedResolution = $this->coreSession->getResolution();
           $selectedQuality = $this->coreSession->getQuality();
            foreach($fpsGameArray as $fps){
                $table = $this->resourceConnection->getTableName('fps');
                $this->_connection = $this->resourceConnection->getConnection();
                $sql = 'SELECT * FROM  '. $table .' where sku = "'.$productCollection->getSku() .'" AND resolution = "'.$selectedResolution .'" AND quality = "'.$selectedQuality .'" AND game = "'.$fps.'"' ;
                $resultData = $this->_connection->fetchAll($sql);
                if($resultData){
                    foreach($resultData as $fpsdata){
                        $array[] =[
                            $fpsdata['game'],$fpsdata['fps']
                        ];
                    }
                }else{
                    $array[] =[
                            strtoupper($fps),'N/A'
                    ];
                }
                
            }
            $data = array(
                'name' => $productCollection->getName(),
                'image_url' => $imagepath,
                'mrp_price' => $productCollection->getPrice(),
                'rod_price' => $rodPrice,
                'price' => $this->getFormatedPrice($productCollection->getPrice()),
                'special_price' => $specialPrice,
                'sku' => $productCollection->getSku(),
                'description' => $productCollection->getDescription(),
                'fps' => $array
            );

        } elseif($qty) {
            if($specialPrice){
               $specialPrice = $this->getFormatedPrice($rodPrice * $qty);
               $rodPrice = $rodPrice * $qty;
            }else{
               $specialPrice = $this->getFormatedPrice($productCollection->getPrice() * $qty);
               $rodPrice = $productCollection->getPrice()  * $qty;
            }
            $data = array(
                'currentproductid' => $currentProductId,
                'name' => $productCollection->getName(),
                'qty' => $qty,
                'price' => $this->getFormatedPrice($productCollection->getPrice() * $qty),
                'special_price' => $specialPrice,
                'mrp_price' => $productCollection->getPrice()  * $qty,
                'rod_price' => $rodPrice,
                'image_url' => $imagepath,
                'description' => $productCollection->getDescription(),
                'sku' => $productCollection->getSku()
            );
        }else{
            $data = array(
                'currentproductid' => $currentProductId,
                'name' => $productCollection->getName(),
                'image_url' => $imagepath,
                'price' => $this->getFormatedPrice($productCollection->getPrice()),
                'special_price' => $specialPrice,
                'sku' => $productCollection->getSku(),
                'description' => $productCollection->getDescription(),
                'mrp_price' => $productCollection->getPrice(),
                'rod_price' => $rodPrice
            );
        }
        $result->setData(['output' => $data]);
        return $result;
    }

    public function getImageUrl($imageurl){
        $store = $this->storemanager->getStore();
        $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $imageurl;
        return $productImageUrl;
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