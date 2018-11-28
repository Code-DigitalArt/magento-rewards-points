<?php
/**
 * Company: Unleaded Group.
 * Developer: Jason Roy
 * Email: Jason@UnleadedGroup.com
 * Module: Unleaded_Jrewards
 */
 

class Unleaded_Jrewards_Helper_Api
{
    private $url;
    private $code;
    private $transURL;
    private $magento = '/magento/';
    private $reward = '/reward';
    private $person = '/person';
    private $redeem = '/redeem';
    private $summary = '/summary';
    private $verify = '/verify/';
    private $loyalty = '/loyalty';
    private $status = '/status';
    private $transaction = '/trans';

    /**
     * Unleaded_Jrewards_Helper_Api constructor.
     */
    function __construct()
    {
        $this->url = Mage::getStoreConfig('jrewards_options/section_one/field_one');
        $this->code = Mage::getStoreConfig('jrewards_options/section_one/field_two');
        $this->transURL = Mage::getStoreConfig('jrewards_options/section_one/field_three');
    }

    /**
     * @param $curl_response
     * @param $curl
     */
    protected function curlFail($curl_response, $curl)
    {
        if ($curl_response === false) {
            $info = curl_getinfo($curl);
            curl_close($curl);
            // Log this error
//            die('API Failure : ') . var_export($info);
        }
    }

    /**
     * @param $curl_response
     * @param $curl
     * @return mixed
     */
    protected function curlSuccess($curl_response, $curl)
    {
        curl_close($curl);
        $responseArray = json_decode($curl_response, true);
        return $responseArray;
    }

    /**
     * @param $curl_response
     * @param $curl
     * @return array
     */
    protected function curlResponse($curl_response, $curl)
    {
        $this->curlFail($curl_response, $curl);
        return
            [
            'response code' => curl_getinfo($curl, CURLINFO_HTTP_CODE),
            'curl response' => $curl_response,
            'curl values'   => $this->curlSuccess($curl_response, $curl)
            ];
    }

    /**
     * @param $serviceUrl
     * @return array
     */
    protected function setupGet($serviceUrl)
    {
        $curl = curl_init($serviceUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['authorization: Basic ' . $this->code,
            'Content-Type: application/json']);
        $curl_response = curl_exec($curl);
        return $this->curlResponse($curl_response, $curl);
    }

