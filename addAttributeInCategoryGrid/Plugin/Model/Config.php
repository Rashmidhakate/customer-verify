<?php
namespace Brainvire\Coreoverride\Plugin\Model;

class Config
{

	/**
	* Adding custom options and changing labels
	*
	* @param \Magento\Catalog\Model\Config $catalogConfig
	* @param [] $options
	* @return []
	*/
	public function afterGetAttributeUsedForSortByArray(\Magento\Catalog\Model\Config $catalogConfig, $options)
	{

		//Remove specific default sorting options(if require)
		unset($options['position']);
		unset($options['name']);
		unset($options['price']);

		//Changing label(if require)
		$newOption['price_desc'] = __('Highest to Lowest');
		$newOption['price_asc'] = __('Lowest to Highest');
		$newOption['bestseller'] = __('Best Sellers');
		$newOption['recently_added'] = __('Recently Added');

		//Merge default sorting options with new options
		$options = array_merge($newOption, $options);

		return $options;
	 }
}