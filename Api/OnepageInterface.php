<?php
namespace Qisst\Oneclick\Api;
interface OnepageInterface {

    /**
     * GET for Post api
     * @param mixed $params
     * @return string
     */
    public function placeOrder($params);

    /**
     * GET for Post api
     * @return string
     */
    public function shippingMethodsList();

}
