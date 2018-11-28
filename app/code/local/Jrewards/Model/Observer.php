<?php
/**
 * Company: Unleaded Group.
 * Developer: Jason Roy
 * Email: Jason@UnleadedGroup.com
 * Module: Unleaded_Jrewards
 */


class Unleaded_Jrewards_Model_Observer
{

    /**
     * @internal param $observer
     */
//    public function finalizeRewardTransaction()
//    {
//        $customer   = Mage::getSingleton('customer/session')->getCustomer();
//
//        $customerId = Mage::getSingleton('customer/session')->getId();
//        $redeemId = Mage::getModel('core/cookie')->get('qid');
//        $amount = Mage::getSingleton('customer/session')->getCustomer()->getData('jrewards_reward_amount');
//        $amount = $this->_notNull($amount);
//        $order = Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId());
//        $orderId = $order->getIncrementId();
//        $api = Mage::helper('unleaded_jrewards/api');
//        $api->putRewardFinal($customerId, $redeemId, $amount, $orderId);
//
//        $customer->setData('jrewards_reward_amount', 0);
//        $customer->getResource()->saveAttribute($customer, 'jrewards_reward_amount');
//
//    }

    /**
     * @param $string
     * @return int
     */
    public function _notNull($string)
    {
        if($string == null)
        {
            return 0;
        } elseif($string == false) {
            return 0;
        }
        else {
            return $string;
        }
    }

    /**
     * @param $string
     * @return string
     */
    public function _blankString($string)
    {
        if($string == null) {
            return '';
        } elseif ($string == false) {
            return '';
        }else{
            return $string;
        }
    }

    /**
     * @param $observer
     */
    public function createRewardRegister($observer)
    {
        $customer = $observer->getCustomer();
        if(Mage::app()->getRequest()->getParam('product_preference')){
            $customer->setGender(Mage::app()->getRequest()->getParam('product_preference'));
            $customer->getResource()->saveAttribute($customer, 'gender');
        }

        if(Mage::app()->getRequest()->getParam('jackrabbit-signup') == 'on') {



            $verification_code = Mage::getModel('core/cookie')->get('JRVerification');

            if ($verification_code) {
                $customer->setData('jrewards_verify_code', $verification_code);
                $customer->getResource()->saveAttribute($customer, 'jrewards_verify_code');
            }

            $request = [
                'agreement' => Mage::app()->getRequest()->getParam('jackrabbit-signup'),
                'secondary_email' => Mage::app()->getRequest()->getParam('secondary-email'),
                'cell_phone' => Mage::app()->getRequest()->getParam('cell-phone'),
                'text_messaging' => Mage::app()->getRequest()->getParam('text-messaging'),
                'home_phone' => Mage::app()->getRequest()->getParam('home-phone')
            ];

            Mage::helper('unleaded_jrewards/data')->registerUser($customer, $request);

        }
    }

    /**
     * @param $observer
     */
    public function loginRegister($observer)
    {
        $customer       = $observer->getCustomer();
        $verification_code = Mage::getModel('core/cookie')->get('JRVerification');
        if($verification_code)
        {
            $customer->setData('jrewards_verify_code', $verification_code);
            $customer->getResource()->saveAttribute($customer, 'jrewards_verify_code');
            Mage::helper('unleaded_jrewards/api')->postVerify($customer);
        }
    }

