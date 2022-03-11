<?php
namespace Qisst\Oneclick\Api;
interface ShippingInterface {

    /**
     * GET for Post api
     * @return string
     */
    public function toOptionArray();
    /**
     * GET for Post api
     * @param int $cost
     * @return int
     */
    public function returnShipMeth($cost);

}
