<?php

namespace Brainvire\Mobileapi\Helper;

use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use \Magento\Store\Model\StoreManagerInterface;
use Brainvire\Custom\Model\AppQuoteFactory;
use Brainvire\Custom\Model\AppQuoteItemFactory;
use Brainvire\SalesReps\Model\SalesRepsFactory;

/**
 * Class - Brainvire\Mobileapi\Helper\StoreInformation
 * Module - Brainvire\Mobileapi
 * Description - Get Current Store Information
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    const LOGIN_ENABLE = 1;
    const LOGIN_DISABLE = 0;
    const XML_PATH_ANDROID_VERSION = 'mobileapi/version/android_version';
    const XML_PATH_IOS_VERSION = 'mobileapi/version/ios_version';
    const XML_PATH_ANDROID_PLATFORM = 'android';
    const XML_PATH_IOS_PLATFORM = 'ios';
    const XML_PATH_MOBILEAPI_LOCALE_TIMEZONE = 'mobileapi/locale/timezone';

    protected $_storeManager;
    protected $_currency;

    public function __construct(
    Context $context,
    // \Mirasvit\Rewards\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
    // \Mirasvit\Rewards\Model\ResourceModel\ReferralLink\CollectionFactory $referralLinkCollectionFactory,
            \Magento\Directory\Model\Currency $currency, TimezoneInterface $timezoneInterface, \Magento\Framework\App\ResourceConnection $resourceConnection, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, StoreManagerInterface $storeManager, \Brainvire\SalesReps\Model\ResourceModel\SalesReps\CollectionFactory $salesRepsCollectionFactory, \Magento\Framework\App\RequestInterface $request, AppQuoteFactory $appQuoteFactory, AppQuoteItemFactory $appQuoteItemFactory, \Magento\Catalog\Model\ProductFactory $productFactory, SalesRepsFactory $salesRepsFactory, \Magento\Customer\Model\Customer $customerCheck
    ) {
        $this->_storeManager = $storeManager;
        $this->_currency = $currency;
        $this->resourceConnection = $resourceConnection;
        // $this->referralLinkCollectionFactory = $referralLinkCollectionFactory;
        // $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->_timezoneInterface = $timezoneInterface;
        $this->_scopeConfig = $scopeConfig;
        $this->salesRepsCollectionFactory = $salesRepsCollectionFactory;
        $this->request = $request;
        $this->appQuoteFactory = $appQuoteFactory;
        $this->appQuoteItemFactory = $appQuoteItemFactory;
        $this->productFactory = $productFactory;
        $this->salesRepsFactory = $salesRepsFactory;
        $this->customerCheck = $customerCheck;
        parent::__construct($context);
    }

    public function loginSuccess() {
        return self::LOGIN_ENABLE;
    }

    public function logoutSuccess() {
        return self::LOGIN_DISABLE;
    }

    public function _checkDeviceIds($deviceToken, $cusId, $platform) {
        // $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('bv_auth_mobile_token');
        $sql = "Select id FROM " . $tableName . " WHERE customer_id = '" . $cusId . "' AND platform LIKE '" . $platform . "' AND device_token = '" . $deviceToken . "'";
        $result = $connection->fetchCol($sql);
        return $result;
    }

    public function _checkLoginStatus($deviceToken, $cusId, $platform) {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('bv_auth_mobile_token');
        $sql = "Select id FROM " . $tableName . " WHERE customer_id = '" . $cusId . "' AND platform LIKE '" . $platform . "' AND device_token = '" . $deviceToken . "' AND login_status = '" . $this->loginSuccess() . "'";
        $result = $connection->fetchCol($sql);
        return $result;
    }

    // public function getReferralLinkId($customerId) {
    // 	$link = $this->referralLinkCollectionFactory->create()
    // 		->addFieldToFilter('customer_id', $customerId)
    // 		->getFirstItem();
    // 	//if we haven't generated link, create it
    // 	if (!$link->getId()) {
    // 		$link->createReferralLinkId($customerId);
    // 	}
    // 	return $link->getReferralLink();
    // }
    // public function getTransactionCollection($customerId) {
    // 	$this->_collection = $this->transactionCollectionFactory->create()
    // 		->addFieldToFilter('customer_id', $customerId)
    // 		->setOrder('created_at', 'desc')
    // 	;
    // 	return $this->_collection;
    // }

    public function getServerTimeZone() {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_MOBILEAPI_LOCALE_TIMEZONE, $storeScope);
    }

    public function getFormatedDate($dateTime) {
        $dateTimeAsTimeZone = $this->_timezoneInterface
                ->formatDate(new \DateTime($dateTime), \IntlDateFormatter::MEDIUM, false);
        return $dateTimeAsTimeZone;
    }

    public function getLiveAndroidVersion() {
        return $this->_scopeConfig->getValue(self::XML_PATH_ANDROID_VERSION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getLiveIosVersion() {
        return $this->_scopeConfig->getValue(self::XML_PATH_IOS_VERSION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function restrictLoginGroups() {
        return ['0', '1', '2', '3'];
    }

    public function addProductOptions($items) {
        $result = [];
        $itemArray = [
            'CPQ' => ($items->getCp()) ? $items->getCp() : 0,
            'IPQ' => ($items->getIp()) ? $items->getIp() : 0,
            'PriceCode' => ($items->getPriceCode()) ? $items->getPriceCode() : '',
            'LineItemType' => ($items->getLineItemType()) ? $items->getLineItemType() : '',
            'LineNumber' => ($items->getLineItemNumber()) ? $items->getLineItemNumber() : ''
        ];
        $i = 0;
        foreach ($itemArray as $key => $item) {
            $result[$i]['label'] = $key;
            $result[$i]['value'] = $item;
            $i++;
        }

        return $result;
    }

    public function getSalesPersonCodeByUserId($userId) {
        $collection = $this->salesRepsCollectionFactory->create()->addFieldToFilter('customer_ids', $userId);
        $collection->getSelect()->group('sales_person_group');
        $result = [];
        foreach ($collection as $key => $value) {
            $result[] = $value['sales_person_group'];
        }
        return $result;
    }

    public function dateConvert($date) {
        $magentoTZ = date_default_timezone_get();
        $serverTZ = ($this->getServerTimeZone()) ? $this->getServerTimeZone() : 'CST';
        $format = 'Y-m-d H:i:s';
        $d = new \DateTime($date, new \DateTimeZone($magentoTZ));
        $d->setTimezone(new \DateTimeZone($serverTZ));
        return $d->format($format);
    }

    public function customerLoginCheck() {
        $headers = array(
            'Authorization:  ' . $this->request->getHeader('Authorization'),
        );
        $ch = curl_init();
        $host = $this->_storeManager->getStore()->getBaseUrl();
        curl_setopt($ch, CURLOPT_URL, $host . 'rest/V1/customers/me');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $output = curl_exec($ch);
        $customer = json_decode($output, 1);
        if (array_key_exists('message', $customer)) {
            $message = "Customer is not logged in.";
            $error = (object) array();
            header('x', true, 401);

            $result = array(
                "Settings" => ["Code" => "400", "Message" => strval($message)]
            );
            echo json_encode($result);
            exit;
        }

        curl_close($ch);

        return $customer;
    }

    public function checkQuoteDataValidation($Data) {
        $message = '';
        $error = $error2 = 0;
        if (count($Data) > 0) {
            $j = 0;
            foreach ($Data as $key => $data) {

                if (isset($data['QuoteId']) && $data['QuoteId'] != '') {
                    $appQuote = $this->appQuoteFactory->create();
                    $appQuote->load($data['QuoteId']);
                    if (!is_numeric($data['QuoteId'])) {
                        $message = '#' . $data['QuoteId'] . " - Numeric value required for QuoteId";
                        $error = 1;
                        break;
                    }

                    if (empty($appQuote->getData()) && $error != 1) {
                        $message = '#' . $data['QuoteId'] . " - The Quote that was requested doesn't exist. Verify the Quote and try again.";
                        $error = 1;
                        break;
                    }
                }

                if (isset($data['CustomerId']) && $data['CustomerId'] != '') {
                    $this->_storeManager->setCurrentStore('default');
                    $websiteId = $this->_storeManager->getStore()->getWebsiteId();
                    $_customer = $this->customerCheck->setWebsiteId($websiteId)->load($data['CustomerId']);

                    if (empty($_customer->getId()) && $error != 1) {
                        $message = '#' . $data['CustomerId'] . " - The customer that was requested doesn't exist. Verify the customer and try again.";
                        $error = 1;
                        break;
                    } else {
                        $customerAddress = array();

                        foreach ($_customer->getAddresses() as $address)
                        {
                            $customerAddress[] = $address->toArray();
                        }

                        foreach ($customerAddress as $customerAddres) {
                            
                            if (strlen($customerAddres['street']) < 2 || 
                                strlen($customerAddres['city']) < 2 || 
                                strlen($customerAddres['postcode']) == '' ||
                                strlen($customerAddres['region']) == ''
                                ) {
                                $message = '#' . $data['CustomerId'] . " - Customer Address is not validate";
                                $error = 1;
                                break;
                            }                            
                        }
                    }
                }
                if (isset($data['SalesPersonCode']) && $data['SalesPersonCode'] != '' && $error != 1) {
                    $salesReps = $this->salesRepsFactory->create();
                    $salesReps->load($data['SalesPersonCode'], 'sales_person_group');
                    if (empty($salesReps->getData())) {
                        $message = '#' . $data['SalesPersonCode'] . " - The requested sales person code doesn't exist. Verify sales person code and try again.";
                        $error = 1;
                        break;
                    }
                }


                if (!empty($data['Products'])) {
                    foreach ($data['Products'] as $items) {
                        $productId = $items['ProductId'];
                        $loadProductData = $this->productFactory->create()->load($productId);
                        
                        if ($loadProductData->getStatus() == 2) {
                            $message = '#' . $items['ProductId'] . " - The requested product is disable.";
                            $error = 1;
                            break;
                        }    
                        
                        if (empty($loadProductData->getId()) && $error != 1) {
                            $message = '#' . $items['ProductId'] . " - The product that was requested doesn't exist. Verify the product and try again.";
                            $error = 1;
                            break;
                        }
                        if ($items['UnitPrice'] != '' && !is_numeric($items['UnitPrice'])) {
                            $error2 = 1;
                            break;
                        }

                        if ($items['TotalQuantity'] != '' && !is_numeric($items['TotalQuantity'])) {
                            $error2 = 1;
                            break;
                        }

                        if ($items['CPQ'] != '' && !is_numeric($items['CPQ'])) {
                            $error2 = 1;
                            break;
                        }

                        if ($items['IPQ'] != '' && !is_numeric($items['IPQ'])) {
                            $error2 = 1;
                            break;
                        }
                    }
                }


                if (isset($data['OrderTotal']) && $data['OrderTotal'] != '') {
                    if (!is_numeric($data['OrderTotal'])) {
                        $error2 = 1;
                        break;
                    }
                }

                if (isset($data['OrderWeight']) && $data['OrderWeight'] != '') {
                    if (!is_numeric($data['OrderWeight'])) {
                        $error2 = 1;
                        break;
                    }
                }

                if (isset($data['OrderVolume']) && $data['OrderVolume'] != '') {
                    if (!is_numeric($data['OrderVolume'])) {
                        $error2 = 1;
                        break;
                    }
                }

                if (isset($data['ContainerSize']) && $data['ContainerSize'] != '') {
                    if (!is_numeric($data['ContainerSize'])) {
                        $error2 = 1;
                        break;
                    }
                }

                if (isset($data['LineNumbers']) && $data['LineNumbers'] != '') {
                    if (!is_numeric($data['LineNumbers'])) {
                        $error2 = 1;
                        break;
                    }
                }

                $j++;
            }
            if ($error) {
                $result = array(
                    "Settings" => ["Code" => "400", "Message" => strval($message)]
                );
                echo json_encode($result);
                exit;
            }

            if ($error2) {
                $message = __("Invalid Data");
                $result = array(
                    "Settings" => ["Code" => "400", "Message" => strval($message)]
                );
                echo json_encode($result);
                exit;
            }

            return true;
        }
    }

    public function statusLogic($exported, $acknowleadge) {
        $exported = strtolower($exported);
        $acknowleadge = strtolower($acknowleadge);
        if ($exported == 'new' && $acknowleadge == '') {
            $status = 'Submitted';
        } else if ($acknowleadge == 'waiting' || $acknowleadge == 'failed') {
            $status = 'Waiting';
        } else if ($acknowleadge == 'approved') {
            $status = 'Remote Order';
        } else {
            $status = 'Submitted';
        }
        return $status;
    }

}

?>