    /**
     * @param $observer
     */
    public function postTransactionData(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $orderData = $order->getData();
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $jreward = $quote->getData('JRReward');
        $customerId = $orderData['customer_id'];
        $orderId = $orderData['increment_id'];

        $cartItems = $quote->getAllVisibleItems();
        $products = [];
        $i = 0;
        foreach ($cartItems as $item)
        {

            $products[$i] = [
                'name' => $item->getProduct()->getName(),
                'sku'   => $item->getProduct()->getSku(),
                'price' => $item->getProduct()->getFinalPrice(),
                'qty'   => $item->getQty()
            ];

            $i++;
        }

        $transactionData =
            [
                'value_schema_id' => 65,
                'records' =>
                    [
                        [
                            'value' => [
                                'order_id'    => $orderId,
                                'customer_id' => $customerId,
                                'firstname'   => $this->_blankString($orderData['customer_firstname']),
                                'lastname'    => $this->_blankString($orderData['customer_lastname']),
                                'email'       => $this->_blankString($orderData['customer_email']),
                                'dob'         => $this->_blankString($orderData['customer_dob']),
                                'grand_total' => $this->_notNull($orderData['grand_total']),
                                'subtotal'    => $this->_notNull($orderData['subtotal']),
                                'tax'         => $this->_notNull($orderData['tax_amount']),
                                'shipping'    => $this->_notNull($orderData['shipping_amount']),
                                'shipping_tax'=> $this->_notNull($orderData['shipping_tax_amount']),
                                'discount'    => $this->_notNull($orderData['discount_amount']),
                                'gift_cards'  => $this->_notNull($orderData['gift_cards']),
                                'date_time'   => $orderData['created_at'],
                                'jrreward'    => $this->_notNull($jreward),
                                'products'    => $products
                            ]
                        ]
                    ]
            ];

        $tData = json_encode($transactionData);

        Mage::helper('unleaded_jrewards/api')->postTransactionData($tData);

    }

