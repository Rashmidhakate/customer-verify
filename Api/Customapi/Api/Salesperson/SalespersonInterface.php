<?php
namespace Brainvire\Customapi\Api\Salesperson;

/**
 * Customer information interface
 *
 * @api
 */

interface SalespersonInterface {

    /**
     * Get Salesperson information.
     * @api
     * @param string $timestamp
     * @return array
     */
    public function getSalespersonList($timestamp = "");

    /**
     * Get User Salesperson information.
     * @api
     * @param int $userId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUserSalespersonList($userId);
}