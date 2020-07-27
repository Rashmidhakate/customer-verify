<?php

namespace Brainvire\Mobileapi\Model\Cart;

use Brainvire\Mobileapi\Api\Cart\CartInterface;
//use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Brainvire\Custom\Model\AppQuoteFactory;
use Brainvire\Custom\Model\AppQuoteItemFactory;
/**
 * Defines the implementaiton class of the \Brainvire\Mobileapi\Api\Cart\CartInterface
 */
class CartManagement extends \Magento\Framework\Model\AbstractModel implements CartInterface {

    const PAGE_LIMIT = '20';
    const MAX_CHAR = '150';

    protected $cart;
    protected $product;
    protected $customerCheck;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Brainvire\Mobileapi\Helper\Data $generalHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        Customer $customerCheck,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\Product $product,
        \Magento\Checkout\Model\Cart $cartFactory,
        \Magento\Framework\Data\Form\FormKey $formkey,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Framework\Stdlib\DateTime\DateTime $dataTime,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\SessionManagerInterface $session,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollection,
        \Magento\Quote\Model\GuestCart\GuestCartRepository $guestCart,
        \Magento\Quote\Api\GuestCartManagementInterface $guestCartManagementInterface,
        \Magento\Quote\Api\GuestCartRepositoryInterface $guestCartRepositoryInterface,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Framework\App\RequestInterface $request,
        AppQuoteFactory $appQuoteFactory,
        AppQuoteItemFactory $appQuoteItemFactory,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->quoteFactory = $quoteFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->generalHelper = $generalHelper;
        $this->productFactory = $productFactory;
        $this->customerCheck = $customerCheck;
        $this->formKey = $formKey;
        $this->cart = $cart;
        $this->product = $product;
        $this->quoteRepository = $quoteRepository;
        $this->dateTime = $dataTime;
        $this->_storeManager = $storeManager;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->pricingHelper = $pricingHelper;
        $this->customerRepository = $customerRepository;
        $this->_urlInterface = $urlInterface;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->couponFactory = $couponFactory;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->timezone = $timezone;
        $this->order = $order;
        $this->quoteItemCollection = $quoteItemCollection;
        $this->guestCart = $guestCart;
        $this->guestCartManagementInterface = $guestCartManagementInterface;
        $this->guestCartRepositoryInterface = $guestCartRepositoryInterface;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->request = $request;
        $this->appQuoteFactory = $appQuoteFactory;
        $this->appQuoteItemFactory = $appQuoteItemFactory;
        $this->quoteManagement = $quoteManagement;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->_orderItemFactory = $orderItemFactory;
        $this->_orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->_quoteItemFactory = $quoteItemFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_optionFactory = $optionFactory;
        $this->orderFactory = $orderFactory;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @param string $Version
     * @param string $Platform
     * @param string $DeviceId
     * @param mixed $Data
     * @return \Brainvire\Mobileapi\Api\Cart\CartInterface[]
     */
    public function createAppQuote($Version = '', $Platform= '', $DeviceId= '', $Data) {
        $succ = $error = $QuoteIds = [];
        $update = 0;
        $platform = strtolower($Platform);
        $customer = $this->generalHelper->customerLoginCheck();
        $UserId = $customer['id'];

        $this->generalHelper->checkQuoteDataValidation($Data);       
        
        if (count($Data) > 0 ) {
            $j = 0;
            foreach ($Data as $key => $data) {
                $appQuote = $this->appQuoteFactory->create(); 
                // Save App Quote 
                if (isset($data['QuoteId']) && $data['QuoteId'] != '') {
                    $appQuote->load($data['QuoteId']);
                    $update = 1;
                }

                $customerData = $this->customerFactory->create()->load($data['CustomerId']);

                $appQuote->setLocalOrderId($data['LocalOrderId']);
                $appQuote->setUserId($UserId);
                $appQuote->setAppStatus($data['Status']);
                $appQuote->setSalesPersonCode($data['SalesPersonCode']);
                $appQuote->setCustomerId($data['CustomerId']);
                $appQuote->setStoreName($data['StoreName']);
                $appQuote->setContainerType($data['ContainerType']);
                $appQuote->setContainerSize($data['ContainerSize']);
                $appQuote->setLineNumber($data['LineNumbers']);
                $appQuote->setOrderWeight($data['OrderWeight']);
                $appQuote->setOrderVolume($data['OrderVolume']);
                $appQuote->setOrderTotal($data['OrderTotal']);
                $appQuote->setNotes(substr($data['Notes'],0,self::MAX_CHAR));                
                $appQuote->setPlateform($Platform);
                $appQuote->setVersion($Version);
                $appQuote->setDeviceId($DeviceId);
                if (isset($data['CreatedAt'])) {
                    $appQuote->setCreatedAt($data['CreatedAt']);    
                }
                if (isset($data['UpdatedAt'])) {
                    $appQuote->setUpdatedAt($data['UpdatedAt']);
                }

                if ($customerData->getData('erp_customer_number')) {
                    $appQuote->setCustomerAccountNumber($customerData->getData('erp_customer_number'));
                }
                if ($customerData->getData('customer_source_code')) {
                    $appQuote->setCustomerSourceCode($customerData->getData('customer_source_code'));
                }

                $appQuote->save();

                if ($appQuote->getId()) {
                    $resultData[$j]['LocalOrderId'] = $data['LocalOrderId'];
                    $resultData[$j]['QuoteId'] = $appQuote->getId();
                    $QuoteItems = [];
                    // Save App Quote Item 
                    
                    if(!empty($data['Products'])) {
                        $i = 0;
                        // delete all items before save
                        // $appQuoteItems = $this->appQuoteItemFactory->create()->getCollection()->addFieldToFilter('app_quote_id', array('eq' => $data['QuoteId']));
                        // foreach ($appQuoteItems as $item) {
                        //     $appQuoteItem = $this->appQuoteItemFactory->create()->load($item->getAppQuoteItemId());
                        //     if($appQuoteItem->getAppQuoteItemId()){
                        //         $appQuoteItem->delete();
                        //     }
                        // }
                        /*
                        $appQuoteItem = $this->appQuoteItemFactory->create()->getCollection()->addFieldToFilter('app_quote_id', array('eq' => $data['QuoteId']));
                        if ($appQuoteItem->getSize()) {
                            $appQuoteItem->walk('delete');
                        }
                        */
                        foreach ($data['Products'] as $items) {                    
                            $appQuoteItem = $this->appQuoteItemFactory->create();
                            if (!empty($items['QuoteItemId'])) {
                                $appQuoteItem->load($items['QuoteItemId']);
                            }
                            $appQuoteItem->setAppQuoteId($appQuote->getId());
                            $appQuoteItem->setProductId($items['ProductId']);
                            $appQuoteItem->setLocalQuoteItemId($items['LocalQuoteItemId']);
                            $appQuoteItem->setPriceCode($items['PriceCode']);
                            $appQuoteItem->setItemName($items['ProductName']);
                            $appQuoteItem->setUnitePrice($items['UnitPrice']);
                            $appQuoteItem->setCpq($items['CPQ']);
                            $appQuoteItem->setIpq($items['IPQ']);
                            $appQuoteItem->setSellable($items['Sellable']);
                            $appQuoteItem->setTotalQuantity($items['TotalQuantity']);                    
                            $appQuoteItem->setUnitOfMeasure($items['UnitOfMeasure']);
                            $appQuoteItem->setLineItemType($items['LineItemType']);
                            $appQuoteItem->setSplit($items['Split']);
                            $appQuoteItem->setLineNumber($items['LineNumber']);
                            $appQuoteItem->setLineNotes(($items['LineNotes']) ? substr($items['LineNotes'],0,self::MAX_CHAR) : '');
                            $appQuoteItem->setTotalPrice($items['TotalPrice']);
                            $appQuoteItem->save();
                            $appQuoteItem->load($appQuoteItem->getId());
                            $QuoteItems[$i]['LocalQuoteItemId'] = $items['LocalQuoteItemId'];
                            $QuoteItems[$i]['QuoteItemId'] = $appQuoteItem->getId();
                            $i++;
                        }
                        $resultData[$j]['QuoteItemId'] = $QuoteItems;
                    }
                    $QuoteIds[] = '#'.$appQuote->getId();
                    $succ[] = 1;

                } else {
                    $resultData[$j]['LocalOrderId'];
                    // $result[] = array(
                    //     "Settings" => ["Code" => "401","Message" => "Error"],
                    //     "Data" => $resultData
                    // );
                    $error[] = 1;
                }
                $j++;
            }   
            if (count($succ) > 0) {

                $message = "Quotes Successfully ";
                 $message .= ($update) ? "Updated" : "Saved" . ", Quote Reference number " . implode(', ', $QuoteIds);
                $result = array(
                    "Settings" => ["Code" => "200","Message" => $message ],
                    "Data" => $resultData
                );   
            }
            echo json_encode($result); exit; 
        }            
    }

