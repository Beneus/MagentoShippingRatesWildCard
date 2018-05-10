<?php
 /*
Overriding \Magento\Shipping\Model\Rate\Result to add a new function to calculate the new price
The function is called f0r the plugin ShippingPlugin and set the new rate price.
 */
namespace Beneus\ShippingTableRates\Model\Rate;
 
class Result extends \Magento\Shipping\Model\Rate\Result
 
{
 	
   public function changeRatePrice($price)
    {
       
        foreach ($this->_rates as $rate) {
            $rate->setPrice($price);
        }

        return $this;
    }

}

