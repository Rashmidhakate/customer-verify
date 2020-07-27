<?php
namespace Brainvire\Mobileapi\Api\Cart;

/**
 * Cart information interface
 *
 * @api
 */

interface CartInterface {

    /**
     * @param string $Version
     * @param string $Platform
     * @param string $DeviceId
     * @param mixed $Data
     * @return \Brainvire\Mobileapi\Api\Cart\CartInterface[]
     */
    public function createAppQuote($Version = '', $Platform= '', $DeviceId= '', $Data);

    /**
     * @param string $Version
     * @param string $Platform
     * @param string $DeviceId
     * @param mixed $Data
     * @return \Brainvire\Mobileapi\Api\Cart\CartInterface[]
     */
    public function createAppOrder($Version = '', $Platform= '', $DeviceId= '', $Data);

    /**
     * @param string $Version
     * @param string $Platform
     * @param string $DeviceId
     * @param string $DateTimeStamp
     * @return \Brainvire\Mobileapi\Api\Cart\CartInterface[]
     */
    public function listAppQuote($Version = '', $Platform= '', $DeviceId= '', $DateTimeStamp= '');

    /**
     * @param string $Version
     * @param string $Platform
     * @param string $DeviceId
     * @param mixed $Data
     * @return \Brainvire\Mobileapi\Api\Cart\CartInterface[]
     */
    public function deleteAppQuote($Version = '', $Platform= '', $DeviceId= '', $Data);

    /**
     * @param string $Version
     * @param string $Platform
     * @param string $DeviceId
     * @param string $QuoteId
     * @param string $DateTimeStamp
     * @return \Brainvire\Mobileapi\Api\Cart\CartInterface[]
     */
    public function listAppQuoteItems($Version = '', $Platform= '', $DeviceId= '', $QuoteId, $DateTimeStamp= '');

    /**
     * @param string $Version
     * @param string $Platform
     * @param string $DeviceId
     * @param mixed $Data
     * @return \Brainvire\Mobileapi\Api\Cart\CartInterface[]
     */
    public function deleteAppQuoteItem($Version = '', $Platform= '', $DeviceId= '', $Data);

    /**
     * @param string $Version
     * @param string $Platform
     * @param string $DeviceId
     * @param string $DateTimeStamp
     * @return \Brainvire\Mobileapi\Api\Cart\CartInterface[]
     */
    public function listAppOrders($Version = '', $Platform= '', $DeviceId= '', $DateTimeStamp= '');

    /**
     * @param string $Version
     * @param string $Platform
     * @param string $DeviceId
     * @param string $MagentoOrderId
     * @return \Brainvire\Mobileapi\Api\Cart\CartInterface[]
     */
    public function getOrderDetails($Version = '', $Platform= '', $DeviceId= '', $MagentoOrderId);
}