    /**
     * @param string $Version
     * @param string $Platform
     * @param string $DeviceId
     * @param mixed $Data
     * @return \Brainvire\Mobileapi\Api\Cart\CartInterface[]
     */

    public function createAppOrder($Version = '', $Platform= '', $DeviceId= '', $Data) {
        $succ = $error = $resultData = $OrderIds = [];
        $platform = strtolower($Platform);
        // $headers = array(
        //     'Authorization:  '. $this->request->getHeader('Authorization'),
        // );
        // $ch = curl_init();
        // $host = $this->_storeManager->getStore()->getBaseUrl();
        // curl_setopt($ch, CURLOPT_URL, $host.'rest/V1/customers/me');
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // $output = curl_exec($ch);
        // $customer = json_decode($output,1);
        // if (array_key_exists('message',$customer)){
        //     $message = "Customer is not logged in.";
        //     $error = (object) array();
        //     header('x', true, 401);

        //     $result = array(
        //         "Settings" => ["Code" => "400","Message" => strval($message)]
        //     );  
        //     echo json_encode($result); exit;
        // }

        // curl_close($ch);
        
        // $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/quote-item-id.log');
        // $logger = new \Zend\Log\Logger();

        $websiteId = 1;
        if (count($Data) > 0 ) {
            $j = 0;
            $host = $this->_storeManager->getStore()->getBaseUrl();
            $userData = array("username" => "api_auth", "password" => "4sgm!2020");
            $ch = curl_init($host . "rest/V1/integration/admin/token");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Lenght: " . strlen(json_encode($userData))));

            $token = curl_exec($ch);
            foreach ($Data as $key => $data) {               
                $appQuoteData = $this->appQuoteFactory->create()->load($data['QuoteId']);
                
                if (!$appQuoteData->getData()) {
                    $error[] = 2;
                    continue;
                }            

                $appQuoteItemData = $this->appQuoteItemFactory->create()->getCollection()->addFieldToFilter('app_quote_id', array('eq' => $appQuoteData->getAppQuoteId()));

                $UserId = $appQuoteData->getData('user_id');
                $data['LocalOrderId'] = $appQuoteData->getData('local_order_id');
                $data['QuoteId'] = $appQuoteData->getData('app_quote_id');
                $data['Status'] = $appQuoteData->getData('app_status');
                $data['SalesPersonCode'] = $appQuoteData->getData('sales_person_code');
                $data['CustomerId'] = $appQuoteData->getData('customer_id');
                $data['StoreName'] = $appQuoteData->getData('store_name');
                $data['ContainerType'] = $appQuoteData->getData('container_type');
                $data['ContainerSize'] = $appQuoteData->getData('container_size');
                $data['LineNumbers'] = $appQuoteData->getData('line_number');
                $data['OrderWeight'] = $appQuoteData->getData('order_weight');
                $data['OrderVolume'] = $appQuoteData->getData('order_volume');
                $data['OrderTotal'] = $appQuoteData->getData('order_total');
                $data['Notes'] = $appQuoteData->getData('notes');

                $data['Products'] = [];

                $customerLoad = $this->customerCheck->setWebsiteId($websiteId)->load($appQuoteData->getData('customer_id'));

                if (count($appQuoteItemData->getData())) {
                    $i =0;
                    foreach ($appQuoteItemData->getData() as $key => $appQuoteItem) {
                     $data['Products'][$i]['QuoteItemId'] = $appQuoteItem['app_quote_id'];
                     $data['Products'][$i]['LocalQuoteItemId'] = $appQuoteItem['local_quote_item_id'];
                     $data['Products'][$i]['ProductId'] = $appQuoteItem['product_id'];
                     $data['Products'][$i]['PriceCode'] = $appQuoteItem['price_code'];
                     $data['Products'][$i]['ProductName'] =  $appQuoteItem['item_name'];
                     $data['Products'][$i]['UnitPrice'] =  $appQuoteItem['unite_price'];
                     $data['Products'][$i]['CPQ'] = $appQuoteItem['cpq'];
                     $data['Products'][$i]['IPQ'] = $appQuoteItem['ipq'];
                     $data['Products'][$i]['Sellable'] = $appQuoteItem['sellable'];
                     $data['Products'][$i]['TotalQuantity'] = $appQuoteItem['total_quantity'];
                     $data['Products'][$i]['TotalPrice'] = $appQuoteItem['total_price'];
                     $data['Products'][$i]['UnitOfMeasure'] = $appQuoteItem['unit_of_measure'];
                     $data['Products'][$i]['LineItemType'] = $appQuoteItem['line_item_type'];
                     $data['Products'][$i]['Split'] = $appQuoteItem['split'];
                     $data['Products'][$i]['LineNumber'] = $appQuoteItem['line_number'];
                     $data['Products'][$i]['LineNotes'] = $appQuoteItem['line_notes'];
                     $i++;
                 }
             }

            if ($appQuoteData->getOrderId()) {
                $orderDetail = $this->orderFactory->create()->load($appQuoteData->getOrderId());
                $OrderIds[] = '#'.$orderDetail->getIncrementId();
                $succ[] = 1;
                $message = "Order has created successfully";
                $resultData[$j] = [
                    'LocalOrderId' => $data['LocalOrderId'],
                    'QuoteId' => $data['QuoteId'],
                    'MagentoOrderId' => $orderDetail->getIncrementId()
                ];
            } else {
                $customerData = [
                    'customer_id' => $data['CustomerId']
                ];

                $ch = curl_init( $host . "rest/V1/carts/mine");
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($customerData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));

                $result = curl_exec($ch);
                $quote_id = json_decode($result, 1);
                $itemCustomPricesAndQty = [];
                if($data['Products']) {
                    foreach ($data['Products'] as $key => $productItem) {
                    $optionId = '';
                    $customOptions = $customOptionData = [];
                    $productId = $productItem['ProductId'];
                    $loadProductData = $this->productFactory->create()->load($productId);

                    $customOptions = $this->_optionFactory->create()->getProductOptionCollection($loadProductData);
                    foreach ($customOptions as $customOption) {
                        $values = $customOption->getData();
                        if($values['title'] == 'Split') {
                            $optionId = $values['option_id'];
                        }
                    }

                    // Same SKU item added to cart solution
                    $customOptionData[0]['option_id'] = $optionId;
                    $customOptionData[0]['option_value'] = $productItem['Split'];

                    if ($productItem['Split'] == 'L2') {
                        $customOptionData[1]['option_id'] = $optionId;
                        $customOptionData[1]['option_value'] = $productItem['Split'];
                    }

                    $productData = [
                        'cart_item' => [
                            'quote_id' => $quote_id,
                            'sku' => $loadProductData->getSku(),
                            'qty' => $productItem['TotalQuantity'],
                            "product_option" => [
                                "extension_attributes" => [
                                    "custom_options" => $customOptionData 
                                ]
                            ]
                        ]
                    ];
                    //echo '<pre>'; print_r($productData); exit;
                    $ch = curl_init( $host . "rest/V1/carts/mine/items");
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($productData));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));

                    $result = curl_exec($ch);
                    $result = json_decode($result, 1);

                    $quoteItem = $this->_quoteItemFactory->create()->load($result['item_id']);

                    $quoteItem->setQty($productItem['TotalQuantity'])
                    ->setIsVirtual(0)
                    ->setPrice($productItem['UnitPrice'])
                    ->setBasePrice($productItem['UnitPrice'])
                    ->setOriginalPrice($productItem['UnitPrice'])
                    ->setBaseOriginalPrice($productItem['UnitPrice'])
                    ->setBaseRowTotal($productItem['TotalPrice'])
                    ->setPriceInclTax($productItem['TotalPrice'])
                    ->setBasePriceInclTax($productItem['TotalPrice'])
                    ->setTaxAmount($productItem['TotalPrice'])
                    ->setBaseTaxAmount($productItem['TotalPrice'])
                    ->setRowTotal($productItem['TotalPrice'])
                    ->setBaseRowTotal($productItem['TotalPrice'])
                    ->setRowTotalInclTax($productItem['TotalPrice'])
                    ->setBaseRowTotalInclTax($productItem['TotalPrice'])
                    ->setCp($productItem['CPQ'])
                    ->setIp($productItem['IPQ'])
                    ->setLocalQuoteItemId($productItem['LocalQuoteItemId'])
                    ->setPriceCode($productItem['PriceCode'])
                    ->setSellable($productItem['Sellable'])
                    ->setSplit($productItem['Split'])
                    ->setLineItemType($productItem['LineItemType'])
                    ->setUniteOfMeasure($productItem['UnitOfMeasure'])
                    ->setLineItemNumber($productItem['LineNumber'])
                    ->setLineNotes($productItem['LineNotes'])
                    ->setTaxAmount(0)
                    ->setBaseTaxAmount(0)
                    ->setTaxPercent(0)
                    ->setDiscountAmount(0)
                    ->setBaseDiscountAmount(0)
                    ->setDiscountPercent(0);

                    $quoteItem->save();

                    $itemCustomPricesAndQty[$result['item_id']] = [
                        'TotalQuantity' => $productItem['TotalQuantity'],
                        'UnitPrice' => $productItem['UnitPrice'],
                        'TotalPrice' => $productItem['TotalPrice'],
                        'TotalQuantity' => $productItem['TotalQuantity']
                    ];
                    }
                }
                $ch = curl_init( $host . "rest/V1/carts/".$quote_id);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));

                $result = curl_exec($ch);
                $quoteData = json_decode($result, 1);
                $quote = $this->quoteRepository->get($quote_id);
                //echo '<pre>';
                $customerDefaultBillingAddress = []; //$quoteData['customer']['default_billing'];
                $customerDefaultShippingAddress = []; //$quoteData['customer']['default_shipping'];

                if(!empty($quoteData['customer']['addresses'])) {
                    foreach ($quoteData['customer']['addresses'] as $key => $addresses) {
                        if (isset($addresses['default_billing']) == 1 && isset($addresses['default_shipping']) == 1) {
                            $customerDefaultShippingAddress = $addresses;
                            $customerDefaultBillingAddress = $addresses;                                    
                        } else if (isset($addresses['default_billing']) == 1) {
                            $customerDefaultBillingAddress = $addresses;
                        } else if (isset($addresses['default_shipping']) == 1) {
                            $customerDefaultShippingAddress = $addresses;
                        }                         
                    }
                }
                $quote->getBillingAddress()->addData($customerDefaultBillingAddress);
                $quote->getShippingAddress()->addData($customerDefaultShippingAddress);

                $shippingAddress=$quote->getShippingAddress();    
                $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod('freeshipping_freeshipping'); 
                $quote->setPaymentMethod('checkmo'); 
                $quote->setInventoryProcessed(false);

                // Set Sales Order Payment
                $quote->getPayment()->importData(['method' => 'checkmo']);

                $quote->setIsVirtual(0);
                $quote->setSubTotal($data['OrderTotal']);
                $quote->setBaseSubTotal($data['OrderTotal']);
                $quote->setBaseGrandTotal($data['OrderTotal']);
                $quote->setGrandTotal($data['OrderTotal']);
                $quote->setAppQuoteId($data['QuoteId']);
                $quote->setLocalAppOrderId($data['LocalOrderId']);
                $quote->setContainerType($data['ContainerType']);
                $quote->setContainerSize($data['ContainerSize']);
                $quote->setDeviceId($DeviceId);
                $quote->setPlateform($Platform);
                $quote->setVersion($Version);
                $quote->setStoreName($data['StoreName']);
                $quote->setSalesPersonCode($data['SalesPersonCode']);
                $quote->setTotalCube($data['OrderVolume']);
                $quote->setTotalWeight($data['OrderWeight']);
                $quote->setCustomerNote($data['Notes']);
                $quote->setLineNumber($data['LineNumbers']);
                $quote->setUserId($UserId);
                $quote->setDeviceUnlock('No');
                $quote->setExported('New');
                if ($appQuoteData->getData('customer_account_number')) {
                    $quote->setCustomerAccountNumber($appQuoteData->getData('customer_account_number'));
                }
                if ($appQuoteData->getData('customer_source_code')) {
                    $quote->setCustomerSourceCode($appQuoteData->getData('customer_source_code'));
                }
                if (isset($data['OrderDate'])) {
                    $quote->setCreatedAt($data['OrderDate']);
                    $quote->setUpdatedAt($data['OrderDate']);    
                }
                $quote->save();

                $order = $this->quoteManagement->submit($quote);

                $order->setEmailSent(0);
                $order->setDiscountAmount(0);
                $order->setBaseDiscountAmount(0);
                $order->setTaxAmount(0);
                $order->setBaseTaxAmount(0);
                $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                $order->setSubTotal($data['OrderTotal']);
                $order->setBaseSubTotal($data['OrderTotal']);
                $order->setGrandTotal($data['OrderTotal']);
                $order->setBaseGrandTotal($data['OrderTotal']);
                $order->setAppQuoteId($data['QuoteId']);
                $order->setLocalAppOrderId($data['LocalOrderId']);
                $order->setContainerType($data['ContainerType']);
                $order->setContainerSize($data['ContainerSize']);
                $order->setDeviceId($DeviceId);
                $order->setPlateform($Platform);
                $order->setVersion($Version);
                $order->setUserId($UserId);
                $order->setCustomerNote($data['Notes']);
                $order->setLineNumber($data['LineNumbers']);                
                $order->setDeviceUnlock('No');
                $order->setExported('New');
                $order->setStoreName($data['StoreName']);
                $order->setSalesPersonCode($data['SalesPersonCode']);
                $order->setTotalWeight($data['OrderWeight']);
                $order->setTotalCube($data['OrderVolume']);
                if ($appQuoteData->getData('customer_account_number')) {
                    $order->setCustomerAccountNumber($appQuoteData->getData('customer_account_number'));
                }
                if ($appQuoteData->getData('customer_source_code')) {
                    $order->setCustomerSourceCode($appQuoteData->getData('customer_source_code'));
                }
                if (isset($data['OrderDate'])) {
                    $order->setCreatedAt($data['OrderDate']);
                    $order->setUpdatedAt($data['OrderDate']);    
                }
                $order->save();

                $appQuote = $this->appQuoteFactory->create(); 
                // Save App Quote 
                if ($order->getAppQuoteId()) {
                    $appQuote->load($order->getAppQuoteId());
                    $appQuote->setOrderId($order->getId());
                    $appQuote->save();
                }

                $orderItems = $this->_orderItemCollectionFactory->create()->addFieldToFilter('order_id', $order->getId());

                if ($orderItems->getData()) {
                    foreach ($orderItems->getData() as $key => $orderItem) {
                       $qItems = $this->_quoteItemFactory->create()->load($orderItem['quote_item_id'], 'item_id');
                       $orderItemModel = $this->_orderItemFactory->create()->load($orderItem['item_id']);
                       $orderItemModel->setPriceCode($qItems->getPriceCode());
                       $orderItemModel->setCp($qItems->getCp());
                       $orderItemModel->setIp($qItems->getIp());
                       $orderItemModel->setLineItemNumber($qItems->getLineItemNumber());
                       $orderItemModel->setLineNotes($qItems->getLineNotes());
                       $orderItemModel->setLocalQuoteItemId($qItems->getLocalQuoteItemId())
                        ->setSellable($qItems->getSellable())
                        ->setSplit($qItems->getSplit())
                        ->setLineItemType($qItems->getLineItemType())
                        ->setUniteOfMeasure($qItems->getUniteOfMeasure())
                        ->setTaxAmount(0)
                        ->setBaseTaxAmount(0)
                        ->setTaxPercent(0)
                        ->setDiscountAmount(0)
                        ->setBaseDiscountAmount(0)
                        ->setDiscountPercent(0)
                        ->setQtyOrdered($itemCustomPricesAndQty[$orderItem['quote_item_id']]['TotalQuantity'])
                        ->setPrice($itemCustomPricesAndQty[$orderItem['quote_item_id']]['UnitPrice'])
                        ->setBasePrice($itemCustomPricesAndQty[$orderItem['quote_item_id']]['UnitPrice'])
                        ->setOriginalPrice($itemCustomPricesAndQty[$orderItem['quote_item_id']]['UnitPrice'])
                        ->setBaseOriginalPrice($itemCustomPricesAndQty[$orderItem['quote_item_id']]['UnitPrice'])
                        ->setPriceInclTax($qItems->getPriceInclTax())
                        ->setBasePriceInclTax($qItems->getBasePriceInclTax())
                        ->setRowTotal($itemCustomPricesAndQty[$orderItem['quote_item_id']]['TotalPrice'])
                        ->setBaseRowTotal($itemCustomPricesAndQty[$orderItem['quote_item_id']]['TotalPrice'])
                        ->setRowTotalInclTax($itemCustomPricesAndQty[$orderItem['quote_item_id']]['TotalPrice'])
                        ->setBaseRowTotalInclTax($itemCustomPricesAndQty[$orderItem['quote_item_id']]['TotalPrice']);

                        $options = $orderItemModel->getProductOptions();                            
                        $additionalOptions = array();
                        $additionalOptions = $this->generalHelper->addProductOptions($qItems); 
                        $options['additional_options'] = $additionalOptions;
                        $orderItemModel->setProductOptions($options);
                                 
                       $orderItemModel->save();
                    }
                }

                if($order->getId()){
                    $OrderIds[] = '#'.$order->getIncrementId();
                    $succ[] = 1;
                    $message = "Order has created successfull";
                    $resultData[$j] = [
                        'LocalOrderId' => $data['LocalOrderId'],
                        'QuoteId' => $data['QuoteId'],
                        'MagentoOrderId' =>$order->getIncrementId()
                    ];                            
                }else{
                    $error[] = 1;
                }
            }    
            $j++;
        }

        if (count($succ) > 0) {
            $message = "Order has created successfully, Order Reference number " . implode(', ', $OrderIds);
            $result = array(
                "Settings" => ["Code" => "200","Message" => $message],
                "Data" => $resultData
            );   
        }
        if (in_array("1", $error)) {
            $message = "Something went wrong";
            $result = array(
                "Settings" => ["Code" => "400","Message" => $message]
            );   
        }
        if (in_array("2", $error)) {
            $message = "Quote Data doesn't match";
            $result = array(
                "Settings" => ["Code" => "400","Message" => $message]
            );   
        }
        echo json_encode($result); exit; 
        }            
    }
    

    /**
     * @param string $Version
     * @param string $Platform
     * @param string $DeviceId
     * @param string $DateTimeStamp
     * @return \Brainvire\Mobileapi\Api\Cart\CartInterface[]
     */
    public function listAppQuote($Version = '', $Platform= '', $DeviceId= '', $DateTimeStamp= '') {
        //echo date('Y-m-d H:i:s', $DateTimeStamp); exit;
        $resultData = $Pagination = [];
        $params = $this->request->getParams();

        $platform = strtolower($Platform);

        $customer = $this->generalHelper->customerLoginCheck();
        $UserId = $customer['id'];

        $salesPersonCode = $this->generalHelper->getSalesPersonCodeByUserId($UserId);
        $collection = $this->appQuoteFactory->create()->getCollection()->addFieldToFilter(
            ['user_id', 'sales_person_code'], 
            [
                ['eq' => $UserId],
                ['in' => $salesPersonCode]
            ]        
        )->addFieldToFilter(
            ['app_status'], 
            [
                ['nin' => ['Submitted'] ]
            ]   
        )->addFieldToFilter(
            'order_id',
            ['null' => true]
        );
        
        if($DateTimeStamp){
            $date = date('Y-m-d H:i:s', $DateTimeStamp);
            $collection->addFieldToFilter('updated_at', array('gteq' => $date ));
        }

        $collection->setOrder('updated_at','DESC');
        //echo $collection->getSelect()->__toString();
        // exit;
        if (array_key_exists('page', $params) && array_key_exists('count', $params) && !empty($params['page']) && !empty($params['count'])) {
            $page = $params['page'];
            $count = $params['count'];
            $totalProducts = $collection->getSize();

            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }
            if ($pageSize < $params['page']) {
                $Pagination['pageInfo'] = array('TotalItems' => 0, 'TotalPages' => 0);
                $result = array(
                    "Settings" => ["Code" => "400","Message" => "No data found"],
                    "Data" => $resultData,
                    "Pagination" => $Pagination
                );              
                echo json_encode($result); exit;

            } else {
                $startLimit = ($page - 1) * $count;
                $collection->setPageSize($count)->setCurPage($page);
            }

        }elseif (array_key_exists('count', $params) && !empty($params['count'])){
            $count = $params['count'];
            $totalProducts = $collection->getSize();
            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }

            $startLimit = (1 - 1) * $count;

            $collection->setPageSize($count)->setCurPage(1);
        }elseif (array_key_exists('page', $params) && !empty($params['page'])){
            $page = $params['page'];
            $count = self::PAGE_LIMIT;
            $totalProducts = $collection->getSize();
            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }

            $startLimit = ($page - 1) * $count;
            $collection->setPageSize($count)->setCurPage($page);
        }else{
            $page = 1;
            $count = self::PAGE_LIMIT;
            $totalProducts = $collection->getSize();
            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }

            $startLimit = ($page - 1) * $count;
            $collection->setPageSize($count)->setCurPage($page);
        }
        //echo $collection->getSelect()->__toString(); exit;
        //echo '<pre>';
       // print_r($collection->getData()); exit;
        if($collection->getSize() > 0) {
            $i= 0;
            foreach ($collection as $key => $value) {
                $resultData[$i]['LocalOrderId'] = strval($value->getData('local_order_id'));
                $resultData[$i]['QuoteId'] = strval($value->getData('app_quote_id'));                
                $resultData[$i]['Status'] = strval($value->getData('app_status'));
                $resultData[$i]['SalesPersonCode'] = strval($value->getSalesPersonCode());
                $resultData[$i]['CustomerId'] = strval($value->getCustomerId());
                $resultData[$i]['StoreName'] = strval($value->getStoreName());
                $resultData[$i]['ContainerType'] = strval($value->getContainerType());
                $resultData[$i]['ContainerSize'] = strval($value->getContainerSize());
                $resultData[$i]['LineNumbers'] = strval($value->getLineNumber());
                $resultData[$i]['OrderWeight'] = strval($value->getOrderWeight());
                $resultData[$i]['OrderVolume'] = strval($value->getOrderVolume());
                $resultData[$i]['OrderTotal'] = strval($value->getOrderTotal());
                $resultData[$i]['Notes'] = strval($value->getNotes());                
                $resultData[$i]['OrderDate'] = strval(strtotime($value->getCreatedAt()));
                $resultData[$i]['ModifiedDate'] = strval(strtotime($value->getUpdatedAt()));
                // $resultData[$i]['OrderDate1'] = strval(strtotime($this->generalHelper->dateConvertForListing($value->getCreatedAt())));
                // $resultData[$i]['ModifiedDate1'] = strval(strtotime($this->generalHelper->dateConvertForListing($value->getUpdatedAt())));
                $resultData[$i]['UserId'] = strval($value->getUserId());
                $resultData[$i]['DeviceId'] = strval($value->getDeviceId());
                $resultData[$i]['MagentoOrderId'] = "";
                $resultData[$i]['Deleted'] = strval($value->getIsDeleted());
                $i++;
            }    

            $Pagination['pageInfo']['TotalItems'] = $totalProducts;
            $Pagination['pageInfo']['TotalPages'] = $pageSize;

            $result = array(
                "Settings" => ["Code" => "200","Message" => "Success"],
                "Data" => $resultData,
                "Pagination" => $Pagination
            );              
            echo json_encode($result); exit;
        } else {
            $Pagination['pageInfo'] = array('TotalItems' => 0, 'TotalPages' => 0);
            $result = array(
                "Settings" => ["Code" => "400","Message" => "No data found"],
                "Data" => $resultData,
                "Pagination" => $Pagination
            );              
            echo json_encode($result); exit;
        }
    }

    /**
     * @param string $Version
     * @param string $Platform
     * @param string $DeviceId
     * @param mixed $Data
     * @return \Brainvire\Mobileapi\Api\Cart\CartInterface[]
     */
    public function deleteAppQuote($Version = '', $Platform= '', $DeviceId= '', $Data) {
        $resultData = [];
        $websiteId = 1;
        $platform = strtolower($Platform);
        $customer = $this->generalHelper->customerLoginCheck();

        if(count($Data)) {
            $i = 0;
            foreach ($Data as $key => $data) {
                // delete all quote
                $appQuote = $this->appQuoteFactory->create()->load($data['QuoteId']);
                if($appQuote->getAppQuoteId()){
                    $appQuote->setIsDeleted(1)->save();
                }
                /*
                $appQuote = $this->appQuoteFactory->create()->getCollection()->addFieldToFilter('app_quote_id', array('eq' => $data['QuoteId']));
                
                if ($appQuote->getSize()) {
                    $appQuote->walk('delete');
                }
                */

                // delete quote items
                // $appQuoteItems = $this->appQuoteItemFactory->create()->getCollection()->addFieldToFilter('app_quote_id', array('eq' => $data['QuoteId']));
                // foreach ($appQuoteItems as $item) {
                //     $appQuoteItem = $this->appQuoteItemFactory->create()->load($item->getAppQuoteItemId());
                //     if($appQuoteItem->getAppQuoteItemId()){
                //         $appQuoteItem->setIsDeleted(1)->save();
                //     }
                // }
               
                /*
                $appQuoteItem = $this->appQuoteItemFactory->create()->getCollection()->addFieldToFilter('app_quote_id', array('eq' => $data['QuoteId']));
                
                if ($appQuoteItem->getSize()) {
                    $appQuoteItem->walk('delete');
                }
                */
                $resultData[$i]['QuoteId'] = strval($data['QuoteId']);
                $i++;
            }
            
        }

        $result = array(
            "Settings" => ["Code" => "200","Message" => "Success"],
            "Data" => $resultData
        );              
        echo json_encode($result); exit;
    }

    /**
     * @param string $Version
     * @param string $Platform
     * @param string $DeviceId
     * @param string $QuoteId
     * @param string $DateTimeStamp
     * @return \Brainvire\Mobileapi\Api\Cart\CartInterface[]
     */
    public function listAppQuoteItems($Version = '', $Platform= '', $DeviceId= '', $QuoteId, $DateTimeStamp= '') {

        $resultData = $Pagination = [];
        $params = $this->request->getParams();

        $customer = $this->generalHelper->customerLoginCheck();
        
        $UserId = $customer['id'];
        
        $collection = $this->appQuoteItemFactory->create()->getCollection()->addFieldToFilter('app_quote_id', array('eq' => $QuoteId));

        if (array_key_exists('page', $params) && array_key_exists('count', $params) && !empty($params['page']) && !empty($params['count'])) {
            $page = $params['page'];
            $count = $params['count'];
            $totalProducts = $collection->getSize();

            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }
            if ($pageSize < $params['page']) {
                $Pagination['pageInfo'] = array('TotalItems' => 0, 'TotalPages' => 0);
                $result = array(
                    "Settings" => ["Code" => "400","Message" => "No data found"],
                    "Data" => $resultData,
                    "Pagination" => $Pagination
                );              
                echo json_encode($result); exit;

            } else {
                $startLimit = ($page - 1) * $count;
                $collection->setPageSize($count)->setCurPage($page);
            }

        }elseif (array_key_exists('count', $params) && !empty($params['count'])){
            $count = $params['count'];
            $totalProducts = $collection->getSize();
            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }

            $startLimit = (1 - 1) * $count;

            $collection->setPageSize($count)->setCurPage(1);
        }elseif (array_key_exists('page', $params) && !empty($params['page'])){
            $page = $params['page'];
            $count = self::PAGE_LIMIT;
            $totalProducts = $collection->getSize();
            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }

            $startLimit = ($page - 1) * $count;
            $collection->setPageSize($count)->setCurPage($page);
        }else{
            $page = 1;
            $count = self::PAGE_LIMIT;
            $totalProducts = $collection->getSize();
            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }

            $startLimit = ($page - 1) * $count;
            $collection->setPageSize($count)->setCurPage($page);
        }

        if($collection->getSize() > 0) {
            $i= 0;
            foreach ($collection as $key => $value) {
                $productId = $value->getData('product_id');
                $loadProductData = $this->productFactory->create()->load($productId);
                $resultData[$i]['QuoteItemId'] = strval($value->getData('app_quote_item_id'));
                $resultData[$i]['LocalQuoteItemId'] = strval($value->getData('local_quote_item_id'));
                $resultData[$i]['ProductId'] = strval($value->getData('product_id'));
                $resultData[$i]['PriceCode'] = strval($value->getData('price_code'));
                $resultData[$i]['ProductName'] = strval($value->getData('item_name'));
                $resultData[$i]['UnitPrice'] = strval($value->getUnitePrice());
                $resultData[$i]['CPQ'] = strval($value->getCpq());
                $resultData[$i]['IPQ'] = strval($value->getIpq());
                $resultData[$i]['Sellable'] = strval($value->getSellable());
                $resultData[$i]['TotalQuantity'] = strval($value->getTotalQuantity());
                $resultData[$i]['TotalPrice'] = strval($value->getTotalPrice());
                $resultData[$i]['UnitOfMeasure'] = strval($value->getUnitOfMeasure());
                $resultData[$i]['LineItemType'] = strval($value->getLineItemType());
                $resultData[$i]['Split'] = strval($value->getSplit());
                $resultData[$i]['LineNumber'] = strval($value->getLineNumber());
                $resultData[$i]['LineNotes'] = strval($value->getLineNotes());
                $resultData[$i]['Deleted'] = strval($value->getIsDeleted());

                $i++;
            }            
            $Pagination['pageInfo']['TotalItems'] = $totalProducts;
            $Pagination['pageInfo']['TotalPages'] = $pageSize;

            $result = array(
                "Settings" => ["Code" => "200","Message" => "Success"],
                "Data" => $resultData,
                "Pagination" => $Pagination
            );              
            echo json_encode($result); exit;
        } else {
            $Pagination['pageInfo'] = array('TotalItems' => 0, 'TotalPages' => 0);
            $result = array(
                "Settings" => ["Code" => "400","Message" => "No data found"],
                "Data" => $resultData,
                "Pagination" => $Pagination
            );              
            echo json_encode($result); exit;
        }
    }

    /**
     * @param string $Version
     * @param string $Platform
     * @param string $DeviceId
     * @param mixed $Data
     * @return \Brainvire\Mobileapi\Api\Cart\CartInterface[]
     */
    public function deleteAppQuoteItem($Version = '', $Platform= '', $DeviceId= '', $Data) {
        $resultData = [];
        $resultMessage = [];
        $websiteId = 1;
        $platform = strtolower($Platform);
        $customer = $this->generalHelper->customerLoginCheck();

        if(count($Data)) {
            $i = 0;
            //$del = 0;
            foreach ($Data as $key => $data) {
                // delete all quote
                $QuoteItems = [];
                $appQuote = $this->appQuoteFactory->create()->getCollection()->addFieldToFilter('app_quote_id', array('eq' => $data['QuoteId']));

                $getAllItems = $this->appQuoteItemFactory->create()->getCollection()->addFieldToFilter('app_quote_id', array('eq' => $data['QuoteId']));

                // if ($getAllItems->getSize() == count($data['Products'])) {
                //     $del = 1;
                // }

                $resultData[$i]['QuoteId'] = strval($data['QuoteId']);
                if (count($data['Products'])) {
                    $j = 0;
                    foreach ($data['Products'] as $key => $poducts) {
                        $QuoteItems[] = $poducts['QuoteItemId'];
                        $appQuoteItem = $this->appQuoteItemFactory->create()->load($poducts['QuoteItemId']);
                        if($appQuoteItem->getAppQuoteItemId()){
                            $appQuoteItem->setIsDeleted(1)->save();
                            $resultData[$i]['Products'][$j]['QuoteItemId'] = strval($poducts['QuoteItemId']);
                            $resultMessage = ["Code" => "200","Message" => "Success"];
                        }else{
                            $resultData[$i]['Products'][$j]['QuoteItemId'] = strval($poducts['QuoteItemId'])." This quote Item does not exist.";
                            $resultMessage = ["Code" => "400","Message" => "Quote Item does not exist."];
                        }
                        $j++;
                    }
                    
                    // delete quote items
                    // $appQuoteItem = $this->appQuoteItemFactory->create()->getCollection()->addFieldToFilter('app_quote_item_id', array('in' => $QuoteItems));
                    // if ($appQuoteItem->getSize()) {
                    //     foreach($appQuoteItem as $quoteItem){
                    //         $quoteItem->getAppQuoteItemId();
                    //     }
                    // }
                    // exit;
                    // if ($appQuoteItem->getSize()) {
                    //     $appQuoteItem->walk('delete');
                    // }
                    // Logic for if all items deleted then quote deleted. but it commented after discussed
                    // if ($del == 1) {
                    //     if ($appQuote->getSize()) {
                    //         $appQuote->walk('delete');
                    //     }
                    // }
                }

                $i++;
            }
        }

        $result = array(
            "Settings" => $resultMessage,
            "Data" => $resultData
        );              
        echo json_encode($result); exit;
    }

    /**
     * @param string $Version
     * @param string $Platform
     * @param string $DeviceId
     * @param string $DateTimeStamp
     * @return \Brainvire\Mobileapi\Api\Cart\CartInterface[]
     */
    public function listAppOrders($Version = '', $Platform= '', $DeviceId= '', $DateTimeStamp= '') {
        $resultData = $Pagination = [];
        $params = $this->request->getParams();

        $customer = $this->generalHelper->customerLoginCheck();
        
        $UserId = $customer['id'];
        // if ($UserId != $customer['id']) {
        //     $message = "UserId doesn't match with given token";
        //     $error = (object) array();
        //     header('x', true, 401);

        //     $result = array(
        //         "Settings" => ["Code" => "400","Message" => strval($message)]
        //     );  
        //     echo json_encode($result); exit;
        // }
        $collection = $this->_orderCollectionFactory->create()->addAttributeToSelect('*')->addFieldToFilter('user_id', array('eq' => $UserId));

        $salesPersonCode = $this->generalHelper->getSalesPersonCodeByUserId($UserId);
        $collection = $this->_orderCollectionFactory->create()->addAttributeToSelect('*')->addFieldToFilter(
            ['user_id','sales_person_code','device_unlock'], 
            [
                ['eq' => $UserId],
                ['in' => $salesPersonCode],
                ['eq' => 'Yes']
            ]        
        );
        // echo $collection->getSelect()->__toString();
        // die;
        if($DateTimeStamp){
            $date = date('Y-m-d H:i:s', $DateTimeStamp);
            $collection->addFieldToFilter('updated_at', array('gteq' => $date ));
        }

        $collection->setOrder('updated_at','DESC');

        if (array_key_exists('page', $params) && array_key_exists('count', $params) && !empty($params['page']) && !empty($params['count'])) {
            $page = $params['page'];
            $count = $params['count'];
            $totalProducts = $collection->getSize();

            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }
            if ($pageSize < $params['page']) {
                $Pagination['pageInfo'] = array('TotalItems' => 0, 'TotalPages' => 0);
                $result = array(
                    "Settings" => ["Code" => "400","Message" => "No data found"],
                    "Data" => $resultData,
                    "Pagination" => $Pagination
                );              
                echo json_encode($result); exit;

            } else {
                $startLimit = ($page - 1) * $count;
                $collection->setPageSize($count)->setCurPage($page);
            }

        }elseif (array_key_exists('count', $params) && !empty($params['count'])){
            $count = $params['count'];
            $totalProducts = $collection->getSize();
            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }

            $startLimit = (1 - 1) * $count;

            $collection->setPageSize($count)->setCurPage(1);
        }elseif (array_key_exists('page', $params) && !empty($params['page'])){
            $page = $params['page'];
            $count = self::PAGE_LIMIT;
            $totalProducts = $collection->getSize();
            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }

            $startLimit = ($page - 1) * $count;
            $collection->setPageSize($count)->setCurPage($page);
        }else{
            $page = 1;
            $count = self::PAGE_LIMIT;
            $totalProducts = $collection->getSize();
            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }

            $startLimit = ($page - 1) * $count;
            $collection->setPageSize($count)->setCurPage($page);
        }

        if($collection->getSize() > 0) {
            $i= 0;
            foreach ($collection as $key => $value) {
                $exported = $value->getExported();
                $acknowleadge = $value->getAcknowleadge();
                $statusLogic = $this->generalHelper->statusLogic($exported, $acknowleadge);
                $resultData[$i]['MagentoOrderId'] = strval($value->getData('increment_id'));
                $resultData[$i]['LocalOrderId'] = strval($value->getData('local_app_order_id'));
                $resultData[$i]['QuoteId'] = strval($value->getData('app_quote_id'));
                $resultData[$i]['ERPOrderId'] = strval($value->getData('erp_order_number'));
                $resultData[$i]['Status'] = strval($statusLogic);
                $resultData[$i]['SalesPersonCode'] = strval($value->getSalesPersonCode());
                $resultData[$i]['CustomerId'] = strval($value->getCustomerId());
                $resultData[$i]['StoreName'] = strval($value->getStoreName());
                $resultData[$i]['ContainerType'] = strval($value->getContainerType());
                $resultData[$i]['ContainerSize'] = strval($value->getContainerSize());
                $resultData[$i]['LineNumbers'] = strval($value->getLineNumber());
                $resultData[$i]['OrderWeight'] = strval($value->getTotalWeight());
                $resultData[$i]['OrderVolume'] = strval($value->getTotalCube());
                $resultData[$i]['OrderTotal'] = strval($value->getGrandTotal());
                $resultData[$i]['Notes'] = strval($value->getCustomerNote());
                $resultData[$i]['OrderDate'] = strval(strtotime($value->getCreatedAt()));
                $resultData[$i]['ModifiedDate'] = strval(strtotime($value->getCreatedAt()));
                $resultData[$i]['UserId'] = strval($value->getUserId());
                $resultData[$i]['DeviceId'] = strval($value->getDeviceId());
                $i++;
            }    
            $Pagination['pageInfo']['TotalItems'] = $totalProducts;
            $Pagination['pageInfo']['TotalPages'] = $pageSize;

            $result = array(
                "Settings" => ["Code" => "200","Message" => "Success"],
                "Data" => $resultData,
                "Pagination" => $Pagination
            );              
            echo json_encode($result); exit;
        } else {
            $Pagination['pageInfo'] = array('TotalItems' => 0, 'TotalPages' => 0);
            $result = array(
                "Settings" => ["Code" => "400","Message" => "No data found"],
                "Data" => $resultData,
                "Pagination" => $Pagination
            );              
            echo json_encode($result); exit;
        }
    }

    /**
     * @param string $Version
     * @param string $Platform
     * @param string $DeviceId
     * @param string $MagentoOrderId
     * @return \Brainvire\Mobileapi\Api\Cart\CartInterface[]
     */
    public function getOrderDetails($Version = '', $Platform= '', $DeviceId= '', $MagentoOrderId) {
        $resultData = $Pagination = [];
        $params = $this->request->getParams();

        $customer = $this->generalHelper->customerLoginCheck();
        
        $orderModel = $this->orderFactory->create()->load($MagentoOrderId, 'increment_id');    
        
        $collection = $this->_orderItemCollectionFactory->create()->addAttributeToSelect('*')->addFieldToFilter('order_id', array('eq' => $orderModel->getId()));
        
        if($collection->getSize() > 0) {
            $i= 0;
            foreach ($collection as $key => $value) {
                $resultData[$i]['QuoteItemId'] = strval($value->getData('item_id'));
                $resultData[$i]['LocalQuoteItemId'] = strval($value->getData('local_quote_item_id'));
                $resultData[$i]['ProductId'] = strval($value->getData('product_id'));
                $resultData[$i]['PriceCode'] = strval($value->getData('price_code'));
                $resultData[$i]['ProductName'] = strval($value->getData('name'));
                $resultData[$i]['UnitPrice'] = strval($value->getPrice());
                $resultData[$i]['CPQ'] = strval($value->getCp());
                $resultData[$i]['IPQ'] = strval($value->getIp());
                $resultData[$i]['Sellable'] = strval($value->getSellable());
                $resultData[$i]['TotalQuantity'] = strval($value->getQtyOrdered());
                $resultData[$i]['TotalPrice'] = strval($value->getRowTotal());
                $resultData[$i]['UnitOfMeasure'] = strval($value->getUniteOfMeasure());
                $resultData[$i]['LineItemType'] = strval($value->getLineItemType());
                $resultData[$i]['Split'] = strval($value->getSplit());
                $resultData[$i]['LineNumber'] = strval($value->getLineItemNumber());
                $resultData[$i]['LineNotes'] = strval($value->getLineNotes());
                $i++;
            }    

            $result = array(
                "Settings" => ["Code" => "200","Message" => "Success"],
                "Data" => $resultData
            );              
            echo json_encode($result); exit;
        } else {
            $result = array(
                "Settings" => ["Code" => "400","Message" => "No data found"],
                "Data" => $resultData
            );              
            echo json_encode($result); exit;
        }
    }
}