<?php
namespace Brainvire\Mobileapi\Api\Customer;

/**
 * Customer information interface
 *
 * @api
 */

interface CustomerInterface {
	/**
	 * Autenticate the customer by email and password
	 * @param string $EmailId
     * @param string $Password
	 * @param string $Platform
	 * @param string $DeviceToken
	 * @param string $Version
	 * @return \Brainvire\Mobileapi\Api\Customer\CustomerInterface[]
	 */
	public function login($EmailId, $Password, $Platform = '', $DeviceToken = '', $Version = '');

	/**
	 * Logout API
	 * @param string $Platform
	 * @param string $DeviceToken
	 * @param string $Version
	 * @return \Brainvire\Mobileapi\Api\Customer\CustomerInterface[]
	 */
	public function logout($Platform = '', $DeviceToken = '', $Version = '');

	/**
	 * Customer profile Information
	 *
	 * @param string $device_type
	 * @param string $device_token
	 * @param string $appVersion
	 *
	 * @return \Brainvire\Mobileapi\Api\Customer\CustomerInterface[]
	 */
	public function getProfile($device_type = '', $device_token = '', $appVersion = '');
}