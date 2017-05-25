<?php
/**
 * Diglin GmbH
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php

 *
 * @category    Diglin
 * @package     Diglin_Username
 * @copyright   Copyright (c) 2008-2015 Diglin GmbH - Switzerland (http://www.diglin.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once 'Mage/Customer/controllers/AccountController.php';

class Diglin_Username_AccountController extends Mage_Customer_AccountController
{
    /**
     * Rewrite to allow support of Username
     *
     */
    public function forgotPasswordPostAction()
    {
        $email = (string) $this->getRequest()->getPost('email');
        if ($email) {

            /** @var $customer Diglin_Username_Model_Customer */
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByUsername($email);

            if (!$customer->getId() && !Zend_Validate::is($email, 'EmailAddress')) {
                $this->_getSession()->setForgottenEmail($email);
                $this->_getSession()->addError($this->__('Invalid email address.'));
                $this->_redirect('*/*/forgotpassword');
                return;
            } else if (!$customer->getId()) {
                // Load by Email if username not found and email seems to be valid
                $customer
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByEmail($email);
            }

            if ($customer->getId()) {
                try {
                    $newResetPasswordLinkToken = $this->_getHelper('customer')->generateResetPasswordLinkToken();
                    $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                    $customer->sendPasswordResetConfirmationEmail();
                } catch (Exception $exception) {
                    $this->_getSession()->addError($exception->getMessage());
                    $this->_redirect('*/*/forgotpassword');
                    return;
                }
            }
            $this->_getSession()
                ->addSuccess( $this->_getHelper('customer')
                    ->__('If there is an account associated with %s you will receive an email with a link to reset your password.',
                        $this->_getHelper('customer')->escapeHtml($email)));
            $this->_redirect('*/*/');
            return;
        } else {
            $this->_getSession()->addError(Mage::helper('username')->__('Please enter your email or username.'));
            $this->_redirect('*/*/forgotpassword');
            return;
        }
    }
    
    
    
    /**
     * Create customer account action
     */
    public function createPostAction()
    {
    	$errUrl = $this->_getUrl('*/*/create', array('_secure' => true));
    
    	if (!$this->_validateFormKey()) {
    		$this->_redirectError($errUrl);
    		return;
    	}
    
    	/** @var $session Mage_Customer_Model_Session */
    	$session = $this->_getSession();
    	if ($session->isLoggedIn()) {
    		$this->_redirect('*/*/');
    		return;
    	}
    
    	if (!$this->getRequest()->isPost()) {
    		$this->_redirectError($errUrl);
    		return;
    	}
    
    	$customer = $this->_getCustomer();
    
    	try {
    		$errors = $this->_getCustomerErrors($customer);
    		if (empty($errors)) {
    			$customer->cleanPasswordsValidationData();
    			$customer->save();
    			$this->_dispatchRegisterSuccess($customer);
    			$this->_successProcessRegistration($customer);
    			return;
    		} else {
    			$this->_addSessionError($errors);
    		}
    	} catch (Mage_Core_Exception $e) {
    		$session->setCustomerFormData($this->getRequest()->getPost());
    		if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
    			$url = $this->_getUrl('customer/account/forgotpassword');
    			$message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
    		} else {
    			$message = $this->_escapeHtml($e->getMessage());
    		}
    		$session->addError($message);
    	} catch (Exception $e) {
    		$session->setCustomerFormData($this->getRequest()->getPost());
    		$session->addException($e, $this->__('Cannot save the customer.'));
    	}
    
    	$this->_redirectError($errUrl);
    }
    
    /**
     * Validate customer data and return errors if they are
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return array|string
     */
    protected function _getCustomerErrors($customer)
    {

    	
    	$errors = array();
    	$request = $this->getRequest();
    	if ($request->getPost('create_address')) {
    		$errors = $this->_getErrorsOnCustomerAddress($customer);
    	}
    	$customerForm = $this->_getCustomerForm($customer);
    	$customerData = $customerForm->extractData($request);
    	
    	#echo "<pre>"; print_r($customerData['username']); echo "</pre>"; die();
    	$customerErrors = $customerForm->validateData($customerData);
    	if ($customerErrors !== true) {
    		$errors = array_merge($customerErrors, $errors);
    	} elseif ($this->_countLength($customerData['username'])){
    		#$errors[] = 'Minium 5 and Maximum 10 alpha/numeric characters required.';
    		$errors = array_merge(['Username Minium 5 and Maximum 10 alpha/numeric characters required.'], $errors);
    	} elseif ($this->_countDots($customerData['username'])){
    		#$errors[] = 'Only accommodate alpha/numeric characters.';
    		$errors = array_merge(['Username Only accommodate alpha/numeric characters.'], $errors);
    	} else {
    		$customerForm->compactData($customerData);
    		$customer->setPassword($request->getPost('password'));
    		$customer->setPasswordConfirmation($request->getPost('confirmation'));
    		$customerErrors = $customer->validate();
    		if (is_array($customerErrors)) {
    			$errors = array_merge($customerErrors, $errors);
    		}
    	}
    	return $errors;
    }
    
    private function _countDots($subDomainName){
    
    	if (is_numeric($subDomainName[0])){
    		return true;
    	} elseif (preg_match('/[\'^£$%&*()} {@#~?><>,.|=_+¬-]/', $subDomainName)){
    		return true;
    	}
    	return false;
    
    }
    
    
    private function _countLength($subDomainName){
    	if ((strlen($subDomainName) < 5) || (strlen($subDomainName) > 10)){
    		return true;
    	}
    	return false;
    
    }
}
