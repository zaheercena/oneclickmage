<?php

namespace Qisst\Oneclick\Model\Api;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Shipping\Model\Config;

class Deliverymethod extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var Config
     */
    protected $_deliveryModelConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Config               $deliveryModelConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $deliveryModelConfig
    ) {

        $this->_scopeConfig = $scopeConfig;
        $this->_deliveryModelConfig = $deliveryModelConfig;
    }

    public function toOptionArray()
    {
        $deliveryMethods = $this->_deliveryModelConfig->getActiveCarriers();
        $deliveryMethodsArray = array();
        foreach ($deliveryMethods as $shippigCode => $shippingModel) {
            $shippingTitle = $this->_scopeConfig->getValue('carriers/'.$shippigCode.'/title');
            $shippingCost = $this->_scopeConfig->getValue('carriers/'.$shippigCode.'/price');
            $deliveryMethodsArray[$shippigCode] = array(
                'title' => $shippingTitle,
                'cost' => $shippingCost
            );
        }
        return $deliveryMethodsArray;



    }
    public function returnShipMeth($cost)
    {
        $deliveryMethods = $this->_deliveryModelConfig->getActiveCarriers();
        $deliveryMethodsArray = array();
        foreach ($deliveryMethods as $shippigCode => $shippingModel) {
            $shippingTitle = $this->_scopeConfig->getValue('carriers/'.$shippigCode.'/title');
            $shippingCost = $this->_scopeConfig->getValue('carriers/'.$shippigCode.'/price');
            if($cost == $shippingCost){
              return $shippigCode;
            }
        }
        return 'flatrate';
    }

}
