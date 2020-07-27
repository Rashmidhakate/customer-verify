<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Brainvire\Customapi\Api\Product;

/**
 * product information interface
 *
 * @api
 * @since 100.0.2
 */
interface ProductInterface
{
    /**
     * Get product information.
     *
     * @param string $timestamp
     * @return array
     */
    public function getProductList($timestamp = "");

    /**
     * Get product quantity and price information.
     *
     * @param int $productId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function GetProductQtyAndPrice($productId);

    /**
     * Set product quantity and price information.
     *
     * @param string $SKU
     * @param string $StandardPrice
     * @param string $MinPrice
     * @param string $SpecialPrice
     * @param string $QuantityOnHand
     * @param string $QtyOnSO
     * @param string $QTYRO
     * @param string $QtyOnPO
     * @param string $Sellable
     * @param string $QTYLimit
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function SetProductQtyAndPrice($SKU, $StandardPrice='', $MinPrice='', $SpecialPrice='', $QuantityOnHand='', $QtyOnSO='', $QtyOnPO='', $QTYRO='', $Sellable='', $QTYLimit='');
}
