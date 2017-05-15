<?php
/**
 * Created by PhpStorm.
 * User: julfiker
 * Date: 5/14/17
 * Time: 12:36 AM
 */
class Julfiker_Party_CustomerController extends Mage_Core_Controller_Front_Action
{

    public function createAction() {
        //Todo: create form action
    }

    /**
     * Create customer account action
     */
    public function createPostAction()
    {
        $websiteId = Mage::app()->getWebsite()->getId();
        $store = Mage::app()->getStore();
        $request = $this->getRequest();
        $data = array();
        $y = $request->getPost('year');
        $m = $request->getPost('month');
        $d = $request->getPost('day');
        $date = date('m/j/Y', strtotime("$y-$m-$d"));

        $customer = Mage::getModel("customer/customer");
        $customer   ->setWebsiteId($websiteId)
            ->setStore($store)
            ->setFirstname($request->getPost('firstname'))
            ->setLastname($request->getPost('lastname'))
            ->setMiddleName($request->getPost('middlename'))
            ->setEmail($request->getPost('email'))
            ->setDob($date)
            ->setUsername($request->getPost('username'))
            ->setPassword($request->getPost('password3'));
        try{
            $customer->save();
            $this->createAddress($customer);
            if ($this->getRequest()->get('type') == "json") {
                $data = array(
                    "error" => false,
                    "id" => $customer->getId(),
                    "name" => $customer->getName()
                );
            }
            else {
                Mage::getSingleton('customer/session')->addSuccess(Mage::helper('julfiker_party')->__('Member was added successfully!'));
            }
        }
        catch (Exception $e) {
          if ($this->getRequest()->get('type') == "json") {
              $data = array("error" => true, "message" => $e->getMessage());
          }
          else {
              Mage::getSingleton('customer/session')->addError($e->getMessage());
          }
        }

        if ($data) {
            $this->getResponse()->clearHeaders()->setHeader(
                'Content-type',
                'application/json'
            );
            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode($data)
            );
        }
        else {
            $this->_redirectReferer();
        }
    }

    public function addressPostAction() {
        $id = $this->getRequest()->get('id');
        if ($id) {
            $customer = Mage::getModel("customer/customer")->load($id);
            $address = $this->createAddress($customer);
            $street = $address->getStreet();
            $data['id'] = $address->getId();
            $data['location'] = $street[0].", ".$address->getCity().", ".$address->getPostcode().", ".$address->getCountry();
        }
        else {
            $data = array("error" => true, "message" => "Something went wrong!");
        }
        $this->getResponse()->clearHeaders()->setHeader(
            'Content-type',
            'application/json'
        );
        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($data)
        );
    }

    protected function createAddress($customer) {
        $request = $this->getRequest();

        $address = Mage::getModel("customer/address");
        $address->setCustomerId($customer->getId())
            ->setFirstname($customer->getFirstname())
            ->setMiddleName($customer->getMiddlename())
            ->setLastname($customer->getLastname())
            ->setCountryId($request->getPost('country_id'))
            ->setPostcode($request->getPost('postcode'))
            ->setCity($request->getPost('city'))
            ->setTelephone($request->getPost('telephone'))
            ->setFax($request->getPost('fax'))
            ->setCompany($request->getPost('company'))
            ->setStreet($request->getPost('street'))
            ->setIsDefaultBilling('1')
            ->setIsDefaultShipping('1')
            ->setSaveInAddressBook('1');

        if ($request->getPost('country_id') == "us")
            $address->setRegionId($request->getPost('region_id'));
            try{
            $address->save();
        }
        catch (Exception $e) {
            echo Zend_Debug::dump($e->getMessage());
            return false;
        }
        return $address;
    }
}