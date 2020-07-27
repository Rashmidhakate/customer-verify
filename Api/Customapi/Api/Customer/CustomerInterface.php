<?php
namespace Brainvire\Customapi\Api\Customer;

/**
 * Customer information interface
 *
 * @api
 */

interface CustomerInterface {

	/**
     * Get Customer information.
     *
     * @param string $timestamp
     * @return array
     */
    public function getCustomerList($timestamp = "");
    /**
     * Get User information.
     *
     * @param string $timestamp
     * @return array
     */
    public function getUserList($timestamp = "");
}