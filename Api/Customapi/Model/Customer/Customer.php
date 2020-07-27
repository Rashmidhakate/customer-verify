<?php

namespace Brainvire\Customapi\Model\Customer;


/**
 * Defines the implementaiton class of the \Brainvire\Customapi\Api\CustomerInterface
 */
class Customer extends \Magento\Framework\Model\AbstractModel implements \Brainvire\Customapi\Api\Customer\CustomerInterface {

	protected $customerCheck;
	const PAGE_LIMIT = '20';
	/**
	 *
	 * @param \Magento\Customer\Model\Customer $customerCheck
	 */
	public function __construct(
		\Magento\Customer\Model\Customer $customerCheck,
		\Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
		\Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
		\Magento\Framework\Json\Helper\Data $jsonHelper,
		\Magento\Framework\App\RequestInterface $request,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Brainvire\Mobileapi\Helper\Data $generalHelper
	) {
		$this->customerCheck = $customerCheck;
		$this->groupRepository = $groupRepository;
		$this->curlFactory = $curlFactory;
		$this->jsonHelper = $jsonHelper;
		$this->request = $request;
		$this->storeManager = $storeManager;
		$this->generalHelper = $generalHelper;
	}

	public function getCustomerList($timestamp = ""){
		$customerArray = [];
		$resultData = [];
		$params = $this->request->getParams();
		$this->checkLoginCustomer();
		$collection = $this->customerCheck->getCollection();
		$collection->addAttributeToSelect("*");
		if($timestamp){
			// $dateFormat = date('Y-m-d H:i:s', $timestamp);
		 //    $collection->addFieldToFilter('updated_at', array('gt' => $dateFormat));
		    $date = date('Y-m-d H:i:s', $timestamp);
            //$date =  $this->generalHelper->dateConvert($date);
            $collection->addFieldToFilter('updated_at', array('gteq' => $date ));
		}
		$collection->addFieldToFilter('group_id', array('in' => array(1)));
		//$collection->load();
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
			foreach ($collection as $customer) {
			 	$group = $this->groupRepository->getById($customer->getGroupId());
				$customerGroupName = $group->getCode();
				if($customer->getAddresses()){
					foreach ($customer->getAddresses() as $address)
					{
						$street = $address->getStreet();
						if(isset($street[1])){
							$streetLine2 =  $street[1];
						}else{
							$streetLine2 = null;
						}
						if(isset($street[2])){
							$streetLine3 = $street[2];
						}else{
							$streetLine3 = null;
						}

						$customerArray[] = [
								"CustomerId" => strval($customer->getId()),
								"Division" => strval($customer->getErpDivision()),
								"CustomerNumber" => strval($customer->getErpCustomerNumber()),
								"CustomerName" => strval($customer->getName()),
								"Email" => strval($customer->getEmail()),
								"PhoneNumber" => strval($customer->getTelephone()),
								"AddressLine1" => strval($street[0]),
								"AddressLine2" => strval($streetLine2),
								"AddressLine3" => strval($streetLine3),
								"City" => strval($address->getCity()),
								"State" => strval($address->getRegion()),
								"ZipCode" => strval($address->getPostcode()),
								"CountryCode" => strval($address->getCountry()),
								"FirstName" => strval($customer->getFirstname()),
								"LastName" => strval($customer->getLastname()),
								"MobilePhone" => strval($customer->getCustomerMobilePhone())
							];
					}
				}else{
					$customerArray[] = [
							"CustomerId" => strval($customer->getId()),
							"Division" => strval($customer->getErpDivision()),
							"CustomerNumber" => strval($customer->getErpCustomerNumber()),
							"CustomerName" => strval($customer->getName()),
							"Email" => strval($customer->getEmail()),
							"PhoneNumber" => strval($customer->getTelephone()),
							"AddressLine1" => "",
							"AddressLine2" => "",
							"AddressLine3" => "",
							"City" => "",
							"State" => "",
							"ZipCode" => "",
							"CountryCode" => "",
							"FirstName" => strval($customer->getFirstname()),
							"LastName" => strval($customer->getLastname()),
							"MobilePhone" => strval($customer->getCustomerMobilePhone())
						];
				}

			}
			$Pagination['pageInfo']['TotalItems'] = $totalProducts;
            $Pagination['pageInfo']['TotalPages'] = $pageSize;
			$customersData = [
				"Settings" => [
					"Code" => "200",
					"Message" => "Customers data synced successfully",
				],
				"Data" => $customerArray,
				"Pagination" => $Pagination
			];
			$ary_response[] = $customersData;

        }else{
        	$Pagination['pageInfo'] = array('TotalItems' => 0, 'TotalPages' => 0);
			$customersData = [
				"Settings" => [
					"Code" => "400",
					"Message" => "We can't find customers matching the selection.",
					"Pagination" => $Pagination
				]
			];
			$ary_response[] = $customersData;
        }
       	echo $this->jsonHelper->jsonEncode($customersData); exit;
	}

	public function getUserList($timestamp = ""){
		$userArray = [];
		$resultData = [];
		$params = $this->request->getParams();
		$this->checkLoginCustomer();
		$collection = $this->customerCheck->getCollection();
		$collection->addAttributeToSelect("*");
		if($timestamp){
			$date = date('Y-m-d H:i:s', $timestamp);
            //$date =  $this->generalHelper->dateConvert($date);
            $collection->addFieldToFilter('updated_at', array('gteq' => $date ));

			// $dateFormat = date('Y-m-d H:i:s', $timestamp);
		 //    $collection->addFieldToFilter('updated_at', array('gt' => $dateFormat));
		}
		$collection->addFieldToFilter('group_id', array('in' => array(4,5,6,7,8,9)));

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
			foreach ($collection as $customer) {
			 	$group = $this->groupRepository->getById($customer->getGroupId());
				$customerGroupName = $group->getCode();
				if($customer->getAddresses()){
					foreach ($customer->getAddresses() as $address)
					{
						$street = $address->getStreet();
						if(isset($street[1])){
							$streetLine2 =  $street[1];
						}else{
							$streetLine2 = null;
						}
						if(isset($street[2])){
							$streetLine3 = $street[2];
						}else{
							$streetLine3 = null;
						}

						$userArray[] = [
								"CustomerId" => strval($customer->getId()),
								"Email" => strval($customer->getEmail()),
								"PhoneNumber" => strval($customer->getTelephone()),
								"AddressLine1" => strval($street[0]),
								"AddressLine2" => strval($streetLine2),
								"AddressLine3" => strval($streetLine3),
								"City" => strval($address->getCity()),
								"State" => strval($address->getRegion()),
								"ZipCode" => strval($address->getPostcode()),
								"CountryCode" => strval($address->getCountry()),
								"SalesPersonCode" => strval($customer->getSalesPersonCode()),
								"FirstName" => strval($customer->getFirstname()),
								"LastName" => strval($customer->getLastname()),
								"MobilePhone" => strval($customer->getCustomerMobilePhone()),
								"CustomerTitle" => strval($customer->getUserTitle()),
								"CustomerGroup" => strval($customerGroupName)
							];
					}
				}else{
					$userArray[] = [
							"CustomerId" => strval($customer->getId()),
							"Email" => strval($customer->getEmail()),
							"PhoneNumber" => strval($customer->getTelephone()),
							"AddressLine1" => "",
							"AddressLine2" => "",
							"AddressLine3" => "",
							"City" => "",
							"State" => "",
							"ZipCode" => "",
							"CountryCode" => "",
							"SalesPersonCode" => strval($customer->getSalesPersonCode()),
							"FirstName" => strval($customer->getFirstname()),
							"LastName" => strval($customer->getLastname()),
							"MobilePhone" => strval($customer->getCustomerMobilePhone()),
							"CustomerTitle" => strval($customer->getUserTitle()),
							"CustomerGroup" => strval($customerGroupName)
						];
				}

			}
			$Pagination['pageInfo']['TotalItems'] = $totalProducts;
            $Pagination['pageInfo']['TotalPages'] = $pageSize;
			$userData = [
				"Settings" => [
					"Code" => "200",
					"Message" => "Users data synced successfully",
				],
				"Data" => $userArray,
				"Pagination" => $Pagination
			];
			$ary_response[] = $userData;

        }else{
        	$Pagination['pageInfo'] = array('TotalItems' => 0, 'TotalPages' => 0);
			$userData = [
				"Settings" => [
					"Code" => "400",
					"Message" => "We can't find users matching the selection.",
					"Pagination" => $Pagination
				]
			];
			$ary_response[] = $userData;
        }
        echo $this->jsonHelper->jsonEncode($userData); exit;
	}

	public function getCustomerListOnRoot($timestamp = ""){
		$customerArray = [];
		$collection = $this->customerCheck->getCollection();
		$collection->addAttributeToSelect("*");
		if($timestamp){
			$date = date('Y-m-d H:i:s', $timestamp);
            //$date =  $this->generalHelper->dateConvert($date);
            $collection->addFieldToFilter('updated_at', array('gteq' => $date ));

			// $dateFormat = date('Y-m-d H:i:s', $timestamp);
		 //    $collection->addFieldToFilter('updated_at', array('gt' => $dateFormat));
		}
		$collection->addFieldToFilter('group_id', array('in' => array(1)));
		$collection->load();
		
		if(sizeof($collection) != 0){
			foreach ($collection as $customer) {
			 	$group = $this->groupRepository->getById($customer->getGroupId());
				$customerGroupName = $group->getCode();
				if($customer->getAddresses()){
					foreach ($customer->getAddresses() as $address)
					{
						$street = $address->getStreet();
						if(isset($street[1])){
							$streetLine2 =  $street[1];
						}else{
							$streetLine2 = null;
						}
						if(isset($street[2])){
							$streetLine3 = $street[2];
						}else{
							$streetLine3 = null;
						}

						$customerArray[] = [
								"CustomerId" => strval($customer->getId()),
								"Division" => strval($customer->getErpDivision()),
								"CustomerNumber" => strval($customer->getErpCustomerNumber()),
								"CustomerName" => strval($customer->getName()),
								"Email" => strval($customer->getEmail()),
								"PhoneNumber" => strval($customer->getTelephone()),
								"AddressLine1" => strval($street[0]),
								"AddressLine2" => strval($streetLine2),
								"AddressLine3" => strval($streetLine3),
								"City" => strval($address->getCity()),
								"State" => strval($address->getRegion()),
								"ZipCode" => strval($address->getPostcode()),
								"CountryCode" => strval($address->getCountry()),
								"FirstName" => strval($customer->getFirstname()),
								"LastName" => strval($customer->getLastname()),
								"MobilePhone" => strval($customer->getCustomerMobilePhone())
							];
					}
				}else{
					$customerArray[] = [
							"CustomerId" => strval($customer->getId()),
							"Division" => strval($customer->getErpDivision()),
							"CustomerNumber" => strval($customer->getErpCustomerNumber()),
							"CustomerName" => strval($customer->getName()),
							"Email" => strval($customer->getEmail()),
							"PhoneNumber" => strval($customer->getTelephone()),
							"AddressLine1" => "",
							"AddressLine2" => "",
							"AddressLine3" => "",
							"City" => "",
							"State" => "",
							"ZipCode" => "",
							"CountryCode" => "",
							"FirstName" => strval($customer->getFirstname()),
							"LastName" => strval($customer->getLastname()),
							"MobilePhone" => strval($customer->getCustomerMobilePhone())
						];
				}

			}
			$customersData = [
				"Settings" => [
					"Code" => "200",
					"Message" => "Customers data synced successfully",
				],
				"Data" => $customerArray
			];
			$ary_response[] = $customersData;

        }else{
			$customersData = [
				"Settings" => [
					"Code" => "400",
					"Message" => "We can't find customers matching the selection.",
				]
			];
			$ary_response[] = $customersData;
        }
		$json = \Zend_Json::encode($customersData);
		$customerJsonContent = \Zend_Json::prettyPrint($json);
		return $customerJsonContent;
	}

	public function getUserListOnRoot($timestamp = ""){
		$userArray = [];
		$collection = $this->customerCheck->getCollection();
		$collection->addAttributeToSelect("*");
		if($timestamp){
			$date = date('Y-m-d H:i:s', $timestamp);
           // $date =  $this->generalHelper->dateConvert($date);
            $collection->addFieldToFilter('updated_at', array('gteq' => $date ));

			// $dateFormat = date('Y-m-d H:i:s', $timestamp);
		 //    $collection->addFieldToFilter('updated_at', array('gt' => $dateFormat));
		}
		$collection->addFieldToFilter('group_id', array('in' => array(4,5,6,7,8,9)));
		$collection->load();
		
		if(sizeof($collection) != 0){
			foreach ($collection as $customer) {
			 	$group = $this->groupRepository->getById($customer->getGroupId());
				$customerGroupName = $group->getCode();
				if($customer->getAddresses()){
					foreach ($customer->getAddresses() as $address)
					{
						$street = $address->getStreet();
						if(isset($street[1])){
							$streetLine2 =  $street[1];
						}else{
							$streetLine2 = null;
						}
						if(isset($street[2])){
							$streetLine3 = $street[2];
						}else{
							$streetLine3 = null;
						}

						$userArray[] = [
								"CustomerId" => strval($customer->getId()),
								"Email" => strval($customer->getEmail()),
								"PhoneNumber" => strval($customer->getTelephone()),
								"AddressLine1" => strval($street[0]),
								"AddressLine2" => strval($streetLine2),
								"AddressLine3" => strval($streetLine3),
								"City" => strval($address->getCity()),
								"State" => strval($address->getRegion()),
								"ZipCode" => strval($address->getPostcode()),
								"CountryCode" => strval($address->getCountry()),
								"SalesPersonCode" => strval($customer->getSalesPersonCode()),
								"FirstName" => strval($customer->getFirstname()),
								"LastName" => strval($customer->getLastname()),
								"MobilePhone" => strval($customer->getCustomerMobilePhone()),
								"CustomerTitle" => strval($customer->getUserTitle()),
								"CustomerGroup" => strval($customerGroupName)
							];
					}
				}else{
					$userArray[] = [
							"CustomerId" => strval($customer->getId()),
							"Email" => strval($customer->getEmail()),
							"PhoneNumber" => strval($customer->getTelephone()),
							"AddressLine1" => "",
							"AddressLine2" => "",
							"AddressLine3" => "",
							"City" => "",
							"State" => "",
							"ZipCode" => "",
							"CountryCode" => "",
							"SalesPersonCode" => strval($customer->getSalesPersonCode()),
							"FirstName" => strval($customer->getFirstname()),
							"LastName" => strval($customer->getLastname()),
							"MobilePhone" => strval($customer->getCustomerMobilePhone()),
							"CustomerTitle" => strval($customer->getUserTitle()),
							"CustomerGroup" => strval($customerGroupName)
						];
				}

			}
			$userData = [
				"Settings" => [
					"Code" => "200",
					"Message" => "Users data synced successfully",
				],
				"Data" => $userArray
			];
			$ary_response[] = $userData;

        }else{
			$userData = [
				"Settings" => [
					"Code" => "400",
					"Message" => "We can't find users matching the selection.",
				]
			];
			$ary_response[] = $userData;
        }
		$json = \Zend_Json::encode($userData);
		$userJsonContent = \Zend_Json::prettyPrint($json);
		return $userJsonContent;
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
