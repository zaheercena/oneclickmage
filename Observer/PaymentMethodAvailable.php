<?php
namespace Qisst\Oneclick\Observer;
use Magento\Framework\Event\ObserverInterface;
class PaymentMethodAvailable implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // you can replace "checkmo" with your required payment method code
        //if($observer->getEvent()->getMethodInstance()->getCode()=="qppayment"){
            // $checkResult = $observer->getEvent()->getResult();
            // $checkResult->setData('is_available', false);
        //}
    }
}
