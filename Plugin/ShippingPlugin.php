<?php
namespace Beneus\ShippingTableRates\Plugin;


use Magento\Quote\Model\Quote\Address\RateCollectorInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use \Magento\Checkout\Model\Session as CheckoutSession;

class ShippingPlugin
{
    /**
     * @var string
     */
    protected $_code = 'Shipping';

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var
     */
    protected $_result;

    /**
     * @var RateCollectorInterface
     */
    protected $_rateCollector;
    protected $_checkoutSession;
     /**
     * @var \Magento\OfflineShipping\Model\Carrier\Tablerate
     */
    protected $_tablerate;

    /**
     * @var \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CollectionFactory
     */
    protected $_collectionFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        RateCollectorInterface $rateCollector,
        CheckoutSession $checkoutSession,
        \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CollectionFactory $collectionFactory,
        \Magento\OfflineShipping\Model\Carrier\Tablerate $tablerate,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->directory_list = $directory_list;
        $this->cart = $cart;
        $this->resourceConnection = $resourceConnection;
        $this->_rateCollector = $rateCollector;
        $this->_checkoutSession = $checkoutSession;
         $this->_collectionFactory = $collectionFactory;
        $this->_tablerate = $tablerate;
    }

    /**
     * @param \Magento\Shipping\Model\Shipping $subject
     * @param $collectRatesResult
     * @return RateCollectorInterface
     */
/*
\Magento\Shipping\Model\Shipping $subject,
\Magento\Quote\Model\Quote\Address\RateRequest $request
*/
    public function afterCollectRates($subject, $collectRatesResult)
    {
  
        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $collectRatesResult->getResult();		
        $ShippingAddress = $this->_checkoutSession->getQuote()->getShippingAddress();
		
        $countryId = $ShippingAddress->getCountryId();
        $postCode = $ShippingAddress->getPostcode();
        $subTotal = $ShippingAddress->getSubtotal();

        $collection = $this->_collectionFactory->create();
        $collection->setCountryFilter($countryId);
/*
     Your code to change the rule should go here
*/
        $rateFound = false;
	$prices = [];
        foreach($collection as $rate){	
        	//dest_zip must end with wildcard (*)
		if(strpos($rate['dest_zip'],'*') > 0){
			$postCodeRoot = str_replace('*','',$rate['dest_zip']);
			$pos = strpos($postCode, $postCodeRoot);
			 if($pos !== false){
				// condition_value must be lower than subtotal and the lowest of all of them
				if( ($subTotal - $rate['condition_value']) >= 0){
					$prices[] = $rate['price'];
					$rateFound= true;
				}	
			    //$result->updateRatePrice($rate['price']/$price);
			 }
            	}
        }
		if($rateFound){
			$result->changeRatePrice(min($prices));
		}
        return $collectRatesResult;
    }
}
