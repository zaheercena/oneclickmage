<?php
namespace Qisst\Oneclick\Model\Api;
use Qisst\Oneclick\Api\RaptorInterface;

class Raptor implements RaptorInterface
{
  public function __construct(
    \Magento\Config\Model\ResourceModel\Config $config,
    \Magento\Framework\App\Helper\Context $context,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Catalog\Model\ProductFactory $productFactory,
            \Magento\Quote\Model\QuoteManagement $quoteManagement,
            \Magento\Customer\Model\CustomerFactory $customerFactory,
            \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
            \Magento\Sales\Model\Service\OrderService $orderService,
            \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
            \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
            \Magento\Quote\Model\Quote\Address\Rate $shippingRate
    )
     {
       $this->config = $config;
       //parent::__construct($context);
       $this->_storeManager = $storeManager;
            $this->_productFactory = $productFactory;
            $this->quoteManagement = $quoteManagement;
            $this->customerFactory = $customerFactory;
            $this->customerRepository = $customerRepository;
            $this->orderService = $orderService;
            $this->cartRepositoryInterface = $cartRepositoryInterface;
            $this->cartManagementInterface = $cartManagementInterface;
            $this->shippingRate = $shippingRate;
     }



    /* This is Validator Function Only Start */
    public function returnOrderId($quoteid) {
      $entityDesired = $quoteid;
      if(!$quoteid){$age = array("orderId"=>null);$response[0] = $age;return $response;}
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        $shippingAddress = $cart->getQuote()->getShippingAddress();
        $cartId = $cart->getQuote()->getId();
        $this->_resources = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        $connection= $this->_resources->getConnection();
        $parentTable = $connection->getTableName('sales_order');
        $subSeqTable = $connection->getTableName('sales_order_grid');
        $customOrderGet = "SELECT `entity_id` FROM `".$parentTable."` where quote_id =".$entityDesired ;
        $customOrderNo = $connection->fetchAll($customOrderGet);
        if($customOrderNo){
          $customOrderReturn = $customOrderNo[0]['entity_id'];
          $sqlDetail = "SELECT `increment_id` FROM `".$subSeqTable."` where entity_id =".$customOrderReturn;
          $orderno = $connection->fetchAll($sqlDetail);
          $orderst = $orderno[0]['increment_id'];
        }else{$age = array("orderId"=>null);$response[0] = $age;return $response;}
      if($orderst){$age = array("orderId"=>$orderst);$response[0] = $age;return $response;}else{return null;}
    }

    /**
    * @param array $orderData
    * @return int $orderId
    *
    */
    //public function createOrder($orderfname, $orderlname, $orderemail, $orderphone, $orderaddress1, $orderaddress2, $ordercity, $orderstate, $orderpostcode, $ordercountry, $orderquantiry, $orderprice, $ordershipping, $ordertax, $ordernote){

    public function createOrder($first_name, $last_name, $email, $phone, $address_1, $address_2, $city, $state, $postcode, $country, $quantity, $total_amount, $shipping_total, $tax_amount, $payment_note){
      //init the store id and website id @todo pass from array
      $orderData=[
     'currency_id'  => 'PKR',
     'email'        => $orderemail, //buyer email id
     'shipping_address' =>[
            'firstname'    => $first_name, //address Details
            'lastname'     => $last_name,
            'street' => $address_1." ".$address_2,
            'city' => $city,
            'country_id' => $country,
            'region' => $state,
            'postcode' => $postcode,
            'telephone' => $phone,
            'fax' => '',
            'save_in_address_book' => 1
                 ],
   'items'=> [ //array of product which order you want to create
              ['product_id'=>'1','qty'=>$quantity]
            ]
];
                  $store = $this->_storeManager->getStore();
                  $websiteId = $this->_storeManager->getStore()->getWebsiteId();

                  //init the customer
                  $customer=$this->customerFactory->create();
                  $customer->setWebsiteId($websiteId);
                  $customer->loadByEmail($orderData['email']);// load customet by email address

                  //check the customer
                  if(!$customer->getEntityId()){

                      //If not available then create this customer
                      $customer->setWebsiteId($websiteId)
                          ->setStore($store)
                          ->setFirstname($orderData['shipping_address']['firstname'])
                          ->setLastname($orderData['shipping_address']['lastname'])
                          ->setEmail($orderData['email'])
                          ->setPassword($orderData['email']);

                      $customer->save();
                  }

                  //init the quote
                  $cart_id = $this->cartManagementInterface->createEmptyCart();
                  $cart = $this->cartRepositoryInterface->get($cart_id);

                  $cart->setStore($store);

                  // if you have already had the buyer id, you can load customer directly
                  $customer= $this->customerRepository->getById($customer->getEntityId());
                  $cart->setCurrency();
                  $cart->assignCustomer($customer); //Assign quote to customer

                  //add items in quote
                  foreach($orderData['items'] as $item){
                      $product = $this->_productFactory->create()->load($item['product_id']);
                      $cart->addProduct(
                          $product,
                          intval($item['qty'])
                      );
                  }

                  //Set Address to quote @todo add section in order data for seperate billing and handle it
                  $cart->getBillingAddress()->addData($orderData['shipping_address']);
                  $cart->getShippingAddress()->addData($orderData['shipping_address']);

                  // Collect Rates, Set Shipping & Payment Method
                  $this->shippingRate
                      ->setCode('freeshipping_freeshipping')
                      ->getPrice(1);

                  $shippingAddress = $cart->getShippingAddress();

                  //@todo set in order data
                  $shippingAddress->setCollectShippingRates(true)
                      ->collectShippingRates()
                      ->setShippingMethod('flatrate_flatrate'); //shipping method
                  //$cart->getShippingAddress(0);

                  $cart->setPaymentMethod('checkmo'); //payment method

                  //@todo insert a variable to affect the invetory
                  $cart->setInventoryProcessed(false);

                  // Set sales order payment
                  $cart->getPayment()->importData(['method' => 'checkmo']);

                  // Collect total and save
                  $cart->collectTotals();



                  // Submit the quote and create the order
                  // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                  // $order = $objectManager->create('\Magento\Sales\Model\Order')->load(53);
                  // $order->addStatusHistoryComment('This comment is programatically added to last order in this Magento setup');
                  // $order->save();


                  $cart->save();
                  //$cart = $this->cartRepositoryInterface->get($cart->getId());
                  //return $cart->getId();

                  $order_id = $this->cartManagementInterface->placeOrder($cart->getId());
                  return $cart_id;
    }
    /**
     *
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterface[] $stockItems
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateStockItems($stockItems)
    {
        //$json = json_decode(json_encode($stockItems), true);
        echo $stockItems[1]['firstname'];
        // foreach ($stockItems as $stockItem){
        //     //echo $stockItem['stockItemss'][0];
        // }
        //print_r ($json);
        /*
        {
   "stockItems": [
      {
        "item_id": 1,
        "product_id": 1,
        "stock_id": 1,
        "qty": 15
      },
      {
        "item_id": 2,
        "product_id": 2,
        "stock_id": 2,
        "qty": 15
      }
   ],
   "shipping_address":[
            {
            "firstname":"QisstPay",
            "lastname" : "BNPL",
            "street" : "DHA Phase 5",
            "city":"Lahore",
            "country_id":"PK",
            "region":"CA",
            "postcode":"33284",
            "telephone":"03011000201",
            "fax":"56456",
            "save_in_address_book": 1
            }
    ],
    "currency_id":"PKR",
    "email":"zaheer.ahmed@qisstpay.com"
}
*/
        return 1;
    }
/* This is Validator Function Only  End */
}
