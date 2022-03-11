<?php
namespace Qisst\Oneclick\Model\Api;
class Onepage extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $storeFactory;

    protected $orderRepository;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,

        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Quote\Model\Quote\Address\Rate $rate,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    )
    {
        $this->_registry = $registry;
        $this->_storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
        $this->_objectManager = $objectManager;
        $this->_productFactory = $productFactory;
        $this->_customerRepository = $customerRepository;
        $this->_cartRepositoryInterface = $cartRepository;
        $this->_shippingRate = $rate;
        $this->orderRepository = $orderRepository;
        parent::__construct($context,$registry);
    }

    public function placeOrder($params) {
//return $params;
        //$customer = $this->_registry->registry('auth_customer');
        // if (!$customer) {
        //     throw new Exception($this->__('User not authorized'));
        // }

        //$data = json_decode(json_encode($data), True);

        // $items = $this->getCartItems();
        //$items = $data['products'];

        //$customerAddressModel = $this->_objectManager->create('Magento\Customer\Model\Address');
        //$shippingID =  $customer->getDefaultShipping();
        //$address = $customerAddressModel->load($shippingID);

//magento default currency get ###########################################
        $orderData = [
            'currency_id' => 'PKR',
            'email' => $params['email'], //buyer email id
            'shipping_address' => [
                'firstname' => $params['first_name'],
                'lastname' => $params['last_name'],
                'street' => $params['address_1'].' '.$params['address_2'],
                'city' => $params['city'],
                'country_id' => $params['country'],
                'region' => $params['state'],
                'postcode' => $params['postcode'],
                'shipping_total'=> $params['shipping_total'],
                'telephone' => $params['phone'],
                'tax' => $params['tax_amount'],
                'fax' => '',
                'save_in_address_book' => 1
            ],

            'items' => $params['products'],
          ];
        return $this->createOrder($orderData, $orderData);
    }
    public function createOrder($orderData, $data)
    {
        $response=[];
        //$response['success']=FALSE;

        if(!count($orderData['items'])) {
            $response['error_msg'] = 'Cart is Empty';
        } else {
            $this->cartManagementInterface = $this->_objectManager->get('\Magento\Quote\Api\CartManagementInterface');

            //init the store id and website id
            $store = $this->_storeManager->getStore(1);
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();

            //init the customer
            $customer = $this->_customerFactory->create();
            $customer->setWebsiteId($websiteId);
            $customer->loadByEmail($orderData['email']);// load customer by email address

            //check the customer
            if (!$customer->getEntityId()) {

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
            $cart = $this->_cartRepositoryInterface->get($cart_id);

            $cart->setStore($store);

            // if you have already buyer id then you can load customer directly
            $customer = $this->_customerRepository->getById($customer->getEntityId());
            $cart->setCurrency();
            $cart->assignCustomer($customer); //Assign quote to customer

            $_productModel = $this->_productFactory->create();
            //add items in quote
            $naming = '';
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productRepo = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
            //$product = $productRepo->getById(1);
            foreach ($orderData['items'] as $item) {

                //$product = $_productModel->load($item['id']);
                $product = $productRepo->getById($item['id']);

                $naming = $naming.' '.$item['id'];
                try {
                    // print_r($item); die();
                    $params = array('product' => $item['id'], 'qty' => $item['quantity']);
                    // if (array_key_exists('options', $item) && $item['options']) {
                    //     $params['options'] = json_decode(json_encode($item['options']), True);
                    // }
                    // if ($product->getTypeId() == 'configurable') {
                    //     $params['super_attribute'] = $item['super_attribute'];
                    // } elseif ($product->getTypeId() == 'bundle') {
                    //     $params['bundle_option'] = $item['bundle_option'];
                    //     $params['bundle_option_qty'] = $item['bundle_option_qty'];
                    // } elseif ($product->getTypeId() == 'grouped') {
                    //     $params['super_group'] = $item['super_group'];
                    // }

                    // $objParam = new \Magento\Framework\DataObject();
                    // $objParam->setData($params);
                    // $cart->addProduct($product, $objParam);

                     $cart->addProduct($product,intval($item['quantity']));
                } catch (Exception $e) {
                    $response[$item['id']]= $e->getMessage();
                }
            }

            $cart->getBillingAddress()->addData($orderData['shipping_address']);
            $cart->getShippingAddress()->addData($orderData['shipping_address']);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            $baseUrl = $storeManager->getStore()->getBaseUrl();  // to get Base Url
            $costing = $orderData['shipping_address']['shipping_total'];
            $curl = curl_init();
            $apiurl = $baseUrl."/rest/V1/qp/returnshipmeth?cost=".$orderData['shipping_address']['shipping_total'];
            curl_setopt($curl, CURLOPT_URL, $apiurl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($curl);
            $shippingCost = str_replace('"', '', $output);
            curl_close($curl);
            $this->_shippingRate->setCode($shippingCost.'_'.$shippingCost)->getPrice(1);
            $shippingAddress = $cart->getShippingAddress();
            $shippingAddress->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod($shippingCost.'_'.$shippingCost);
            $cart->getShippingAddress()->addShippingRate($this->_shippingRate);
            $cart->setPaymentMethod('qppayment'); //payment method
            $cart->setInventoryProcessed(false);
            $cart->getPayment()->importData(['method' => 'qppayment']);
            $cart->collectTotals();
            $cart->save();
            //$cart = $this->_cartRepositoryInterface->get($cart->getId());
            try{
                $order_id = $this->_objectManager->get('\Magento\Quote\Api\CartManagementInterface')->placeOrder($cart->getId());
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $order = $objectManager->create('\Magento\Sales\Model\Order')->load($order_id);
                $order->addStatusHistoryComment('This comment is programatically added to last order in this Magento setup');
                $order->setShippingAmount($costing);
                $order->setTaxAmount($orderData['shipping_address']['tax']);
                $order->setBaseTaxAmount($orderData['shipping_address']['tax']);
                $order->setBaseShippingAmount($costing);
                $order->setGrandTotal($order->getGrandTotal() + $costing + $orderData['shipping_address']['tax']);
                $order->save();
                if(isset($order_id) && !empty($order_id)) {
                    $order = $this->orderRepository->get($order_id);
                    $this->deleteQuoteItems(); //Delete cart items
                    $response['success'] = TRUE;
                    $response['order_id'] = $order->getIncrementId();
                    $response['link'] = $this->_storeManager->getStore()->getBaseUrl().'qisstpay?orderid='.$order->getIncrementId();
                    $response['payment_method'] = 'qisstpay';
                    //return [['success'=>true]];

                }
            } catch (Exception $e) {
                $response['error_msg']=$e->getMessage();
            }
        }
        //$factory = new \Magento\Framework\Controller\Result\JsonFactory();
        $responsiveData = json_encode($response);
        return $responsiveData;
    }

    public function deleteQuoteItems(){
        $checkoutSession = $this->getCheckoutSession();
        $allItems = $checkoutSession->getQuote()->getAllVisibleItems();//returns all teh items in session
        foreach ($allItems as $item) {
            $itemId = $item->getItemId();//item id of particular item
            $quoteItem=$this->getItemModel()->load($itemId);//load particular item which you want to delete by his item id
            $quoteItem->delete();//deletes the item
        }
    }
    public function getCheckoutSession(){
        $checkoutSession = $this->_objectManager->get('Magento\Checkout\Model\Session');//checkout session
        return $checkoutSession;
    }

    public function getItemModel(){
        $itemModel = $this->_objectManager->create('Magento\Quote\Model\Quote\Item');//Quote item model to load quote item
        return $itemModel;
    }


    public function shippingMethodsList(){
      return "Progress";
    }
}
