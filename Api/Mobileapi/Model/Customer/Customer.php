<?php

namespace Brainvire\Mobileapi\Model\Customer;

use Brainvire\Mobileapi\Api\Customer\CustomerInterface;
//use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Webapi\Model\Cache\Type\Webapi as WebapiCache;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;

/**
 * Defines the implementaiton class of the \Brainvire\Mobileapi\Api\CustomerInterface
 */
class Customer extends \Magento\Framework\Model\AbstractModel implements CustomerInterface {

	const CACHE_ID = 'customer_webapi';
	const PRODUCT_LIMIT = 20;

	/**
	 *
	 * @var WebapiCache
	 */
	protected $cache;
	protected $storeManager;
	// protected $encrypt;
	protected $customerRepositoryInterface;
	protected $accountManagementInterface;
	protected $subscriberFactory;
	protected $subscriber;
	protected $customerSession;
	protected $storeInformation;
	protected $customerCheck;
	protected $_filesystem;
	protected $_wishlistCollectionFactory;

	/**
	 *
	 * @param WebapiCache $cache
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param \Magento\Framework\Encryption\EncryptorInterface $encryt
	 * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
	 * @param \Magento\Customer\Api\AccountManagementInterface $customerAccount
	 * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
	 * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
	 * @param \Magento\Newsletter\Model\Subscriber $subscriber
	 */
	public function __construct(
		WebapiCache $cache,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
		\Magento\Customer\Api\AccountManagementInterface $customerAccount,
		\Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
		\Magento\Newsletter\Model\Subscriber $subscriber,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Customer\Model\Customer $customerCheck,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		// \Brainvire\Mobileapi\Helper\StoreInformation $StoreInformation,
		\Magento\Customer\Model\CustomerFactory $customerFactory,
		\Magento\Sales\Model\Order $orderModel,
		\Magento\Catalog\Model\ProductRepository $productModel,
		\Magento\Customer\Model\ResourceModel\CustomerFactory $customerResourceFactory,
		// \Brainvire\Mobileapi\Model\AuthtokenFactory $authtoken,
		\Brainvire\Mobileapi\Helper\Data $generalHelper,
		\Magento\Framework\App\ResourceConnection $resourceConnection,
		\Magento\Integration\Model\Oauth\TokenFactory $tokenModelFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
		\Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
		\Magento\Framework\Pricing\Helper\Data $pricingHelper,
		\Magento\Sales\Api\Data\OrderInterface $order,
		\Magento\Customer\Model\Address $address,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Framework\App\RequestInterface $request,
		\Magento\Wishlist\Model\WishlistFactory $wishlist,
		\Magento\Catalog\Model\ProductFactory $productFactory,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
		\Magento\Wishlist\Model\Item $wishlistItem,
		\Magento\Wishlist\Model\Wishlist $wishlistProvider,
		\Magento\Framework\Event\ManagerInterface $eventManager,
		\Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $wishlistCollectionFactory,
		\Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
		\Magento\Customer\Model\Address\Config $addressConfig,
		\Magento\Customer\Model\Address\Mapper $addressMapper,
		\Magento\Directory\Api\CountryInformationAcquirerInterface $country,
		TokenCollectionFactory $tokenModelCollectionFactory,
		\Magento\Customer\Api\GroupRepositoryInterface $groupRepository
	) {
		$this->cache = $cache;
		// $this->storeInformation = $StoreInformation;
		$this->customerRepositoryInterface = $customerRepositoryInterface;
		$this->accountManagementInterface = $customerAccount;
		$this->subscriberFactory = $subscriberFactory;
		$this->subscriber = $subscriber;
		$this->customerSession = $customerSession;
		$this->customerCheck = $customerCheck;
		$this->orderCollection = $orderCollectionFactory;
		$this->customerFactory = $customerFactory;
		$this->orderModel = $orderModel;
		$this->productModel = $productModel;
		$this->customerResourceFactory = $customerResourceFactory;
		$this->tokenModelCollectionFactory = $tokenModelCollectionFactory;
		// $this->_customerToken = $authtoken;
		$this->tokenModelFactory = $tokenModelFactory;
		$this->resourceConnection = $resourceConnection;
		$this->generalHelper = $generalHelper;
		$this->_storeManager = $storeManager;
		$this->cartManagementInterface = $cartManagementInterface;
		$this->quoteRepository = $quoteRepository;
		$this->pricingHelper = $pricingHelper;
		$this->order = $order;
		$this->_address = $address;
		$this->addressRepository = $addressRepository;
		$this->_addressConfig = $addressConfig;
		$this->addressMapper = $addressMapper;
		$this->country = $country;
		$this->_filesystem = $filesystem;
		$this->timezone = $timezone;
		$this->_wishlist = $wishlist;
		$this->_productFactory = $productFactory;
		$this->request = $request;
		$this->_wishlistItem = $wishlistItem;
		$this->_wishlistProvider = $wishlistProvider;
		$this->eventManager = $eventManager;
		$this->_wishlistCollectionFactory = $wishlistCollectionFactory;
		$this->groupRepository = $groupRepository;
	}

