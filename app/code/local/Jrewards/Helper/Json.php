<?php
/**
 * Company: Unleaded Group.
 * Developer: Jason Roy
 * Email: Jason@UnleadedGroup.com
 * Module: Unleaded_Jrewards
 */
 

class Unleaded_Jrewards_Helper_Json
{
    /**
     * @param $item
     * @return null
     */
    private function _arrayChange($item)
    {
        $returned = null;
        foreach ($item as $value)
        {
            $returned = $value;
        }
        return $returned;
    }

    /**
     * @param $string
     * @return mixed
     */
    private function _firstLetter($string)
    {
        $returned = $string[0];
        return $returned;
    }

    /**
     * @param $string
     * @return string
     */
    public function _notNull($string)
    {
        if($string == null)
        {
            return '';
        } elseif($string == false) {
            return '';
        }
        else {
            return $string;
        }
    }

    /**
     * @param $customer
     * @return string
     */
    public function dateOfBirth($customer)
    {
        $preDob = new DateTime($customer->getDob());
        return $dob = $preDob->format( 'Y-m-d');
    }

    /**
     * @param $customerId
     * @return string
     */
    public function personData($customerId)
    {
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $middleInitial = '';

        if($customer->getDefaultBillingAddress())
        {
            $_nestedBillingAddress = $customer->getDefaultBillingAddress()->getStreet();
            $billingAddress = '';
            foreach ($_nestedBillingAddress as $street){
                $billingAddress = $billingAddress . ' '. $street;
            }
            $telephone      = $customer->getDefaultBillingAddress()->getTelephone();
            $cityBilling    = $customer->getDefaultBillingAddress()->getCity();
            $stateBilling   = $customer->getDefaultBillingAddress()->getRegion();
            $zipcodeBilling = $customer->getDefaultBillingAddress()->getPostcode();
            $countryBilling = $customer->getDefaultBillingAddress()->getCountry_id();
        }

        if($customer->getDefaultShippingAddress())
        {
            $_nestedShippingAddress = $customer->getDefaultShippingAddress()->getStreet();
            $shippingAddress = '';
            foreach ($_nestedShippingAddress as $street){
                $shippingAddress = $shippingAddress . ' '. $street;
            }
            $cityShipping    = $customer->getDefaultShippingAddress()->getCity();
            $stateShipping   = $customer->getDefaultShippingAddress()->getRegion();
            $zipcodeShipping = $customer->getDefaultShippingAddress()->getPostcode();
            $countryShipping = $customer->getDefaultShippingAddress()->getCountry_id();
        }

        if($customer->getMiddlename())
        {
            $middleName = $customer->getMiddlename();
            $middleInitial = $this->_firstLetter($middleName);
        }

        if(!($telephone) && $customer->getData('jrewards_home_number'))
        {
            $telephone = $customer->getData('jrewards_home_number');
        }

        if($this->dateOfBirth($customer)){
            $dob = $this->dateOfBirth($customer);
        } else {
            $dob = null;
        }

        $gender = $customer->getGender();

        if(null != $gender)
        {
            if(1 == $gender) {
                $gender = 'Mens';
            } elseif(2 == $gender) {
                $gender = 'Womens';
            }
        }

        $preUpdatedAt = new DateTime($customer->getUpdatedAt());
        $updatedAt = $preUpdatedAt->format('Y-m-d\Th:m:s\Z');

        $person = [
            'FirstName'     => $this->_notNull($customer->getFirstname()),
            'MiddleInitial' => $this->_notNull($middleInitial),
            'LastName'      => $this->_notNull($customer->getLastname()),
            'HomePhone'     => $this->_notNull($telephone),
            'MobilePhone'   => $this->_notNull($customer->getData('jrewards_cell_number')),
            'DateOfBirth'   => $this->_notNull($dob),
            'Email' => [
                $this->_notNull($customer->getEmail()),
                $this->_notNull($customer->getData('jrewards_secondary_email'))
            ],
            'BillingAddress' => [
                [
                    'Address1' => $this->_notNull($billingAddress),
                    'Address2' => $this->_notNull(null),
                    'City'     => $this->_notNull($cityBilling),
                    'State'    => $this->_notNull($stateBilling),
                    'ZipCode'  => $this->_notNull($zipcodeBilling),
                    'Country'  => $this->_notNull($countryBilling),
                ]
            ],
            'ShippingAddress'  => [
                [
                    'Address1' => $this->_notNull($shippingAddress),
                    'Address2' => $this->_notNull(null),
                    'City'     => $this->_notNull($cityShipping),
                    'State'    => $this->_notNull($stateShipping),
                    'ZipCode'  => $this->_notNull($zipcodeShipping),
                    'Country'  => $this->_notNull($countryShipping),
                ]
            ],
            'ProductPreference'   => $this->_notNull($gender),
            'LastAddrUpdatedDate' =>  $this->_notNull($updatedAt),
        ];

        return json_encode($person);
    }

    /**
     * @param $customer
     * @return string
     */
    public function verificationData($customer)
    {
        $data = [
            "Email" => $customer->getEmail(),
            "SecondaryEmails" => [
                $this->_notNull($customer->getData('jrewards_secondary_email')),
            ],
            "SignupSource" => 'Web',
            "MagentoAccount" => $customer->getId(),
            "FirstName" => $customer->getFirstname(),
            "LastName" => $customer->getLastname(),
            "DOB" => $this->_notNull($this->dateOfBirth($customer)),
        ];

        return $data = json_encode($data);
    }

    /**
     * @param $customer
     * @return string
     */
    public function secondaryData($customer)
    {
        if($customer->getData('jrewards_secondary_email')) {
            $data = [
                "Primary" => false,
                "Email" => $customer->getEmail(),
                "PrimaryEmail" => $customer->getEmail(),
                "SecondaryEmails" => [
                    $this->_notNull($customer->getData('jrewards_secondary_email')),
                ],
                "SignupSource" => 'Web',
                "MagentoAccount" => $customer->getId(),
                "FirstName" => $customer->getFirstname(),
                "LastName" => $customer->getLastname(),
                "DOB" => $this->_notNull($this->dateOfBirth($customer)),
            ];
            return $data = json_encode($data);
        }

        $data = [
            "Primary" => false,
            "Email" => $customer->getEmail(),
            "PrimaryEmail" => $customer->getEmail(),
            "SignupSource" => 'Web',
            "MagentoAccount" => $customer->getId(),
            "FirstName" => $customer->getFirstname(),
            "LastName" => $customer->getLastname(),
            "DOB" => $this->_notNull($this->dateOfBirth($customer)),
        ];

        return $data = json_encode($data);
    }

}

/**
 * Bibliography
 * http://stackoverflow.com/questions/18311762/magento-how-have-i-to-get-all-the-transaction-payment-items-of-a-specific-order
 */