    public function setDiscount()
    {

        $customer        = Mage::getSingleton('customer/session')->getCustomer();
        $customerId      = $customer->getId();

        $quote           = Mage::getSingleton('checkout/session')->getQuote();
        $quoteId         = $quote->getId();
        $grandTotal      = (float)$quote->getGrandTotal();

        $name            = 'reward_amount';
        $amountApplied   = (int)Mage::getModel('core/cookie')->get($name);
        $shipping        = $quote->getShippingAddress()->getShippingAmount();
        $tax             = $quote->getShippingAddress()->getData('tax_amount');
        $GTMShipping     = $grandTotal - $shipping - $tax;
        $rewardAvailable = Mage::helper('unleaded_jrewards/api')->getRewardBalance($customerId);

        $discountAmount  = $this->discountAmount($amountApplied, $GTMShipping, $rewardAvailable);


        $gift_card_serial = $quote->getData('gift_cards');
        $gift_card        = unserialize($gift_card_serial);
        $gift_card_code   = $gift_card['0']['c'];
        $gift_card_amount = $gift_card['0']['a'];
        $gift_pay        = 0;

        if(($grandTotal - $discountAmount) > 0 && ($gift_card_code))
        {
            if($gift_card_amount >= $grandTotal)
            {
                $gift_pay = $grandTotal;
            } else {
                $gift_pay = $gift_card_amount;
            }
        }

        if($customer->getEntityId()){
            $customer
                ->setData('jrewards_quote_before', $quoteId);
            $customer
                ->getResource()->save($customer);
        }



        $quote->setData('JRReward', $discountAmount);

        if($quoteId)
        {
            if($discountAmount > 0)
            {
                $total=$quote->getBaseSubtotal();
                $InitialProductTax = $quote->getShippingAddress()->getTaxAmount()
                    - $quote->getShippingAddress()->getShippingTaxAmount(); // 5.16
                $TaxPercentage = round(($InitialProductTax / $quote->getSubtotal()),4, PHP_ROUND_HALF_UP);
                $ProductTax = ($quote->getSubtotal() - $discountAmount) * $TaxPercentage;



                $TaxAfterDiscount = $ProductTax + $quote->getShippingAddress()->getShippingTaxAmount();
                $TaxAfterDiscount = round($TaxAfterDiscount, 2, PHP_ROUND_HALF_UP);


                $quote->setSubtotal(0);
                $quote->setBaseSubtotal(0);

                $quote->setSubtotalWithDiscount(0);
                $quote->setBaseSubtotalWithDiscount(0);

                $quote->setGrandTotal(0);
                $quote->setBaseGrandTotal(0);

                $canAddItems = $quote->isVirtual() ? ('billing') : ('shipping') ;

                foreach($quote->getAllAddresses() as $address)
                {
                    $address->setBaseSubtotal(0);

                    $address->setGrandTotal(0);
                    $address->setBaseGrandTotal(0);
                    $address->collectShippingRates();
                    $address->collectTotals(0);


                    $quote->setSubtotal((float) $quote->getSubtotal() + $address->getSubtotal());
                    $quote->setBaseSubtotal((float) $quote->getBaseSubtotal() + $address->getBaseSubtotal());

                    $quote->setSubtotalWithDiscount((float)
                        $quote->getSubtotalWithDiscount() +
                        $address->getSubtotalWithDiscount());

                    $quote->setBaseSubtotalWithDiscount((float)
                        $quote->getBaseSubtotalWithDiscount() +
                        $address->getBaseSubtotalWithDiscount());

                    $quote->setGrandTotal((float) $quote->getGrandTotal() + $address->getGrandTotal());
                    $quote->setBaseGrandTotal((float) $quote->getBaseGrandTotal() + $address->getBaseGrandTotal());
                    if($gift_card_code)
                    {
                        Mage::helper('enterprise_giftcardaccount')->setCards($quote, $gift_card);
                    }

                    $quote
                        ->setGrandTotal($quote->getBaseSubtotal() + $TaxAfterDiscount - $discountAmount)
                        ->setBaseGrandTotal($quote->getBaseSubtotal() + $TaxAfterDiscount  - $discountAmount)
                        ->setSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                        ->setBaseSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                        ->save();

                    if($address->getAddressType() == $canAddItems)
                    {
                        if($gift_card_code)
                        {
                            $address->setSubtotalWithDiscount((float)
                                $address->getSubtotalWithDiscount()
                                - ($discountAmount + $gift_pay));
                            $address->setBaseSubtotalWithDiscount((float)
                                $address->getBaseSubtotalWithDiscount()
                                - ($discountAmount + $gift_pay));
                            $address->setGrandTotal((float) ($address->getBaseGrandTotal() + $TaxAfterDiscount)
                                - ($discountAmount + $gift_pay + $quote->getShippingAddress()->getTaxAmount()));
                            $address->setBaseGrandTotal((float) ($address->getBaseGrandTotal() + $TaxAfterDiscount)
                                - ($discountAmount + $gift_pay + $quote->getShippingAddress()->getTaxAmount()));
                            $address->setTaxAmount($TaxAfterDiscount);
                            $address->setBaseTaxAmount($TaxAfterDiscount);
                        } else {
                            $address->setSubtotalWithDiscount((float)
                                $address->getSubtotalWithDiscount()
                                - $discountAmount);

                            $address->setBaseSubtotalWithDiscount((float)
                                $address->getBaseSubtotalWithDiscount()
                                - $discountAmount);

                            $address->setGrandTotal((float)($address->getBaseGrandTotal() + $TaxAfterDiscount) -
                                ($discountAmount + $quote->getShippingAddress()->getTaxAmount()));
                            $address->setBaseGrandTotal((float)($address->getBaseGrandTotal() + $TaxAfterDiscount) -
                                ($discountAmount + $quote->getShippingAddress()->getTaxAmount()));
                            $address->setTaxAmount($TaxAfterDiscount);
                            $address->setBaseTaxAmount($TaxAfterDiscount);
                        }
                        if($address->getDiscountDescription())
                        {
                            $address->setDiscountAmount(-($address->getDiscountAmount() - $discountAmount));
                            $address->setDiscountDescription($address->getDiscountDescription()
                                . ', JackRabbit Reward Discount ' . $discountAmount);
                            $address->setBaseDiscountAmount(-($address->getBaseDiscountAmount() - $discountAmount));
                        } else
                        {
                            $address->setDiscountAmount(-($discountAmount));
                            $address->setDiscountDescription('JackRabbit Reward Discount ' . $discountAmount);
                            $address->setBaseDiscountAmount(-($discountAmount));
                        }

                        if($gift_card_code)
                        {
                            $address->setGiftCards($gift_card_serial);
                            $address->setGiftCardsAmount($gift_card_amount);
                            $address->setBaseGiftCardsAmount($gift_card_amount);
                            $address->setUsedGiftCards($gift_card_serial);
                        }

                        $address->save();
                    }
                }

                foreach($quote->getAllItems() as $item)
                {
                    $ratio = $item->getPrice() / $total;
                    $ratioDiscount = $discountAmount * $ratio;
                    $item->setDiscountAmount(($item->getDiscountAmount() + $ratioDiscount) * $item->getQty());
                    $item->setBaseDiscountAmount(($item->getBaseDiscountAmount() + $ratioDiscount) * $item->getQty());
                    $item->save();
                }
            }
        }

    }

