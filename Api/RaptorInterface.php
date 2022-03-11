<?php
namespace Qisst\Oneclick\Api;
interface RaptorInterface {
    /**
     * GET for Post api
     * @param string $quoteid
     * @return string
     */
    public function returnOrderId($quoteid);

    /**
     * GET for Post api
     * @param string $first_name
     * @param string $last_name
     * @param string $email
     * @param string $email
     * @param string $address_1
     * @param string $address_2
     * @param string $city
     * @param string $state
     * @param string $postcode
     * @param string $country
     * @param string $quantity
     * @param string $total_amount
     * @param string $shipping_total
     * @param string $tax_amount
     * @param string $payment_note
     * @return string
     */
    public function createOrder($first_name, $last_name, $email, $phone, $address_1, $address_2, $city, $state, $postcode, $country, $quantity, $total_amount, $shipping_total, $tax_amount, $payment_note);
    /**
    *
    * @param \Magento\CatalogInventory\Api\Data\StockItemInterface[] $stockItems
    * @return int
    * @throws \Magento\Framework\Exception\NoSuchEntityException
    */
    public function updateStockItems($stockItems);

}
