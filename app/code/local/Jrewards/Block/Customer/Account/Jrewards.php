<?php
/**
 * Company: Unleaded Group.
 * Developer: Jason Roy
 * Email: Jason@UnleadedGroup.com
 * Module: Unleaded_Jrewards
 */

class Unleaded_Jrewards_Block_Customer_Account_Jrewards extends Mage_Core_Block_Template
{
    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return Mage::getSingleton('customer/session')->getId();
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    public function getCustomer()
    {
        $customerId = $this->getCustomerId();
        $customer = Mage::getModel('customer/customer')->load($customerId);
        return $customer;
    }

    /**
     * @return mixed
     */
    public function getTelephone()
    {
        return $this->getCustomer()->getPrimaryBillingAddress()->getTelephone();
    }

    /**
     * @return int
     */
    public function rewardsCustomer()
    {
        $response_code = Mage::helper('unleaded_jrewards/api')->getStatus($this->getCustomerId());
        if($response_code == 'Active' || $response_code == 'Reactivated')
        {
            return 1;
        }else if($response_code == 'Pending'){
            return 2;
        } else {
            return 0;
        }
    }

    public function postVerify()
    {
        $customer = $this->getCustomer();
        Mage::helper('unleaded_jrewards/api')->postVerify($customer);
    }

    /**
     * @return array
     */
    public function loyaltyData()
    {
        $customerId = $this->getCustomerId();
        $data = Mage::helper('unleaded_jrewards/api')->getLoyalty($customerId);
        return $data;
    }

    /**
     * @return string
     */
    public function myRewardsTable()
    {
        date_default_timezone_set('America/New_York');
        $customerId = $this->getCustomerId();
        $rewardData = Mage::helper('unleaded_jrewards/api')->getReward($customerId);
        $rewardArray = $rewardData['curl values']['RewardRewards'];

        $table = '<table 
                        width="100%" 
                        border="0" 
                        cellspacing="0" 
                        cellpadding="0"
                    >';

        $ETable = '</table>';

        $spacing = '<colgroup>
                        <col />
                        <col width="20%" />
                        <col width="15%" />
                        <col width="15%" />
                    </colgroup>';



        $THead = '<thead><tr>
                    <th>DESCRIPTION</th>
                    <th>REWARDS EARNED</th>
                    <th>EXPIRATION</th>
                    <th>Status</th>
                    <th>REWARDS USED</th>
                  </tr></thead>';

        $TSBody = '<body>';
        $TEBody = '</body>';

        $TSRow  = '<tr>';
        $TERow  = '</tr>';

        $TSData = '<td>';
        $TEData = '</td>';


        $table .= $spacing;
        $table .= $THead;
        $table .= $TSBody;

        foreach($rewardArray as $item)
        {
            $table .= $TSRow;

                $table .= $TSData . $item['RewardType'] . ': ' . date('m/d/Y', strtotime($item['RewardIssued']))
                    . $TEData;
                $table .= $TSData . $item['RewardEarned'] . $TEData;
                $table .= $TSData . date('m/d/Y', strtotime($item['RewardExpirationDate'])) . $TEData;
                $table .= $TSData . $item['RewardStatus'] . $TEData;
                $table .= $TSData . ($item['RewardEarned'] - $item['RewardAvailable']);

            $table .= $TERow;
        }
        $table .= $TEBody;
        $table .= $ETable;

        return $table;
    }

    /**
     * @return string
     */
    public function rewardsUsed()
    {
        date_default_timezone_set('America/New_York');
        $customerId = $this->getCustomerId();
        $rewardData = Mage::helper('unleaded_jrewards/api')->getReward($customerId);
        $rewardArray = $rewardData['curl values']['RewardRedeems'];

        $table = '<table 
                        width="100%" 
                        border="0" 
                        cellspacing="0" 
                        cellpadding="0"
                    >';

        $ETable = '</table>';

        $spacing = '<colgroup>
                        <col width="50%" />
                        <col width="50%" />
                    </colgroup>';



        $THead = '<thead><tr>
                       <th class="a-center">REWARDS USED</th>
                       <th class="a-center">DATE</th>
                  </tr></thead>';

        $TSBody = '<body>';
        $TEBody = '</body>';

        $TSRow  = '<tr>';
        $TERow  = '</tr>';

        $TSData = '<td class="a-center">';
        $TEData = '</td>';


        $table .= $spacing;
        $table .= $THead;
        $table .= $TSBody;

        foreach($rewardArray as $item)
        {
            $table .= $TSRow;

            $table .= $TSData . $item['Dollars'];
            $table .= $TSData . date('m/d/Y' ,strtotime($item['Issued'])) . $TEData;

            $table .= $TERow;
        }
        $table .= $TEBody;
        $table .= $ETable;

        return $table;
    }

    /**
     * @return string
     */
    public function transactionTable()
    {
        date_default_timezone_set('America/New_York');
        $customerId = $this->getCustomerId();
        $transactionData = Mage::helper('unleaded_jrewards/api')->getTransactions($customerId);
        $transactionsArray = $transactionData['curl values']['Transactions'];

        $table = '<table 
                        width="100%" 
                        border="0" 
                        cellspacing="0" 
                        cellpadding="0"
                    >';

        $ETable = '</table>';

        $spacing = '<colgroup>
                        <col width="25%" />
                        <col width="25%" />
                        <col width="25%" />
                        <col width="25%" />
                    </colgroup>';



        $THead = '<thead><tr>
                       <th class="a-center">POINTS EARNED</th>
                       <th class="a-center">DATE</th>
                       <th class="a-center">TRANSACTION</th>
                       <th class="a-center">LOCATION</th>
                  </tr></thead>';

        $TSBody = '<body>';
        $TEBody = '</body>';

        $TSRow  = '<tr>';
        $TERow  = '</tr>';

        $TSData = '<td class="a-center">';
        $TEData = '</td>';


        $table .= $spacing;
        $table .= $THead;
        $table .= $TSBody;

        foreach($transactionsArray as $item)
        {
            $table .= $TSRow;

            $table .= $TSData . $item['PointsEarned'];
            $table .= $TSData . date('m/d/Y' ,strtotime($item['TransDate'])) . $TEData;
            $table .= $TSData . $item['POSTransNum'];
            $table .= $TSData . $item['Store'];

            $table .= $TERow;
        }
        $table .= $TEBody;
        $table .= $ETable;

        return $table;
    }

}