<?php
namespace Brainvire\Customerterm\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ObserverInterface;

class Methodavailable implements ObserverInterface {
	protected $_logger;

	public function __construct(
		\Magento\Framework\App\State $state, 
		\Psr\Log\LoggerInterface $logger,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Config\Model\ResourceModel\Config $configResourceModel,
		\Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
		\Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Eav\Model\Config $eavConfig,
		\Magento\Customer\Model\CustomerFactory $customerFactory,
		\Magento\Backend\Model\Session\Quote $backendQuoteSession,
		\Magento\Checkout\Model\Cart $_cart
	) {
		$this->_state = $state;
		$this->_logger = $logger;
		$this->customerSession = $customerSession;
		$this->customerRepositoryInterface = $customerRepositoryInterface;
		$this->scopeConfig = $scopeConfig;
		$this->configResourceModel = $configResourceModel;
		$this->_cacheTypeList = $cacheTypeList;
		$this->_cacheFrontendPool = $cacheFrontendPool;
		$this->storeManager = $storeManager;
		$this->eavConfig = $eavConfig;
		$this->customerFactory = $customerFactory;
		$this->backendQuoteSession = $backendQuoteSession;
		$this->_cart = $_cart; 
	}
	/**
	 *
	 * @param \Magento\Framework\Event\Observer $observer
	 * @return void
	 */
	public function execute(\Magento\Framework\Event\Observer $observer) {
		// $attribute = $this->eavConfig->getAttribute('customer', 'customer_net_terms');
		// $options = $attribute->getSource()->getAllOptions();
		// $config = array();
		// foreach($options as $option) {
		//     //foreach($option as $key => $value){
		//         echo "<pre>";print_r($option);
		//     //}
		// }
		// echo "<pre>";
		// print_r($options);
		// $this->_logger->info(print_r($options));
		// echo $this->_state->getAreaCode();
		// echo $this->customerSession->isLoggedIn();

		// echo "<br>";
		$result = $observer->getEvent()->getResult();
		$method_instance = $observer->getEvent()->getMethodInstance();

		//$quote = $observer->getEvent()->getQuote();
		$scopeId = $this->storeManager->getStore()->getStoreId();
		$websiteId = $this->storeManager->getStore()->getWebsiteId();
			$customerFactory = $this->customerFactory->create();
			
			//if($this->_state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML){
				$quote = $this->_cart->getQuote();
				$customerId = $quote->getCustomerId();	
				//echo $customerId;			
				$customer = $customerFactory->load($customerId);
				// echo "<pre>";
				// print_r($method_instance->getCode());
				$storeId = $customer->getStoreId();
				if ($method_instance->getCode() == 'purchaseorder') {
					if ($customer) {
						$netTermAllowed = $customer->getResource()->getAttribute('net_terms_allowed')->getFrontend()->getValue($customer);
						$customerNetTerm = $customer->getResource()->getAttribute('customer_net_terms')->getFrontend()->getValue($customer);
						if ($netTermAllowed == 'Yes' && $customerNetTerm != "No" && $customerNetTerm != "") {
							$this->configResourceModel->saveConfig(
								'payment/purchaseorder/title',
								"Net Terms",
								\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
								$storeId
							);

							$result->setData('is_available', true);
							$types = array('config', 'layout', 'block_html', 'collections', 'reflection', 'db_ddl', 'eav', 'config_integration', 'config_integration_api', 'full_page', 'translate', 'config_webservice');
							foreach ($types as $type) {
								$this->_cacheTypeList->cleanType($type);
							}
							foreach ($this->_cacheFrontendPool as $cacheFrontend) {
								$cacheFrontend->getBackend()->clean();
							}
						}else{
							$result->setData('is_available', false);
							$types = array('config', 'layout', 'block_html', 'collections', 'reflection', 'db_ddl', 'eav', 'config_integration', 'config_integration_api', 'full_page', 'translate', 'config_webservice');
							foreach ($types as $type) {
								$this->_cacheTypeList->cleanType($type);
							}
							foreach ($this->_cacheFrontendPool as $cacheFrontend) {
								$cacheFrontend->getBackend()->clean();
							}
						}
					}
					if($this->_state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML){
						$quote = $observer->getEvent()->getQuote();
						$customerId = $quote->getCustomerId();
						$customer = $customerFactory->load($customerId);
						$storeId = $customer->getStoreId();
						if ($customer) {
							$netTermAllowed = $customer->getResource()->getAttribute('net_terms_allowed')->getFrontend()->getValue($customer);
							$customerNetTerm = $customer->getResource()->getAttribute('customer_net_terms')->getFrontend()->getValue($customer);

							if ($netTermAllowed == 'Yes' && $customerNetTerm != "No" && $customerNetTerm != "") {
								$this->configResourceModel->saveConfig(
									'payment/purchaseorder/title',
									"Net Terms",
									\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
									$storeId
								);

								$result->setData('is_available', true);
								$types = array('config', 'layout', 'block_html', 'collections', 'reflection', 'db_ddl', 'eav', 'config_integration', 'config_integration_api', 'full_page', 'translate', 'config_webservice');
								foreach ($types as $type) {
									$this->_cacheTypeList->cleanType($type);
								}
								foreach ($this->_cacheFrontendPool as $cacheFrontend) {
									$cacheFrontend->getBackend()->clean();
								}
							}else{
								$result->setData('is_available', false);
								$types = array('config', 'layout', 'block_html', 'collections', 'reflection', 'db_ddl', 'eav', 'config_integration', 'config_integration_api', 'full_page', 'translate', 'config_webservice');
								foreach ($types as $type) {
									$this->_cacheTypeList->cleanType($type);
								}
								foreach ($this->_cacheFrontendPool as $cacheFrontend) {
									$cacheFrontend->getBackend()->clean();
								}
							}
						}
					}	
			}
	
		return $result;
	}
}