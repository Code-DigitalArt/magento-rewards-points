<?php
/**
 * Company: Unleaded Group.
 * Developer: Jason Roy
 * Email: Jason@UnleadedGroup.com
 * Module: Unleaded_Jrewards
 */
 
class Unleaded_Jrewards_CustomerController extends Mage_Core_Controller_Front_Action
{
    /**
     * @return Mage_Core_Model_Abstract|Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * @return mixed
     */
    protected function _getCustomerId()
    {
        $customer = $this->_getSession();
        $customerId = $customer->getId();;
        return $customerId;
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _getCustomer()
    {
        $customerId = $this->_getCustomerId();
        return Mage::getModel('customer/customer')->load($customerId);
    }

    public function registerAction()
    {
        $customer = $this->_getCustomer();

        $request = [
            'agreement' => Mage::app()->getRequest()->getParam('jackrabbit-signup'),
            'secondary_email' => Mage::app()->getRequest()->getParam('secondary-email'),
            'cell_phone' => Mage::app()->getRequest()->getParam('cell-phone'),
            'text_messaging' => Mage::app()->getRequest()->getParam('text-messaging'),
            'preference' => Mage::app()->getRequest()->getParam('gender'),
            'home_phone' => Mage::app()->getRequest()->getParam('home-phone')
        ];

        Mage::helper('unleaded_jrewards/data')->registerUser($customer, $request);
        Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('customer/account'));
    }

    public function discountAction()
    {
//        Mage::dispatchEvent('sales_quote_collect_totals_after');
        $html = $this->getLayout()
            ->createBlock('onestepcheckout/summary')
            ->setTemplate('onestepcheckout/summary.phtml')
            ->toHtml();

        $response['summary'] = $html;
        $this->getResponse()->setBody(Zend_Json::encode($response));

        Mage::dispatchEvent('reward_redeem_post');
    }

    public function resendEmailAction()
    {
        $customer = $this->_getCustomer();
        Mage::helper('unleaded_jrewards/api')->postVerify($customer);
        $this->_redirect('customer/account/login');
    }

    public function emailAction()
    {
        $customer_email = Mage::getModel('core/cookie')->get('JREmail');
        $verification_code = Mage::getModel('core/cookie')->get('JRVerification');

        $customer = $this->_getCustomer();
        $CEmail = $customer->getEmail();

        if(Mage::getSingleton('customer/session')->isLoggedIn())
        {
            if($customer_email == $CEmail)
            {
                if($verification_code)
                {
                    $customer->setData('jrewards_verify_code', $verification_code);
                    $customer->getResource()->saveAttribute($customer,'jrewards_verify_code');

                    Mage::helper('unleaded_jrewards/api')->postVerify($customer);
                    $this->_redirect('customer/account/login');
                }
            } else if($customer_email == $customer->getData('jrewards_secondary_email')) {
                $customer->setData('jrewards_verify_code', $verification_code);
                $customer->getResource()->saveAttribute($customer,'jrewards_verify_code');

                Mage::helper('unleaded_jrewards/api')->postVerifySecondary($customer);
                $this->_redirect('customer/account/login');
            } else {
                $this->_redirect('customer/account/logout');
            }
        } else {
            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
            $customer->loadByEmail($customer_email);

            if($customer->getId())
            {
                $this->_redirect('customer/account/login');
            } else {
                $this->_redirect('customer/account/create');
            }
        }

    }

}