<?php
/**
 * Company: Unleaded Group.
 * Developer: Jason Roy
 * Email: Jason@UnleadedGroup.com
 * Module: Unleaded_Jrewards
 */

class Unleaded_Jrewards_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * @param $customer
     * @param $request
     */
    public function registerUser($customer, $request)
    {
        if($request['agreement'] == 'on')
        {
            $customer->setData('jrewards_member', $this->_onSwitch($request['agreement']));
            $customer->getResource()->saveAttribute($customer, 'jrewards_member');

            $customer->setData('jrewards_secondary_email', $request['secondary_email']);
            $customer->getResource()->saveAttribute($customer, 'jrewards_secondary_email');

            $customer->setData('jrewards_text_messaging', $this->_onSwitch($request['text-messaging']));
            $customer->getResource()->saveAttribute($customer, 'jrewards_text_messaging');

            $customer->setData('jrewards_cell_number', $request['cell-phone']);
            $customer->getResource()->saveAttribute($customer, 'jrewards_cell_number');

            $customer->setData('jrewards_home_number', $request['home_phone']);
            $customer->getResource()->saveAttribute($customer, 'jrewards_home_number');

            Mage::helper('unleaded_jrewards/api')->putPersonData($customer->getId());
            Mage::helper('unleaded_jrewards/api')->postVerify($customer);
            Mage::helper('unleaded_jrewards/api')->putLoyaltyStatus($customer);
        }
    }

    /**
     * @param $value
     * @return int
     */
    protected function _onSwitch($value)
    {
        if($value == 'on')
        {
            return 1;
        }

        return 0;
    }

    /**
     * @param $customerId
     * @return int
     */
    public function rewardsCustomer($customerId)
    {
        $loyalty = Mage::helper('unleaded_jrewards/api')->getLoyaltyStatus($customerId);
        $loyaltyStatus = $loyalty['curl values']['Status'];
        if($loyaltyStatus == 'Active')
        {
            return 1;
        }

        return 0;
    }

}
