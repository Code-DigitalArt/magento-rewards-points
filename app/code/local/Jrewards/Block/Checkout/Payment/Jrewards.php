<?php
/**
 * Company: Unleaded Group.
 * Developer: Jason Roy
 * Email: Jason@UnleadedGroup.com
 * Module: Unleaded_Jrewards
 */

class Unleaded_Jrewards_Block_Checkout_Payment_Jrewards extends Mage_Core_Block_Template
{
    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return Mage::getSingleton('customer/session')->getId();
    }

    /**
     * @return string
     */
    public function useRewardsLabel()
    {
        return Mage::helper('unleaded_jrewards')->__('Use JackRabbit Rewards');
    }

    /**
     * @return string
     */
    public function rewardsAmountLabel()
    {
        return Mage::helper('unleaded_jrewards')->__(' total' );
    }

    /**
     * @return mixed
     */
    public function checkRewardBalance()
    {
        $customerId = $this->getCustomerId();
        $rewardBalance = Mage::helper('unleaded_jrewards/api')->getRewardBalance($customerId);
        $balance  = 0;

        if($rewardBalance >= 5)
        {
                $balance = (int)(($rewardBalance) - ($rewardBalance % 5));
        }

        return $balance;
    }

    /**
     * @return int
     */
    public function isRewardsMember()
    {
        $customerId = $this->getCustomerId();
        $membershipStatus = Mage::helper('unleaded_jrewards/data')->rewardsCustomer($customerId);;
        return $membershipStatus;
    }

    /**
     * @return mixed
     */
    public function rewardsToUse()
    {
        $name = 'reward_amount';
        if(Mage::getModel('core/cookie')->get($name))
        {
            $amountApplied = Mage::getModel('core/cookie')->get($name);
            $rtu = $amountApplied;
        } else {
            $rtu = $this->checkRewardBalance();
        }

        return $rtu;
    }


}