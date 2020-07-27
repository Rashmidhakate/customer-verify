<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Brainvire\Customapi\Model\Product;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * product information class
 */
class Product implements \Brainvire\Customapi\Api\Product\ProductInterface
{
    protected $_productCollectionFactory;
    const PAGE_LIMIT = '20';
    
    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Brainvire\Mobileapi\Helper\Data $generalHelper
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->productFactory = $productFactory;
        $this->curlFactory = $curlFactory;
        $this->jsonHelper = $jsonHelper;
        $this->request = $request;
        $this->storeManager = $storeManager;  
        $this->generalHelper = $generalHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductList($timestamp = "")
    {
        $productArray = [];
        $resultData = [];
        $params = $this->request->getParams();
        $this->checkLoginCustomer();
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        if($timestamp){
            $date = date('Y-m-d H:i:s', $timestamp);
            //$date =  $this->generalHelper->dateConvert($date);
            $collection->addFieldToFilter('updated_at', array('gteq' => $date ));
            
            // $dateFormat = date('Y-m-d H:i:s', $timestamp);
            // $collection->addFieldToFilter('updated_at', array('gt' => $dateFormat));
        }

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
        
        if($collection->getSize() > 0){
            foreach ($collection as $product) {
                $productArray[] = [
                    "ProductID" => strval($product->getId()),
                    "SKU" => strval($product->getSku()),
                    "ProductName" => strval($product->getName()),
                    "BrandName" => strval($product->getBrands()),
                    "PriceCode" => strval($product->getPriceCode()),
                    "StandardPrice" => strval(round($product->getPrice(),2)),
                    "MinPrice" => strval(round($product->getMinPrice(),2)),
                    "SpecialPrice" => strval(round($product->getSpecialPrice(),2)),
                    "QTYLimit" => strval($product->getQtyLimit()),
                    "CPQ" => strval($product->getCpQty()),
                    "IPQ" => strval($product->getIpQty()),
                    "CasePackVolume" => strval($product->getCasePackVolume()),
                    "CaseWeightPounds" => strval($product->getCaseWeightPounds()),
                    "UPC" => strval($product->getUpc()),
                    "QtyOnHand" => strval($product->getQuantityOnHand()),
                    "QtyOnSO" => strval($product->getQtyOnSo()),
                    "QtyOnPO" => strval($product->getQtyOnPo()),
                    "QTYRO" => strval($product->getQtyRo()),
                    "PictureURL" => strval($product->getImageUrl()),
                    "Sellable" => strval($product->getSellable()),
                    "ShowLoc" => strval($product->getShowLoc()),
                    "Hazmat" => strval($product->getHazmat()),
                    "Assortment" => strval($product->getAssortment()),
                    "Color" => strval($product->getColor()),
                ];
            }
            $Pagination['pageInfo']['TotalItems'] = $totalProducts;
            $Pagination['pageInfo']['TotalPages'] = $pageSize;
            $productsData = [
                "Settings" => [
                    "Code" => "200",
                    "Message" => "Products data synced successfully",
                ],
                "Data" => $productArray,
                "Pagination" => $Pagination
            ];
            $ary_response[] = $productsData;
        }else{
            $Pagination['pageInfo'] = array('TotalItems' => 0, 'TotalPages' => 0);
            $productsData = [
                "Settings" => [
                    "Code" => "400",
                    "Message" => "We can't find products matching the selection.",
                    "Pagination" => $Pagination
                ]
            ];
            $ary_response[] = $productsData;
        }
        echo json_encode($productsData); exit;
    }

    public function GetProductQtyAndPrice($productId){
        $collection = $this->productFactory->create();
        $collection->load($productId);
        if (!$collection->getId()) {
            $productsData =[
                "Settings" => [
                    "Code" => "400",
                    "Message" => "The product that was requested doesn't exist. Verify the product and try again.",
                ]
            ];
            $ary_response[] = $productsData;
        }else{
            if($collection->getStatus() != 1){
                $Sellable = 'N';
            }else{
                $Sellable = strval($collection->getSellable());
            }
            $productArray = [
                "StandardPrice" => strval(round($collection->getPrice(),2)),
                "MinPrice" => strval(round($collection->getMinPrice(),2)),
                "SpecialPrice" => strval(round($collection->getSpecialPrice(),2)),
                "QtyOnHand" => strval($collection->getQuantityOnHand()),
                "QtyOnSO" => strval($collection->getQtyOnSo()),
                "QtyOnPO" => strval($collection->getQtyOnPo()),
                "QTYRO" => strval($collection->getQtyRo()),
                "Sellable" => $Sellable
            ];
            $productsData =[
                "Settings" => [
                    "Code" => "200",
                    "Message" => "Success",
                ],
                "Data" => $productArray
            ];
            $ary_response[] = $productsData;
        }
        echo json_encode($productsData); exit; 
    }

