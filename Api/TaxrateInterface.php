<?php
namespace Qisst\Oneclick\Api;
interface TaxrateInterface {

    /**
     * GET for Post api
     * @return string
     */
    public function taxReturn();
    /**
     * GET for Post api
     * @param mixed $params
     * @return string
     */
    public function orderStatus($params);
}