    public function rewardRedeemPost()
    {
        $customer        = Mage::getSingleton('customer/session')->getCustomer();
        $customerId      = $customer->getId();

        $checkoutSession = Mage::getSingleton('checkout/session');
        $quote           = $checkoutSession->getQuote();
        $quoteId         = $quote->getId();

        $discountAmount  = abs($quote->getShippingAddress()->getBaseDiscountAmount());

        if($customer->getData('jrewards_reward_amount') != $discountAmount)
        {
            $customer->setData('jrewards_reward_amount', $discountAmount);
            $customer->getResource()->saveAttribute($customer, 'jrewards_reward_amount');
        }

        if($discountAmount > 0)
        {
            $customer->setData('jrewards_quote_after', $quoteId);
            $customer->getResource()->saveAttribute($customer, 'jrewards_quote_after');

            $redeemData = Mage::helper('unleaded_jrewards/api')->putRewardRedeem($customerId, $quoteId, $discountAmount);

            $redeemId   = $redeemData['curl values']['Number'];

            $customer->setData('jrewards_reward_id', $redeemId);
            $customer->getResource()->saveAttribute($customer, 'jrewards_reward_id');

            $quote->setData('JRReward', 0);
        }

    }

    public function rewardRedeemPut()
    {
        $customer   = Mage::getSingleton('customer/session')->getCustomer();
        $customerId = $customer->getId();
        $amount = $customer->getData('jrewards_reward_amount');
        $before = $customer->getData('jrewards_quote_before');
        $after = $customer->getData('jrewards_quote_after');
        $order = Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId());
        $orderId = $order->getIncrementId();
        $api = Mage::helper('unleaded_jrewards/api');
        if($amount && ($before == $after))
        {
            $redeemId = Mage::getModel('core/cookie')->get('qid');
            $amount = $this->_notNull($amount);

            $api->putRewardFinal($customerId, $redeemId, $amount, $orderId);

            $customer->setData('jrewards_reward_amount', 0);
            $customer->getResource()->saveAttribute($customer, 'jrewards_reward_amount');
        }

    }

    public function updatePerson()
    {
        $customer        = Mage::getSingleton('customer/session')->getCustomer();
        $customerId      = $customer->getId();
        $productPreference = Mage::app()->getRequest()->getParam('product_preference');
        if(null != $productPreference)
        {
            $customer->setGender($productPreference);
            $customer->getResource()->saveAttribute($customer, 'gender');
        }
        Mage::helper('unleaded_jrewards/api')->putPersonData($customerId);
    }

    public function discountAmount($amountApplied, $GTMShipping, $rewardAvailable)
    {
        $discountAmount  = 0;

        if($amountApplied >= 5)
        {
            if($amountApplied >= $rewardAvailable)
            {
                if($amountApplied >= $GTMShipping)
                {
                    $discountAmount = (int)($GTMShipping - ($GTMShipping % 5));
                } else {
                    $discountAmount = (int)($rewardAvailable - ($rewardAvailable % 5));
                }
            } else if($amountApplied >= $GTMShipping)
            {
                $discountAmount = (int)($GTMShipping - ($GTMShipping % 5));
            } else
            {
                $discountAmount = (int)($amountApplied - ($amountApplied % 5));
            }
        }

        return $discountAmount;
    }

}