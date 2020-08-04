<?php

namespace Brainvire\Customerterm\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class Customertermconfigprovider implements ConfigProviderInterface
{
	protected $_logger;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Config\Model\ResourceModel\Config $configResourceModel,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig
    ){
        $this->_logger = $logger;
        $this->customerSession = $customerSession;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->scopeConfig = $scopeConfig;
        $this->configResourceModel = $configResourceModel;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->storeManager = $storeManager;
        $this->eavConfig = $eavConfig;
    }
	public function getConfig()
	{
		$config = array();
		if($this->customerSession->isLoggedIn()) {
		    $customer = $this->customerSession->getCustomer();
		    $customerNetTerm = $customer->getResource()->getAttribute('customer_net_terms')->getFrontend()->getValue($customer);
		    $config['custom_net_term'] = "Net Terms";
		}
		return $config;
	}
}