	/**
	 * Login API
	 *
	 * @param string $EmailId
	 * @param string $Password
	 * @param string $Platform
	 * @param string $DeviceToken
	 * @param string $Version
	 * @return \Brainvire\Mobileapi\Api\Customer\CustomerprofileInterface
	 */
	public function login($EmailId, $Password, $Platform = '', $DeviceToken = '', $Version = '') {

		$email = $EmailId;
		$password = $Password;
		$device_token = ($DeviceToken) ? $DeviceToken : '';
		$device_type = ($Platform) ? $Platform : '';
		$appVersion = ($Version) ? $Version : '';
		
		$data = array();
		try {
			$websiteId = 1;
			$customerObj = $this->customerCheck->setWebsiteId($websiteId)->loadByEmail($email);
			if (count($customerObj->getData())) {
				$ch = curl_init();
				$host = $this->_storeManager->getStore()->getBaseUrl();
				curl_setopt($ch, CURLOPT_URL, $host . 'rest/V1/integration/customer/token');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$postData['username'] = $email;
				$postData['password'] = $password;
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen(json_encode($postData)),
				)
			);
				curl_setopt($ch, CURLOPT_POST, count($postData));
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
				$token = curl_exec($ch);
				curl_close($ch);
				$token = json_decode($token, 1);
				if (is_array($token) && array_key_exists('message', $token)) {
                    // $message = "Please enter valid password.";
					$error = (object)array();
					$result = array(
		                "Settings" => ["Code" => "400","Message" => strval($token['message'])]
	                );  
					echo json_encode($result); exit;
				}
			}
			$customerObj = $this->customerCheck->setWebsiteId($websiteId)->loadByEmail($email);
			$loginRestrictGroups = $this->generalHelper->restrictLoginGroups();
			if (in_array($customerObj->getGroupId(), $loginRestrictGroups))
			{
				$message = "You are restricted to login";
				$result = array(
	                "Settings" => ["Code" => "400","Message" => strval($message)]
                );  
				echo json_encode($result); exit;
			}
			