    public function SetProductQtyAndPrice($SKU, $StandardPrice='', $MinPrice='', $SpecialPrice='', $QuantityOnHand='', $QtyOnSO='', $QtyOnPO='', $QTYRO='', $Sellable='', $QTYLimit=''){
        $collection = $this->productFactory->create();
        $collection->load($collection->getIdBySku($SKU));
        $saveFlag = 0;
        if(isset($StandardPrice) && $StandardPrice != '') {
            $collection->setPrice($StandardPrice);
            $saveFlag = 1;
        }
        if(isset($MinPrice) && $MinPrice != '') {
            $collection->setMinPrice($MinPrice);
            $saveFlag = 1;
        }
        if(isset($SpecialPrice) && $SpecialPrice != '') {
            $collection->setSpecialPrice($SpecialPrice);
            $saveFlag = 1;
        }
        if(isset($QuantityOnHand) && $QuantityOnHand != '') {
            $collection->setQuantityOnHand($QuantityOnHand);
            $saveFlag = 1;
        }
        if(isset($QtyOnSO) && $QtyOnSO != '') {
            $collection->setQtyOnSo($QtyOnSO);
            $saveFlag = 1;
        }
        if(isset($QtyOnPO) && $QtyOnPO != '') {
            $collection->setQtyOnPo($QtyOnPO);
            $saveFlag = 1;
        }
        if(isset($QTYRO) && $QTYRO != '') {
            $collection->setQtyRo($QTYRO);
            $saveFlag = 1;
        }
        if(isset($Sellable) && $Sellable != '') {
            $collection->setSellable($Sellable);
            $saveFlag = 1;
        }
        if(isset($QTYLimit) && $QTYLimit != '') {
            $collection->setQtyLimit($QTYLimit);
            $saveFlag = 1;
        }
        
        if($saveFlag) {
            $collection->save();
        }
        
        if (!$collection->getId()) {
            $productsData =[
                "Settings" => [
                    "Code" => "400",
                    "Message" => "The product that was requested doesn't exist. Verify the product and try again.",
                ]
            ];
            $ary_response[] = $productsData;
        }else{
            $productArray = [
                "SKU" => $collection->getSku()
            ];
            $productsData =[
                "Settings" => [
                    "Code" => "200",
                    "Message" => "Product Data updated",
                ],
                "Data" => $productArray
            ];
            $ary_response[] = $productsData;
        }
        echo json_encode($productsData); exit; 
    }
      /**
     * {@inheritdoc}
     */
    public function getProductListOnRoot($timestamp = "")
    {
        $productArray = [];
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        if($timestamp){
            $dateFormat = date('Y-m-d H:i:s', $timestamp);
            $collection->addFieldToFilter('updated_at', array('gt' => $dateFormat));
        }
        if(sizeof($collection) != 0){
            foreach ($collection as $product) {
                $productArray[] = [
                    "ProductID" => $product->getId(),
                    "SKU" => $product->getSku(),
                    "ProductName" => $product->getName(),
                    "BrandName" => $product->getBrands(),
                    "PriceCode" => $product->getPriceCode(),
                    "StandardPrice" => round($product->getPrice(),2),
                    "MinPrice" => round($product->getMinPrice(),2),
                    "SpecialPrice" => round($product->getSpecialPrice(),2),
                    "QTYLimit" => $product->getQtyLimit(),
                    "CPQ" => $product->getCpQty(),
                    "IPQ" => $product->getIpQty(),
                    "CasePackVolume" => $product->getCasePackVolume(),
                    "CaseWeightPounds" => $product->getCaseWeightPounds(),
                    "UPC" => $product->getUpc(),
                    "QtyOnHand" => $product->getQuantityOnHand(),
                    "QtyOnSO" => $product->getQtyOnSo(),
                    "QtyOnPO" => $product->getQtyOnPo(),
                    "QTYRO" => $product->getQtyRo(),
                    "PictureURL" => $product->getImageUrl(),
                    "Sellable" => $product->getSellable(),
                    "ShowLoc" => $product->getShowLoc(),
                    "Hazmat" => $product->getHazmat(),
                    "Assortment" => strval($product->getAssortment()),
                    "Color" => strval($product->getColor()),
                ];
            }
            $productsData = [
                "Settings" => [
                    "Code" => "200",
                    "Message" => "Products data synced successfully",
                ],
                "Data" => $productArray
            ];
            $ary_response[] = $productsData;
        }else{
            $productsData = [
                "Settings" => [
                    "Code" => "400",
                    "Message" => "We can't find products matching the selection.",
                ]
            ];
            $ary_response[] = $productsData;
        }
        $json = \Zend_Json::encode($productsData);
        $productJsonContent = \Zend_Json::prettyPrint($json);
        return $productJsonContent;
    }

    public function checkLoginCustomer(){
        $host = $this->storeManager->getStore()->getBaseUrl();
        $url = $host.'rest/V1/customers/me';
        $Pagination = [];
        $headers = array(
            'Authorization:  '. $this->request->getHeader('Authorization'),
        );
        $httpAdapter = $this->curlFactory->create();
        $httpAdapter->write(\Zend_Http_Client::GET, $url, '1.1', $headers);
        $result = $httpAdapter->read();
        $body = \Zend_Http_Response::extractBody($result);
        /* convert JSON to Array */
        $response = $this->jsonHelper->jsonDecode($body);
        if (array_key_exists('message',$response)){
            $message = "Customer is not logged in.";
            $error = (object) array();
            header('x', true, 401);

            $result = array(
                "Settings" => ["Code" => "400","Message" => strval($message)]
            );  
            echo $this->jsonHelper->jsonEncode($result); exit;
        }
    }
}