    /**
     * @param $serviceUrl
     * @param $data
     * @return array
     */
    protected function setupPut($serviceUrl, $data)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $serviceUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['authorization: Basic ' . $this->code,
            'Content-Type: application/json']);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $curl_response = curl_exec($curl);
        return $this->curlResponse($curl_response, $curl);
    }

    /**
     * @param $serviceUrl
     * @param $data
     * @return array
     */
    protected function setupPost($serviceUrl, $data)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $serviceUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['authorization: Basic ' . $this->code,
            'Content-Type: application/json']);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $curl_response = curl_exec($curl);
        return $this->curlResponse($curl_response, $curl);
    }

    /**
     * @param $serviceUrl
     * @param $data
     * @return array
     */
    protected function setupAuthPost($serviceUrl, $data)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $serviceUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['authorization: Basic ' . $this->code,
            'Content-Type: application/vnd.kafka.avro.v1+json']);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $curl_response = curl_exec($curl);
        return $this->curlResponse($curl_response, $curl);
    }

    /**
     * @param $customerId
     * @return mixed
     */
    public function getStatus($customerId)
    {
        $loyaltyStatus = $this->getLoyaltyStatus($customerId);
        return $loyaltyStatus['curl values']['Status'];
    }

    /**
     * @param $customerId
     * @return array
     */
    public function getLoyaltyStatus($customerId)
    {
        $serviceUrl = $this->url . $this->magento . $customerId . $this->loyalty . $this->status;
        $loyaltyStatus = $this->setupGet($serviceUrl);
        return $loyaltyStatus;
    }

    /**
     * @param $customerId
     * @return array
     */
    public function getLoyalty($customerId)
    {
        $serviceUrl = $this->url . $this->magento . $customerId . $this->loyalty;
        $loyaltyData = $this->setupGet($serviceUrl);
        return $loyaltyData;
    }

    /**
     * @param $customerId
     * @return array
     */
    public function getReward($customerId)
    {
        $serviceUrl = $this->url . $this->magento . $customerId . $this->reward;
        $rewardData = $this->setupGet($serviceUrl);
        return $rewardData;
    }

    /**
     * @param $customerId
     * @return array
     */
    public function getRewardSummary($customerId)
    {
        $serviceUrl = $this->url . $this->magento . $customerId . $this->reward . $this->summary;
        $rewardData = $this->setupGet($serviceUrl);
        return $rewardData;
    }

    /**
     * @param $customerId
     * @return mixed
     */
    public function getRewardBalance($customerId)
    {
        $rewardData = $this->getRewardSummary($customerId);
        $rewardBalance = $rewardData['curl values']['RewardDollarAvailable'];
        return $rewardBalance;
    }

    /**
     * @param $customerId
     * @return array
     */
    public function getTransactions($customerId)
    {
        $serviceUrl = $this->url . $this->magento . $customerId . $this->transaction;
        $transactionData = $this->setupGet($serviceUrl);
        return $transactionData;
    }

    /**
     * @param $customerId
     * @param $redeemId
     * @param $amount
     * @return array
     */
    public function putRewardRedeem($customerId, $redeemId, $amount)
    {
        $serviceUrl = $this->url . $this->magento . $customerId .  $this->reward . $this->redeem  . "/" . $redeemId;

        $arrayData = [
            'Dollars' => (int)$amount,
        ];

        $data = json_encode($arrayData);

        $response = $this->setupPut($serviceUrl, $data);
        return $response;
    }

    /**
     * @param $customerId
     * @param $redeemId
     * @param $amount
     * @param $orderId
     * @return array
     */
    public function putRewardFinal($customerId, $redeemId, $amount, $orderId)
    {
        $serviceUrl = $this->url . $this->magento . $customerId .  $this->reward . $this->redeem  . "/" . $redeemId;

        $amount = (int)$amount;
        $arrayData = [
            'Dollars' => $amount,
            'Redeemed' => true,
            'OrderId' => $orderId,
        ];

        $data = json_encode($arrayData);

        $response = $this->setupPut($serviceUrl, $data);
        return $response;
    }

    /**
     * @param $customerId
     * @return array
     */
    public function putPersonData($customerId)
    {
        $serviceUrl = $this->url . $this->magento . $customerId . $this->person;
        $data = Mage::helper('unleaded_jrewards/json')->personData($customerId);
        $response = $this->setupPut($serviceUrl, $data);
        return $response;
    }

    /**
     * @param $customer
     * @return array
     */
    public function postVerify($customer)
    {
        $verificationId = $customer->getData('jrewards_verify_code');

        if($verificationId)
        {
            return $this->postVerifyId($customer, $verificationId);
        } else {
            $serviceUrl = $this->url . $this->verify;
            $data = Mage::helper('unleaded_jrewards/json')->verificationData($customer);
            $verificationData = $this->setupPost($serviceUrl, $data);
            return $verificationData;
        }
    }

    /**
     * @param $customer
     * @param $verificationId
     * @return array
     */
    public function postVerifyId($customer, $verificationId)
    {
        $serviceUrl = $this->url . $this->verify . $verificationId;
        $data = Mage::helper('unleaded_jrewards/json')->verificationData($customer);
        $verificationData = $this->setupPost($serviceUrl, $data);
        return $verificationData;
    }

    /**
     * @param $customerId
     * @param $amount
     * @return array
     */
    public function postRewardsRedeem($customerId, $amount)
    {
        $serviceUrl = $this->url . $this->magento . $customerId . $this->reward . $this->redeem;

        $arrayData = [
            'Dollars' => $amount,
            'Redeemed' => false,
        ];
        $data = json_encode($arrayData);

        $redeemData = $this->setupPost($serviceUrl, $data);
        return $redeemData;
    }

    /**
     * @param $data
     * @return array
     */
    public function postTransactionData($data)
    {
        $serviceUrl = $this->transURL;
        $postResponse = $this->setupAuthPost($serviceUrl, $data);
        return $postResponse;
    }

    /**
     * @param $customer
     */
    public function putLoyaltyStatus($customer)
    {
        $customerId = $customer->getId();
        $customerEmail = $customer->getData('email');
        $news = false;
        if($customerEmail)
        {
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customerEmail)->getSubscriberStatus();
            if($subscriber == 1){
                $news = true;
            }
        }

        $serviceUrl = $this->url . $this->magento . $customerId . $this->loyalty . $this->status;
        $putResponse = $this->setupGet($serviceUrl);
        $text = false;

        if($customer->getData('jrewards_verify_code') == 1)
        {
            $text = true;
        }
        if($customer->getIsSubscribed() == 1)
        {
            $news = true;
        }

        $data = [
            "Status"             => $putResponse['curl values']['Status'],
            "ReasonCode"         => $putResponse['curl values']['ReasonCode'],
            "DeactivatedDate"    => $putResponse['curl values']['DeactivatedDate'],
            "LoyaltyIneligible"  => $putResponse['curl values']['LoyaltyIneligible'],
            "ActiveFlag"         => $putResponse['curl values']['ActiveFlag'],
            "SignupSource"       => 'Web',
            "ReferalID"          => $putResponse['curl values']['ReferalID'],
            "OptOut"             => $putResponse['curl values']['OptOut'],
            "EmailAccepted"      => $news,
            "TextAccepted"       => $text,
        ];


        $returnData = json_encode($data);

        $this->setupPut($serviceUrl, $returnData);

    }

}