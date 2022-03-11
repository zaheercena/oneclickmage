<?php

namespace Qisst\Oneclick\Model\Api;
use Magento\Sales\Model\Order;

use \Magento\Tax\Model\Calculation\Rate;

class Taxrate extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Rate
     */
    protected $_taxModelConfig;
    protected $order;
    /**
     * @param Rate               $taxModelConfig
     */
    public function __construct(
        Rate $taxModelConfig,
        Order $order
    ) {
        $this->_taxModelConfig = $taxModelConfig;
        $this->order = $order;
    }

    public function toOptionArray()
    {
        $taxRates = $this->_taxModelConfig->getCollection()->getData();
        $taxArray = array();
        foreach ($taxRates as $tax) {
            $taxRateId = $tax['tax_calculation_rate_id'];
            $taxCode = $tax["code"];
            $taxRate = $tax["rate"];
            $taxName = $taxCode.'('.$taxRate.'%)';
            $taxArray[$taxRateId] = $taxName;
        }
        return $taxArray;
    }
    public function taxReturn(){
      $taxRates = $this->_taxModelConfig->getCollection()->getData();
      $taxArray = array();
      $ratelocal = 0;
      foreach ($taxRates as $tax) {
          $taxRateId = $tax['tax_calculation_rate_id'];
          $taxCode = $tax["code"];
          $taxRate = $tax["rate"];
          $taxCountry = $tax["tax_country_id"];
          $taxName = $taxCode.'('.$taxRate.'%)'.$taxCountry;
          $taxArray[$taxRateId] = $taxName;
          if($taxCountry == 'PK'){
            return $taxRate;
          }
      }
      return null;

    }
    public function orderStatus($params){
      $orderId = $params['order_id'];
      $orderState = '';
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
      if($params['status'] == 'CANCELLED'){$orderState = Order::STATE_CANCELED;}
      elseif($params['status'] == 'DEFAULT'){$orderState = Order::STATE_PROCESSING;}
      $order->setState($orderState)->setStatus($orderState);
      $order->addStatusHistoryComment($params['payment_note']);
      $order->save();
      $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
      $baseUrl = $storeManager->getStore()->getBaseUrl();
      $params['link'] = $baseUrl.'qisstpay?orderid='.$params['order_id'];


      // $order = $this->order->load($orderId);
      // $order->setStatus($params['status']);
      // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      // $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
      // $order->addStatusHistoryComment($params['payment_note']);
      // $order->save();
      return json_encode($params);
    }
}