			if ($customerObj->getId()) {
				if ($this->customerCheck->getConfirmation() && $this->customerCheck->isConfirmationRequired()) {
					$error = (object) array();
					$message = "This account isn't confirmed. Verify and try again.";
					
					$result = array(
	                    "Settings" => ["Code" => "400","Message" => strval($message)],
	                    "Data" => $errors
	                );  
					echo json_encode($result); exit;
				}
				if (!$this->customerCheck->validatePassword($password)) {
					$message = "Invalid login or password.";
					$error = (object) array();

					$result = array(
	                    "Settings" => ["Code" => "400","Message" => strval($message)],
	                    "Data" => $errors
	                );  
					echo json_encode($result); exit;
				}
				if ($this->customerCheck->authenticate($email, $password)) {
					$customer = $this->customerRepositoryInterface->get($email, $websiteId);
					$firstName = strval($customer->getFirstname());
					$lastName = strval($customer->getLastname());
					$fullName = "";

						// Get decrypted customer id
					$customer_id = $customer->getId();
					if ($lastName == '.' || $lastName == 'lastname') {
						$fullName = $firstName;
					} else {
						$fullName = $firstName . " " . $lastName;
					}
					$status = 'Success';
					$platform = strtolower($device_type);
					$returnData = $this->loginCustomer($device_token, $platform, $customer->getId());
					$address = $this->_address->load($customer->getDefaultBilling());
					if ($customer->getDefaultBilling()){
						$telephone = $address->getTelephone();
					}elseif($customer->getCustomAttribute('customer_mobile_number')){
						$telephone = $customer->getCustomAttribute('customer_mobile_number')->getValue();
					}else{
						$telephone = '';
					}

					$group = $this->groupRepository->getById($customerObj->getData('group_id'));
					
					$serverTimeZone = ($this->generalHelper->getServerTimeZone()) ? $this->generalHelper->getServerTimeZone() : 'America/Chicago';
					
					$resultData['UserData'] = array(
						'CustomerId' => strval($customer_id),
						'CustomerType' => strval($group->getCode()),
						'MobileNumber' => strval($telephone),
						'Email' => strval($customer->getEmail()),
						'CustomerName' => strval($fullName),
						'AccessToken' => strval($token),
						'ServerTimeZone' => $serverTimeZone
					);

					$message = "Welcome to 4SGM";

					$result = array(
	                    "Settings" => ["Code" => "200","Message" => strval($message)],
	                    "Data" => $resultData
	                );  
					echo json_encode($result); exit;
				} else {
					$errors = array(
						'invalid_details' => __('Invalid login or password.'),
						'account_locked' => __('The account is locked.'),
						'account_confirmation' => __('This account is not confirmed.'),
					);
					$message = "Invalid login or password.";

					$result = array(
	                    "Settings" => ["Code" => "400","Message" => strval($message)],
	                    "Data" => $errors
	                );  
					echo json_encode($result); exit;
				}
			}else {
				$error = (object) array();
				$message = "The email which you have entered is not registerd";

				$result = array(
	                    "Settings" => ["Code" => "400","Message" => strval($message)],
	                    "Data" => $errors
	                );  
				echo json_encode($result); exit;
			}
		} catch (Exception $e) {
			$error = (object) array();
			$message = "Invalid login or password.";

			$result = array(
	                    "Settings" => ["Code" => "400","Message" => strval($message)],
	                    "Data" => $errors
	                );  
			echo json_encode($result); exit;
		}
	}

	/**
	 * Customer Logout
	 *
	 * @param string $Platform
	 * @param string $DeviceToken
	 * @param string $Version
	 *
	 * @return \Brainvire\Mobileapi\Api\Customer\CustomerprofileInterface
	 */
	public function logout($Platform = '', $DeviceToken = '', $Version = '') {
        // Get website information
        $device_token = ($DeviceToken) ? $DeviceToken : '';
		$device_type = ($Platform) ? $Platform : '';
		$appVersion = ($Version) ? $Version : '';
        $websiteId = 1;
        $platform = strtolower($device_type);
        $customer = $this->generalHelper->customerLoginCheck();
        $token = substr($this->request->getHeader('Authorization'), 7);
        $tokenCollection = $this->tokenModelCollectionFactory->create()->addFieldToFilter('customer_id', array('eq'=>$customer['id']))->addFieldToFilter('token', array('eq'=>$token));
        
        if ($customer['id'] && ($tokenCollection->getSize() > 0)) {

            $deviceIds = $this->generalHelper->_checkLoginStatus($device_token, $customer['id'], $platform);
            if (empty($deviceIds) && $device_token != '') {
                 $message = 'Customer is not logged in.';
                 $error = (object) array();
                 header('x', true, 401);

                $result = array(
	                "Settings" => ["Code" => "400","Message" => strval($message)]
	            );  
				echo json_encode($result); exit;

            } else {
                $id = implode($deviceIds, ',');
                $connection = $this->resourceConnection->getConnection();
                $tableName = $this->resourceConnection->getTableName('bv_auth_mobile_token');
                $sql = "Delete FROM " . $tableName . " WHERE id = '" . $id . "'";
                $result = $connection->query($sql);
                foreach ($tokenCollection as $token) {
                    $token->setRevoked(1)->save();
                }
                $message = 'Customer was logged out successfully.';
                $error = (object) array();

                $result = array(
	                "Settings" => ["Code" => "200","Message" => strval($message)]
	            );  
				echo json_encode($result); exit;
            }
        } else {
            $message = "Customer with token=" . $token . " was not found.";
            $error = (object) array();
            header('x', true, 401);

            $result = array(
                "Settings" => ["Code" => "400","Message" => strval($message)]
            );  
			echo json_encode($result); exit;
        }
    }

	/**
	 * Customer profile Information
	 *
	 * @param string $device_type
	 * @param string $device_token
	 * @param string $appVersion
	 *
	 * @return \Brainvire\Mobileapi\Api\Customer\CustomerprofileInterface
	 */
	public function getProfile($device_type = '', $device_token = '', $appVersion = '') {
		$websiteId = 1;
		$platform = strtolower($device_type);
		$params = $this->request->getParams(); 
		$customer = $this->generalHelper->customerLoginCheck();            
		$customer_id = $customer['id'];
		$customer = $this->customerCheck->setWebsiteId($websiteId)->load($customer_id);
		$platform = strtolower($device_type);
		$deviceIds = $this->generalHelper->_checkLoginStatus($device_token, $customer_id, $platform);
		if (empty($deviceIds) && $device_token != '') {
			$message = 'Your session has been expired. Please login again. Thank you.';
			header('x', true, 401);
			$data = array('statusCode' => strval(401), 'message' => strval($message));
			echo json_encode($data);
			exit;
		}
		if ($customer->getId()) {
			$output = array();
			$subscriber = $this->subscriber->loadByEmail($customer->getEmail());
			$status = $subscriber->isSubscribed();
			$firstName = strval($customer->getFirstname());
			$lastName = strval($customer->getLastname());

			$defaultBilling = $customer->getDefaultBilling();
			if ($defaultBilling) {
				$address = $this->addressRepository->getById($defaultBilling);
				$renderer = $this->_addressConfig->getFormatByCode('oneline')->getRenderer();
				$fullAddress = $renderer->renderArray($this->addressMapper->toFlatArray($address));
				$formattedFullAddress = ($address->getCustomAttribute('apart_suit')) ? $address->getFirstname() . ' ' . $address->getLastname() . ' \n' . $fullAddress . ' \n' . $address->getCustomAttribute('apart_suit')->getValue() : $address->getFirstname() . ' ' . $address->getLastname() . ' \n' . $fullAddress;
				$formattedAddress = str_replace("\\n", "\n", $formattedFullAddress);
			} else {
				$formattedAddress = "";
			}

			($lastName == 'lastname' || $lastName == '.') ? $fullName = $firstName : $fullName = $firstName . " " . $lastName;
			if ($customer->getCreditCard() == 1) {
				$credit_card = 'true';
			} else {
				$credit_card = 'false';
			}
			if ($customer->getPushNotify() == 1) {
				$push_notification = 'true';
			} else {
				$push_notification = 'false';
			}

			$customerInfo['firstname'] = strval($customer->getFirstname());
			$customerInfo['lastname'] = strval($customer->getLastname());
                    //$customerInfo['gender'] = strval($gender);                 
			$address = $this->_address->load($customer->getDefaultBilling());
			$customerInfo['telephone'] = ($address->getTelephone()) ? $address->getTelephone() : '';
			$customerInfo['email'] = strval($customer->getEmail());
			$customerInfo['companyname'] = ($address->getCompany()) ? $address->getCompany() : '';  
			$customerInfo['Address'] = ($formattedAddress) ? $formattedAddress : '';
			$countryName = ($address->getCountry()) ? $this->country->getCountryInfo($address->getCountry()) : '';
			$customerInfo['country'] = ($countryName) ? $countryName->getFullNameLocale() : '';
			$customerInfo['city'] = ($address->getCity()) ? $address->getCity() : '';
                    //$customerInfo['push_notification'] = $push_notification;	 
			
			$customerInfo['newsletter_subscription'] = ($status == true) ? 'true' : 'false';
			$msgsuccess = "success";
			$data = array('statusCode' => strval(200), 'message' => strval($msgsuccess), 'data' => $customerInfo);
			echo json_encode($data);exit;
		} else {
			$error = (object) array();
			$message = 'Invalid customer.';
			$data = array('statusCode' => strval(400), 'message' => strval($message), 'data' => $error);
			echo json_encode($data);exit;
		}
	}
	
	public function loginCustomer($device_token, $device_type, $customerId) {
		try {
			$deviceIds = $this->generalHelper->_checkDeviceIds($device_token, $customerId, $device_type);
			if (empty($deviceIds) && $device_token != '') {
				$connection = $this->resourceConnection->getConnection();
				$tableName = $this->resourceConnection->getTableName('bv_auth_mobile_token');
				$data = array('customer_id' => $customerId,
					'device_token' => $device_token,
					'platform' => $device_type,
					'login_status' => $this->generalHelper->loginSuccess(),
				);
				$connection->insertOnDuplicate($tableName, $data);
			}
			$status = $this->generalHelper->loginSuccess();
			if (!empty($deviceIds)) {
				$connection = $this->resourceConnection->getConnection();
				$tableName = $this->resourceConnection->getTableName('bv_auth_mobile_token');
				$updateStatus = "UPDATE `bv_auth_mobile_token` SET `login_status`= '" . $status . "' WHERE customer_id = '" . $customerId . "' AND platform LIKE '" . $device_type . "' AND device_token = '" . $device_token . "'";
				$connection->query($updateStatus);
			}
		} catch (Exception $e) {
			$error = (object) array();
			$message = "Something went wrong.";
			$data = array('statusCode' => strval('400'), 'message' => strval($message), 'data' => $error);
			echo json_encode($data);
			exit;
		}
	}

	public function LoginCustomerDetails($device_token, $device_type, $customerId) {
		try {
			$deviceIds = $this->generalHelper->_checkDeviceIds($device_token, $customerId, $device_type);
			if (empty($deviceIds) && $device_token != '') {
				$connection = $this->resourceConnection->getConnection();
				$tableName = $this->resourceConnection->getTableName('bv_auth_mobile_token');
				$data = array('customer_id' => $customerId,
					'device_token' => $device_token,
					'platform' => $device_type,
					'login_status' => $this->generalHelper->loginSuccess(),
				);
				$connection->insertOnDuplicate($tableName, $data);
			}
			$status = $this->generalHelper->loginSuccess();
			if (!empty($deviceIds)) {
				$connection = $this->resourceConnection->getConnection();
				$tableName = $this->resourceConnection->getTableName('bv_auth_mobile_token');
				$updateStatus = "UPDATE `bv_auth_mobile_token` SET `login_status`= '" . $status . "' WHERE customer_id = '" . $customerId . "' AND platform LIKE '" . $device_type . "' AND device_token = '" . $device_token . "'";
				$connection->query($updateStatus);
			}
		} catch (Exception $e) {
			$error = (object) array();
			$message = "Something went wrong.";
			$data = array('statusCode' => strval('400'), 'message' => strval($message), 'data' => $error);
			echo json_encode($data);
			exit;
		}
	}

	protected function getQuoteId($customer) {
		$quoteId = 0;
		if ((!is_null($customer))) {
			try {
				$quote = $this->cartManagementInterface->getCartForCustomer($customer->getId());
				$quoteId = $quote->getId();
				if (!$quote->getIsActive()) {
					$quoteId = $this->cartManagementInterface->createEmptyCartForCustomer($customer->getId());
				}
				$quote = $this->quoteRepository->get($quoteId);
				$quote->setStoreId(1)->save();
			} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
				$quoteId = $this->cartManagementInterface->createEmptyCartForCustomer($customer->getId());
				$quote = $this->quoteRepository->get($quoteId);
				$quote->setStoreId(1)->save();
				// if ($quoteId) {
				// 	$customer->setQuoteId($quoteId)->save();
				// }
			}
		}
		return $quoteId;
	}

	public function customerAutoLogin($customerId) {
		// Customer Session. If loged in then logout.
		$customersession = $this->customerSession;
		if ($customersession->isLoggedIn()) {
			$customersession->logout();
		}

		if (!empty($customerId)) {
			/* @var $customerAccountManagement \Magento\Customer\Api\AccountManagementInterface */
			$customerAccountManagement = $this->accountManagementInterface;
			try {
				/* @var $customer \Magento\Customer\Api\Data\CustomerInterface */
				$this->_storeManager->setCurrentStore('default');
				$websiteId = $this->_storeManager->getStore()->getWebsiteId();

				/* @var $_customer \Magento\Customer\Model\Customer */
				$_customer = $this->customerCheck->setWebsiteId($websiteId)->load($customerId);
				$customersession->setCustomerAsLoggedIn($_customer);
				//$customersession->setCustomerDataAsLoggedIn($_customer);
				$customersession->regenerateId();
				return true;
			} catch (\Exception $e) {

			}
		}
		return false;
	}

	public function checkIsLogin($customerId) {
		if ($this->customerSession->isLoggedIn()) {
			$sessionCustomerId = $this->customerSession->getCustomer()->getId();
			if ($sessionCustomerId == $customerId) {
				//$this->customerAutoLogin($customerId);
				return true;
			}
		} else {
			$this->customerAutoLogin($customerId);
		}
		return false;
	}

	public function getCurrencyCode($storeId) {
		$this->_storeManager->setCurrentStore($storeId);
		$currencyCode = $this->_storeManager->getStore()->getCurrentCurrency()
		->getCode();

		return $currencyCode;
	}

}
