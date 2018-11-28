<?php
/**
 * Company: Unleaded Group.
 * Developer: Jason Roy
 * Email: Jason@UnleadedGroup.com
 * Module: Unleaded_Jrewards
 */
 
class Unleaded_Jrewards_Helper_Reward
{
    /**
     * @param $amount
     */
    public function runReward($amount)
    {
        $name = $this->_generateRewardName();
        $code = $this->_generateRewardCode();
        $this->generateReward($name, $code, $amount);
        $this->applyReward($code);
    }

    /**
     * @param $name
     * @param $code
     * @param $amount
     */
    public function generateReward($name, $code, $amount)
    {
        $reward = Mage::getModel('salesrule/rule');

        $reward
            ->setName($name)
            ->setDescription('JackRabbit Reward')
            ->setFromDate(date(''))
            ->setCouponType(2)
            ->setCouponCode($code)
            ->setUsesPerCoupon(1)
            ->setUsesPerCustomer(1)
            ->setCustomerGroupIds([1])
            ->setIsActive(1)
            ->setConditionsSerialized('a:6:{s:4:"type";s:32:"salesrule/rule_condition_combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";}')
            ->setActionsSerialized('a:6:{s:4:"type";s:40:"salesrule/rule_condition_product_combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";}')
            ->setStopRulesProcessing(0)
            ->setIsAdvanced(1)
            ->setProductIds('')
            ->setSortOrder(0)
            ->setSimpleAction('cart_fixed')
            ->setDiscountAmount($amount)
            ->setDiscountQty(null)
            ->setDiscountStep('0')
            ->setSimpleFreeShipping('0')
            ->setApplyToShipping('0')
            ->setIsRss(0)
            ->setWebsiteIds([1]);

        $reward->save();
    }


    /**
     * @return string
     */
    private function _generateRewardName()
    {
        $prefix = 'jack';
        $alphanumeric = '012345679abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $alphaLength = strlen($alphanumeric);
        $rand = '';
        $length = 6;
        for($i=0; $i < $length; $i++)
        {
            $rand .= $alphanumeric[rand(0, $alphaLength - 1)];
        }

        $name = $prefix . $rand;

        return $name;
    }

    /**
     * @return string
     */
    private function _generateRewardCode()
    {
        $prefix = 'reward';
        $alphanumeric = '012345679abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $alphaLength = strlen($alphanumeric);
        $rand = '';
        $length = 6;
        for($i=0; $i < $length; $i++)
        {
            $rand .= $alphanumeric[rand(0, $alphaLength - 1)];
        }

        $name = $prefix . $rand;

        return $name;
    }

    /**
     * @param $code
     */
    public function applyReward($code)
    {
        Mage::getSingleton('checkout/session')->setData('coupon_code', $code);
        Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode($code)->save();

    }

    public function deleteReward()
    {
        $reward = Mage::getModel('salesrule/rule')
            ->getCollection()
            ->addFieldToFilter(
                'code',
                Mage::getSingleton('checkout/session')
                    ->getQuote()
                    ->getCouponCode())
            ->getFirstItem();

        $reward->delete();
    }

    /**
     * @param int $discountApply
     */
    public function setDiscount($discountApply = 0)
    {
        $quote          = Mage::getSingleton('checkout/session')->getQuote();
        $quoteId        = $quote->getId();
        $discountAmount = $discountApply;


        if($quoteId)
        {
            if($discountAmount > 0)
            {
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

                    $quote->save();

                    $quote
                        ->setGrandTotal($quote->getBaseSubtotal() - $discountAmount)
                        ->setBaseGrandTotal($quote->getBaseSubtotal() - $discountAmount)
                        ->setSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                        ->setBaseSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                        ->save();

                    if($address->getAddressType() == $canAddItems)
                    {
                        $address->setSubtotalWithDiscount((float)
                            $address->getSubtotalWithDiscount()
                            - $discountAmount);

                        $address->setBaseSubtotalWithDiscount((float)
                            $address->getBaseSubtotalWithDiscount()
                            - $discountAmount);

                        $address->setGrandTotal((float) $address->getGrandTotal() - $discountAmount);
                        $address->setBaseGrandTotal((float) $address->getBaseGrandTotal() - $discountAmount);

                        if($address->getDiscountDescription())
                        {
                            $address->setDiscountAmount(-($address->getDiscountAmount() - $discountAmount));
                            $address->setDiscountDescription($address->getDiscountDescription()
                                . ', JackRabbit Reward Discount');
                            $address->setBaseDiscountAmount(-($address->getBaseDiscountAmount() - $discountAmount));
                        } else
                        {
                            $address->setDiscountAmount(-($discountAmount));
                            $address->setDiscountDescription('JackRabbit Reward Discount');
                            $address->setBaseDiscountAmount(-($discountAmount));
                        }
                        $address->save();
                    }

                }
            }
        }

        $this->loadLayout(false);
        $this->renderLayout();
    }

}
