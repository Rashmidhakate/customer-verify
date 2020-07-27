<?php

namespace Brainvire\Mobileapi\Helper;

use \Magento\Framework\App\Helper\Context;
use \Magento\Store\Model\StoreManagerInterface;

/**
 * Class - Brainvire\Mobileapi\Helper\StoreInformation
 * Module - Brainvire\Mobileapi
 * Description - Get Current Store Information
 */
class StoreInformation extends \Magento\Framework\App\Helper\AbstractHelper {

	protected $_storeManager;
	protected $_currency;

	public function __construct(
		Context $context,
		\Magento\Directory\Model\Currency $currency,
		StoreManagerInterface $storeManager
	) {
		$this->_storeManager = $storeManager;
		$this->_currency = $currency;
		parent::__construct($context);
	}

	/**
	 * Get store
	 * @return  Store Object
	 */
	public function getStore() {
		return $this->_storeManager->getStore();
	}

	/**
	 * Get store identifier
	 * @return  int
	 */
	public function getStoreId() {
		return $this->_storeManager->getStore()->getId();
	}

	/**
	 * Get website identifier
	 * @return string|int|null
	 */
	public function getWebsiteId() {
		return $this->_storeManager->getStore()->getWebsiteId();
	}

	/**
	 * Get Store code
	 * @return string
	 */
	public function getStoreCode() {
		return $this->_storeManager->getStore()->getCode();
	}

	/**
	 * Get Store name
	 * @return string
	 */
	public function getStoreName() {
		return $this->_storeManager->getStore()->getName();
	}

	/**
	 * Get current url for store
	 * @param bool|string $fromStore Include/Exclude from_store parameter from URL
	 * @return string
	 */
	public function getStoreUrl($fromStore = true) {
		return $this->_storeManager->getStore()->getCurrentUrl($fromStore);
	}

	/**
	 * Check if store is active
	 * @return boolean
	 */
	public function isStoreActive() {
		return $this->_storeManager->getStore()->isActive();
	}

	/**
	 * Get base url for store
	 * @return  string
	 */
	public function getBaseUrl() {
		return $this->_storeManager->getStore()->getBaseUrl();
	}

	/**
	 * Get media url for store
	 * @return  string
	 */
	public function getMediaUrl() {
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}

	/**
	 * Retrieve current Store Locale and return store language
	 *
	 * @return string
	 */
	public function getStoreLocale() {
		$om = \Magento\Framework\App\ObjectManager::getInstance();
		$resolver = $om->get('Magento\Framework\Locale\Resolver');
		$storeLocale = $resolver->getLocale();
		$lang = "en";

		return $lang;
	}

	/**
	 * Retrieve store id based on the store code.
	 *
	 * @return string
	 */
	public function getStoreInfo($lang) {
		$om = \Magento\Framework\App\ObjectManager::getInstance();
		$manager = $om->get('Magento\Store\Model\StoreManagerInterface');
		$storeInfo = $manager->getStore($lang);
		return $storeInfo;
	}

	/**
	 * Return english store details
	 *
	 * @return array
	 */
	public function englishStore() {
		$storeDetails = $this->storeInfo();
		foreach ($storeDetails as $key => $store) {
			$lang = "en";
			if ($lang == "en") {
				$storeIds[] = $key;
			}
		}
		return $storeIds;
	}

	/**
	 * Get all store codes
	 *
	 * @return array
	 */
	public function storeInfo() {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->create("\Magento\Store\Model\StoreManagerInterface");
		$stores = $storeManager->getStores(true, false);
		foreach ($stores as $store) {
			$storeDetails[$store->getId()] = $store->getCode();
		}

		return $storeDetails;
	}

	/**
	 * Get store base currency code
	 *
	 * @return string
	 */
	public function getBaseCurrencyCode() {
		return $this->_storeManager->getStore()->getBaseCurrencyCode();
	}

	/**
	 * Get current store currency code
	 *
	 * @return string
	 */
	public function getCurrentCurrencyCode() {
		return $this->_storeManager->getStore()->getCurrentCurrencyCode();
	}

	/**
	 * Get default store currency code
	 *
	 * @return string
	 */
	public function getDefaultCurrencyCode() {
		return $this->_storeManager->getStore()->getDefaultCurrencyCode();
	}

	/**
	 * Get allowed store currency codes
	 *
	 * If base currency is not allowed in current website config scope,
	 * then it can be disabled with $skipBaseNotAllowed
	 *
	 * @param bool $skipBaseNotAllowed
	 * @return array
	 */
	public function getAvailableCurrencyCodes($skipBaseNotAllowed = false) {
		return $this->_storeManager->getStore()->getAvailableCurrencyCodes($skipBaseNotAllowed);
	}

	/**
	 * Get array of installed currencies for the scope
	 *
	 * @return array
	 */
	public function getAllowedCurrencies() {
		return $this->_storeManager->getStore()->getAllowedCurrencies();
	}

	/**
	 * Get current currency rate
	 *
	 * @return float
	 */
	public function getCurrentCurrencyRate() {
		return $this->_storeManager->getStore()->getCurrentCurrencyRate();
	}

	/**
	 * Get currency symbol for current locale and currency code
	 *
	 * @return string
	 */
	public function getCurrentCurrencySymbol() {
		return $this->_currency->getCurrencySymbol();
	}

	public function setCurrentStore($storeId) {
		$this->_storeManager->setCurrentStore($storeId);
	}
}
?>
