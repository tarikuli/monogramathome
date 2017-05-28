<?php
/**
 * First Data TransArmor integration - Payment model
 *
 * Paradox Labs, Inc.
 * http://www.paradoxlabs.com
 * 717-431-3330
 *
 * Having a problem with the plugin?
 * Not sure what something means?
 * Need custom development?
 * Give us a call!
 *
 * @category	ParadoxLabs
 * @package		ParadoxLabs_Transarmor
 * @author		Ryan Hoerr <ryan@paradoxlabs.com>
 */

// require_once('transarmor.class.php');

// error_reporting(E_ERROR | E_WARNING | E_PARSE);
// Mage::log('Loaded transarmor module: '.date('m-d-Y h:i:s-u'), null, 'firstdata.log');

class ParadoxLabs_Transarmor_Model_Payment extends Mage_Payment_Model_Method_Cc
	implements Mage_Payment_Model_Recurring_Profile_MethodInterface
{
	protected $_formBlockType			= 'transarmor/form';
	protected $_code					= 'transarmor';
	protected $_debug					= false;
	protected $_admin					= false;
	
	// Can-dos
	protected $_isGateway				= false;
	protected $_canAuthorize			= true;
	protected $_canCapture				= true;
	protected $_canCapturePartial		= true;
	protected $_canRefund				= true;
	protected $_canRefundInvoicePartial = true;
	protected $_canVoid					= true;
	protected $_canUseInternal			= true;
	protected $_canUseCheckout			= true;
	protected $_canUseForMultishipping	= true;
	protected $_canSaveCc				= false;
	protected $_canReviewPayment		= true;
	protected $_canCancelInvoice		= true;
	protected $_canManageRecurringProfiles = true;
	
	protected $api;
	protected $_invoice					= null;
	
	protected $errors = array(
		'Invalid Logon' => 'This payment gateway is not configured correctly. Please try an alternate payment method.',
	);
	
	/**
	 * Initialize transarmor class and other data.
	 */
	public function __construct() {
		if( Mage::app()->getStore()->isAdmin() ) {
			$this->_admin = true;
		}
		
		if( $this->_admin && Mage::registry('current_order') != false ) {
			$this->setStore( Mage::registry('current_order')->getStoreId() );
		}
		elseif( $this->_admin && Mage::registry('current_invoice') != false ) {
			$this->setStore( Mage::registry('current_invoice')->getStoreId() );
		}
		elseif( $this->_admin && Mage::registry('current_creditmemo') != false ) {
			$this->setStore( Mage::registry('current_creditmemo')->getStoreId() );
		}
		elseif( $this->_admin && Mage::registry('current_customer') != false ) {
			$this->setStore( Mage::registry('current_customer')->getStoreId() );
		}
		elseif( $this->_admin && Mage::getSingleton('adminhtml/session_quote')->getStoreId() > 0 ) {
			$this->setStore( Mage::getSingleton('adminhtml/session_quote')->getStoreId() );
		}
		else {
			$this->setStore( Mage::app()->getStore()->getId() );
		}
		
		return $this;
	}
	
	/**
	 * Set the payment config scope and reinitialize the API
	 */
	public function setStore( $id ) {
		$this->_storeId = $id;
		
		$this->initializeApi( true );
		
		return $this;
	}
	
	/**
	 * Set the customer to use for payment/card operations.
	 */
	public function setCustomer( $customer ) {
		$this->_customer = $customer;
		
		return $this;
	}
	
	/**
	 * Fetch a setting for the current store scope.
	 */
	public function getConfigData( $field, $storeId=null ) {
		if( is_null( $storeId ) ) {
			$storeId = $this->_storeId;
		}
		
		return Mage::getStoreConfig( 'payment/' . $this->getCode() . '/' . $field, $storeId );
	}
	
	/**
	 * Get the current customer; fetch from session if necessary.
	 */
	public function getCustomer() {
		if( isset( $this->_customer ) ) {
			$customer = $this->_customer;
		}
		elseif( $this->_admin ) {
			$customer = Mage::getModel('customer/customer')->load( Mage::getSingleton('adminhtml/session_quote')->getCustomerId() );
		}
		else {
			$customer = Mage::getSingleton('customer/session')->getCustomer();
		}
		
		$this->setCustomer( $customer );
		
		return $customer;
	}
	
	/**
	 * Initialize the API gateway class. 'force' will reinitialize
	 * in the current config scope.
	 */
	protected function initializeApi( $force=false ) {
		if( $force === true ) {
			$this->api = null;
		}
		
		if( is_null( $this->api ) ) {
			$this->_debug = $this->getConfigData('debug');
			
			$this->api = Mage::getModel('transarmor/api')->init(	$this->getConfigData('login'),
																	$this->getConfigData('password'),
																	$this->getConfigData('trans_key'),
																	$this->getConfigData('test') );
		}
		
		return $this;
	}

	/**
	 * Daily CRON: Iterate through recurring profiles and create
	 * any orders/invoices necessary.
	 */
	public function runDailyBilling() {
		if( $this->_debug ) Mage::log('runDailyBilling()', null, 'firstdata.log');
		
		/**
		 * Fetch active and pending profiles.
		 */
		$db			= Mage::getSingleton('core/resource')->getConnection('core_read');
		$rp_table	= Mage::getSingleton('core/resource')->getTableName('sales/recurring_profile');
		$sql		= $db->select()
						 ->from( $rp_table, array('internal_reference_id') )
						 ->where( 'method_code="transarmor" AND ( state="active" OR (state="pending" and start_datetime < NOW()) )' );
		$data		= $db->fetchAll($sql);
		
		$processed = 0;
		if( count($data) > 0 ) {
			foreach( $data as $pid ) {
				$profile	= Mage::getModel('sales/recurring_profile')->loadByInternalReferenceId( $pid['internal_reference_id'] );
				$refId		= $profile->getReferenceId();
				$adtl		= $profile->getAdditionalInfo();
				$cid		= $profile->getCustomerId();
				
				$this->setStore( $profile->getStoreId() );
				if( $this->getConfigData( 'active' ) == 0 || ( !is_null( $cid ) && Mage::getModel('customer/customer')->load( $cid )->getId() != $cid ) ) {
					continue;
				}
				
				/**
				 * For each active profile...
				 * if it is a billing cycle beyond starting date...
				 * if it is due to be paid OR if there's a balance outstanding...
				 * create an order/invoice and log the results.
				 */
				if( isset($adtl['next_cycle']) && $adtl['next_cycle'] <= time() ) {
					$processed++;
					
					/**
					 * Are we in a trial period?
					 */
					if( isset($adtl['in_trial']) && $adtl['in_trial'] ) {
						if( $adtl['billing_count'] >= $profile->getTrialPeriodMaxCycles() - 1 )
							$adtl['in_trial'] = false;
						
						$price		= $profile->getTrialBillingAmount();
					}
					else {
						$price		= $profile->getBillingAmount();
					}
					
					/**
					 * Is there an outstanding bill?
					 */
					if( isset($adtl['outstanding']) && $adtl['outstanding'] > 0 ) {
						$price += $adtl['outstanding'];
					}
					
					/**
					 * Do we need to bill? If so, do it.
					 */
					$billed = 0;
					$success = false;
					if( $price > 0 ) {
						try {
							/**
							 * Try to generate an order and invoice.
							 */
							$productItemInfo = new Varien_Object;

							if( $adtl['billed_count'] < $profile->getTrialPeriodMaxCycles() ) {
								$productItemInfo->setPaymentType(Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_TRIAL);
							}
							else {
								$productItemInfo->setPaymentType(Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_REGULAR);
							}

							$productItemInfo->setTaxAmount( $profile->getTaxAmount() );
							$productItemInfo->setShippingAmount( $profile->getShippingAmount() );
							$productItemInfo->setPrice( $price );

							$order = $profile->createOrder( $productItemInfo );
							
							if( is_null( $cid ) ) {
								$order->setCustomerId( null );
							}
							
							$order->setExtCustomerId( $adtl['payment_id'] )
								  ->save();

							$profile->addOrderRelation( $order->getId() );
							
							// Handle events and such
							$transaction = Mage::getModel('core/resource_transaction');
							$_customer   = Mage::getModel('customer/customer')->load( $profile->getCustomerId() );
							if( $_customer && $_customer->getId() ) {
								$transaction->addObject($_customer);
							}
							$transaction->addObject($order);
							$transaction->addCommitCallback(array($order, 'place'));
							$transaction->addCommitCallback(array($order, 'save'));
							$transaction->save();
							
							if( $order->getCanSendNewEmailFlag() ) {
								try {
									$order->sendNewOrderEmail();
								}
								catch(Exception $e) {
									Mage::logException($e->getMessage());
								}
							}
							// End events and such

							$adtl['outstanding'] = 0;
							$adtl['billed_count']++;
							$success = true;
							
							Mage::dispatchEvent( 'recurring_profile_billed', array( 'order' => $order, 'profile' => $profile ) );
							
							/**
							 * Is the profile complete?
							 */
							$max_cycles = intval($profile->getPeriodMaxCycles());
							if( $max_cycles > 0 && $adtl['billed_count'] == $max_cycles + intval($profile->getTrialPeriodMaxCycles()) ) {
								$profile->setState( Mage_Sales_Model_Recurring_Profile::STATE_EXPIRED );
							}
						}
						catch(Mage_Core_Exception $e) {
							/**
							 * Payment failed; handle the error.
							 */
							$adtl['failure_count']++;
							
							Mage::dispatchEvent( 'recurring_profile_failed', array( 'profile' => $profile ) );
							
							if( $profile->getSuspensionThreshold() != null && $adtl['failure_count'] >= $profile->getSuspensionThreshold() ) {
								$profile->setAdditionalInfo( $adtl )->setState( Mage_Sales_Model_Recurring_Profile::STATE_CANCELED )->save();
								Mage::log( "Recurring profile #{$refId} failed on payment ({$adtl['failure_count']}/{$profile->getSuspensionThreshold()}); profile terminated.", null, 'firstdata.log', true );
								Mage::log( $e->getMessage(), null, 'firstdata.log', true );
								continue;
							}
							else {
								if( $profile->getBillFailedLater() )
									$adtl['outstanding'] = ($price + $profile->getTaxAmount() + $profile->getShippingAmount());
								
								Mage::log( "Recurring profile #{$refId} failed on payment ({$adtl['failure_count']}/{$profile->getSuspensionThreshold()}).", null, 'firstdata.log', true );
								Mage::log( $e->getMessage(), null, 'firstdata.log', true );
							}
						}
						
						/**
						 * Log the billing sequence.
						 */
						$billed = $success ? round($price + $profile->getTaxAmount() + $profile->getShippingAmount(), 2) : 0;
						$adtl['billing_log'][] = array(	'date'		=> time(),
														'amount'	=> $billed,
														'success'	=> $success );
						$adtl['last_bill'] = time();
					}
					
					if( $profile->getState() == 'pending' ) {
						$profile->setState( Mage_Sales_Model_Recurring_Profile::STATE_ACTIVE );
					}

					/**
					 * Save payment details with the profile for later use.
					 */
					if( $adtl['in_trial'] ) {
						if( $profile->getTrialPeriodUnit() == 'semi_month' )
							$adtl['next_cycle'] = strtotime( '+'.($profile->getTrialPeriodFrequency() * 2).' weeks' );
						else
							$adtl['next_cycle'] = strtotime( '+'.$profile->getTrialPeriodFrequency().' '.$profile->getTrialPeriodUnit() );
					}
					else {
						if( $profile->getPeriodUnit() == 'semi_month' )
							$adtl['next_cycle'] = strtotime( '+'.($profile->getPeriodFrequency() * 2).' weeks' );
						else
							$adtl['next_cycle'] = strtotime( '+'.$profile->getPeriodFrequency().' '.$profile->getPeriodUnit() );
					}

					$profile->setAdditionalInfo( $adtl )->save();

					if( $success ) {
						Mage::log( "Recurring profile #{$refId} billed for $${billed}.", null, 'firstdata.log' );
					}
				}
			}
		}
		
		Mage::log( "CRON: Processed {$processed} recurring profiles.", null, 'firstdata.log' );
	}
	
	/**
	 * Update the CC info during the checkout process.
	 */
	public function assignData( $data ) {
		parent::assignData( $data );
		
		$post = Mage::app()->getRequest()->getParam('payment');
		
		if( !empty( $post['payment_id'] ) ) {
			$card = $this->getPaymentInfoById( $post['payment_id'] );
			
			if( $card->getId() > 0 && $card->getCustomerId() == intval( Mage::getSingleton('customer/session')->getCustomer()->getId() ) ) {
				$this->getInfoInstance()->setCcLast4( $card->getLast4() )
										->setCcType( $card->getType() );
			}
		}
		
		return $this;
	}
	
	/**
	 * Validate the transaction inputs.
	 */
	public function validate() {
		if( $this->_debug ) Mage::log('validate()', null, 'firstdata.log');
		
		$post = Mage::app()->getRequest()->getParam('payment');
		
		if( empty($post['payment_id']) || !empty($post['cc_number']) ) {
			try {
				return parent::validate();
			}
			catch(Exception $e) {
				return $e;
			}
		}
		else {
			return true;
		}
	}

	/**
	 * Authorize a transaction
	 */
	public function authorize(Varien_Object $payment, $amount) {
		if( $this->_debug ) Mage::log('authorize()', null, 'firstdata.log');
		
		$post = Mage::app()->getRequest()->getParam('payment');
		
		// Set card ID if chosen
		if( !empty($post['payment_id']) && empty($post['cc_number']) ) {
			$payment->getOrder()->setExtCustomerId( intval( $post['payment_id'] ) );
		}
		
		// Set 'save card' checkbox if checked
		if( isset( $post['save_card'] ) && $post['save_card'] == 1 ) {
			$payment->setSaveCard( true );
		}
		
		return $this->bill( $payment, $amount, 'AUTH' );
	}

	/**
	 * Capture a transaction [authorize if necessary]
	 */
	public function capture(Varien_Object $payment, $amount) {
		if( $this->_debug ) Mage::log('capture()', null, 'firstdata.log');
		
		$post = Mage::app()->getRequest()->getParam('payment');
	
		// Set card ID if chosen
		if( !empty($post['payment_id']) && empty($post['cc_number']) ) {
			$payment->getOrder()->setExtCustomerId( intval( $post['payment_id'] ) );
		}
		
		// Set 'save card' checkbox if checked
		if( isset( $post['save_card'] ) && $post['save_card'] == 1 ) {
			$payment->setSaveCard( true );
		}
		
		$trans_id = explode( ':', $payment->getOrder()->getExtOrderId(), 2 );
		$type     = isset($trans_id[1]) && !empty($trans_id[1]) ? 'CAPTURE' : 'SALE';
				
		// Handle partial-invoice with expired auth
		if( $payment->getOrder()->getTotalPaid() > 0 ) {
			$type = 'SALE';
		}
		
		// Grab the invoice in case partial invoicing
		$invoice = Mage::registry('current_invoice');
		if( !is_null( $invoice ) ) {
			$this->_invoice = $invoice;
		}
		
		return $this->bill( $payment, $amount, $type );
	}

	/**
	 * Refund a transaction
	 */
	public function refund(Varien_Object $payment, $amount) {
		if( $this->_debug ) Mage::log('refund()', null, 'firstdata.log');
		
		// Grab the invoice in case partial invoicing
		$creditmemo = Mage::registry('current_creditmemo');
		if( !is_null( $creditmemo ) ) {
			$this->_invoice = $creditmemo->getInvoice();
		}
		
		return $this->bill( $payment, $amount, 'REFUND' );
	}

	/**
	 * Void a payment
	 */
	public function void(Varien_Object $payment) {
		if( $this->_debug ) Mage::log('void()', null, 'firstdata.log');
		
		$trans_id	= explode( ':', $payment->getOrder()->getExtOrderId(), 2 );
		$card		= $this->getPaymentInfoById( $payment->getOrder()->getExtCustomerId() );
		
		$this->api->clearParameters();
		$this->api->setParameter( 'transarmor_token', $trans_id[0] );
		$this->api->setParameter( 'authorization_num', $trans_id[1] );
		$this->api->setName( $card->getFirstname(), $card->getLastname() );
		$this->api->setParameter( 'cc_expiry', $card->getExpiry() );
		$this->api->setParameter( 'credit_card_type', $card->getType() );
		$this->api->setParameter( 'amount', $payment->getAmountAuthorized() );
		$this->api->setParameter( 'transaction_type', 'VOID' );
		$this->api->runTransaction();
		
		$this->checkErrors();
		
		$response = $this->getApiResponse();
		
		$payment->getOrder()->setExtOrderId( $response->getToken() . ':' )
							->save();
		
		$payment->setTransactionId( $response->getToken() )
				->setIsTransactionClosed(1)
				->setAdditionalInformation( $response->getData() )
				->save();
		
		Mage::log( json_encode( $response->getData() ), null, 'firstdata.log', true );
		
		return $this;
	}
	
	/**
	 * Cancel a payment
	 */
	public function cancel(Varien_Object $payment) {
		if( $this->_debug ) Mage::log('cancel()', null, 'firstdata.log');
		
		return $this->void($payment);
	}
	
	/**
	 * Payment method available? Yes.
	 */
	public function isAvailable($quote=null) {
		return (bool)($this->getConfigData('active'));
	}

	/**
	 * Validate a recurring profile order
	 * @see Mage_Payment_Model_Recurring_Profile_MethodInterface::validateRecurringProfile()
	 */
	public function validateRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile) {
		if( $this->_debug ) Mage::log('validateRecurringProfile()', null, 'firstdata.log');
	}

	/**
	 * Submit a recurring profile order
	 * @see Mage_Payment_Model_Recurring_Profile_MethodInterface::submitRecurringProfile()
	 */
	public function submitRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile, Mage_Payment_Model_Info $paymentInfo) {
		if( $this->_debug ) Mage::log('submitRecurringProfile()', null, 'firstdata.log');
		
		$billing_log	= array();
		$is_active		= false;
		$is_trial		= false;
		$init_paid		= false;
		$fail_count		= 0;
		$bill_count		= 0;
		$outstanding	= 0;
		
		/**
		 * Set the reference ID to a nice not-obviously-sequential value.
		 */
		$refId = 1703920 + $profile->getId();
		$profile->setReferenceId( $refId );
		
		/**
		 * Create a profile in case they don't have any.
		 */
		$uid		= is_numeric($profile->getCustomerId()) ? $profile->getCustomerId() : 0;
		$_customer	= Mage::getModel('customer/customer')->load( $uid );

		$post = Mage::app()->getRequest()->getParam('payment');
		if( isset($post) && isset($post['payment_id']) && intval($post['payment_id']) > 0 && empty($post['cc_number']) ) {
			$payment_id = intval( $post['payment_id'] );
		}
		else {
			$payment_id = $this->storeCardRecurring( $_customer, $profile );
		}
		
		/**
		 * Do we need to bill?
		 * Did they set a future start date? If so, skip the order.
		 */
		if( strtotime($profile->getStartDatetime()) <= time() ) {
			$is_active = true;
			
			/**
			 * Is there a separate trial period?
			 */
			if( !is_null($profile->getTrialPeriodUnit()) && $trial_cycles = $profile->getTrialPeriodMaxCycles() ) {
				// Keep shipping/tax as given? [going with Yes]
				if( $trial_cycles > 1 )
					$is_trial	= true;
				
				$price		= $profile->getTrialBillingAmount();
			}
			else {
				$price		= $profile->getBillingAmount();
			}
			
			/**
			 * Is there an initial fee?
			 */
			if( !is_null($profile->getInitAmount()) ) {
				$init_paid	= true;
				$price		+= $profile->getInitAmount();
			}
			
			/**
			 * Do we need to bill? If so, do it.
			 */
			if( $price > 0 ) {
				try {
					/**
					 * Try to generate an order and invoice.
					 */
					$productItemInfo = new Varien_Object;

					if( $is_trial ) {
						$productItemInfo->setPaymentType(Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_TRIAL);
					}
					else {
						$productItemInfo->setPaymentType(Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_REGULAR);
					}

					$productItemInfo->setTaxAmount( $profile->getTaxAmount() );
					$productItemInfo->setShippingAmount( $profile->getShippingAmount() );
					$productItemInfo->setPrice( $price );

					$order = $profile->createOrder( $productItemInfo );
					$order->setExtCustomerId( $payment_id )
						  ->save();

					$profile->addOrderRelation( $order->getId() );
					
					// Handle events and such
					$transaction = Mage::getModel('core/resource_transaction');
					if( $_customer->getId() ) {
						$transaction->addObject($_customer);
					}
					$transaction->addObject($order);
					$transaction->addCommitCallback(array($order, 'place'));
					$transaction->addCommitCallback(array($order, 'save'));
					$transaction->save();
					// End events and such
					
					$bill_count = 1;
					
					Mage::dispatchEvent( 'recurring_profile_billed', array( 'order' => $order, 'profile' => $profile ) );
				
					/**
					 * Is the profile complete?
					 * I don't know why we would have a one-unit
					 * recurring profile, but hey... not my problem.
					 */
					$max_cycles = intval($profile->getPeriodMaxCycles());
					if( $max_cycles > 0 && $max_cycles + intval($profile->getTrialPeriodMaxCycles()) == 1 ) {
						$profile->setState( Mage_Sales_Model_Recurring_Profile::STATE_EXPIRED );
					}
				}
				catch(Exception $e) {
					/**
					 * Payment failed; handle the error.
					 */
					$fail_count		= 1;
					
					Mage::dispatchEvent( 'recurring_profile_failed', array( 'profile' => $profile ) );
				
					if( !$profile->getInitMayFail() || $profile->getSuspensionThreshold() === 0 ) {
						$profile->setState( Mage_Sales_Model_Recurring_Profile::STATE_CANCELED )->save();
						Mage::log( "Recurring profile #{$refId} failed on initial payment.", null, 'firstdata.log', true );
						Mage::log( $e->getMessage(), null, 'firstdata.log', true );
						Mage::throwException( $e->getMessage() );
						return;
					}
					else if( $profile->getBillFailedLater() ) {
						$outstanding = ($price + $profile->getTaxAmount() + $profile->getShippingAmount());
						Mage::log( "Recurring profile #{$refId} failed on initial payment; will be retried later.", null, 'firstdata.log', true );
					}
				}
				
				/**
				 * Log the billing sequence.
				 */
				$billing_log[] = array(	'date'		=> time(),
										'amount'	=> round($price + $profile->getTaxAmount() + $profile->getShippingAmount(), 2),
										'success'	=> $bill_count );
				
			}
		}
		
		if( $is_active ) {
			$profile->setState( Mage_Sales_Model_Recurring_Profile::STATE_ACTIVE );
		}
		else {
			$profile->setState( Mage_Sales_Model_Recurring_Profile::STATE_PENDING );
		}

		/**
		 * Save payment details with the profile for later use.
		 */
		$start = $is_active ? time() : strtotime($profile->getStartDatetime());
		if( $is_trial ) {
			if( $profile->getTrialPeriodUnit() == 'semi_month' )
				$next_cycle = strtotime( '+'.($profile->getTrialPeriodFrequency() * 2).' weeks', $start );
			else
				$next_cycle = strtotime( '+'.$profile->getTrialPeriodFrequency().' '.$profile->getTrialPeriodUnit(), $start );
		}
		else {
			if( $profile->getPeriodUnit() == 'semi_month' )
				$next_cycle = strtotime( '+'.($profile->getPeriodFrequency() * 2).' weeks', $start );
			else
				$next_cycle = strtotime( '+'.$profile->getPeriodFrequency().' '.$profile->getPeriodUnit(), $start );
		}
		
		$adtl = array(  'last_bill'		=> time(),
						'next_cycle'	=> $next_cycle,
						'billed_count'	=> $bill_count,
						'failure_count'	=> $fail_count,
						'payment_id'	=> $payment_id,
						'outstanding'	=> $outstanding,
						'init_paid'		=> $init_paid,
						'in_trial' 		=> $is_trial,
						'billing_log'	=> $billing_log );

		$profile->setAdditionalInfo( $adtl )->save();

		Mage::log( "Recurring profile #{$refId} successfully created".($bill_count?' and billed for $'.$billing_log[0]['amount']:'').'.', null, 'firstdata.log' );
	}

	/**
	 * Get details of a recurring profile order
	 * @see Mage_Payment_Model_Recurring_Profile_MethodInterface::getRecurringProfileDetails()
	 */
	public function getRecurringProfileDetails($referenceId, Varien_Object $result) {
		if( $this->_debug ) Mage::log('getRPDetails()', null, 'firstdata.log');
	}

	/**
	 * (bool) Can get details... Everything we have is stored internally.
	 * @see Mage_Payment_Model_Recurring_Profile_MethodInterface::canGetRecurringProfileDetails()
	 */
	public function canGetRecurringProfileDetails() {
		if( $this->_debug ) Mage::log('canGetRPDetails()', null, 'firstdata.log');
		
		return false;
	}

	/**
	 * Update a recurring profile
	 * @see Mage_Payment_Model_Recurring_Profile_MethodInterface::updateRecurringProfile()
	 */
	public function updateRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile) {
		if( $this->_debug ) Mage::log('updateRP()', null, 'firstdata.log');
		
		// I haven't the slightest idea what this method is intended to do. [neither, seemingly, do they.]
	}

	/**
	 * Update the status of a recurring profile
	 * @see Mage_Payment_Model_Recurring_Profile_MethodInterface::updateRecurringProfileStatus()
	 */
	public function updateRecurringProfileStatus(Mage_Payment_Model_Recurring_Profile $profile) {
		if( $this->_debug ) Mage::log('updateRPStatus()', null, 'firstdata.log');
		
		$profile->setState( $profile->getNewState() );
	}
	
	/**
	 * Fetch current customer's payment profiles and masked
	 * card number if available.
	 */
	public function getPaymentInfo( $customer_id=0 ) {
		if( $this->_debug ) Mage::log('getPaymentInfo('.$customer_id.')', null, 'firstdata.log');
		
		if( $customer_id < 1 ) {
			$customer = $this->getCustomer();
			
			if( $customer ) {
				$customer_id = $customer->getId();
			}
		}
		
		if( $customer_id ) {
			$cards = Mage::helper('transarmor')->getCustomerCards( $customer_id );
			
			return $cards;
		}
		
		return false;
	}
	
	/**
	 * Fetch a payment profile by ID.
	 */
	public function getPaymentInfoById( $payment_id ) {
		if( $this->_debug ) Mage::log('getPaymentInfoById('.$payment_id.')', null, 'firstdata.log');
		
		return Mage::getModel('transarmor/card')->load( $payment_id );
	}

	/**
	 * Generate a payment profile from recurring profile info.
	 */
	protected function storeCardRecurring( &$_customer, &$profile ) {
		if( $this->_debug ) Mage::log('storeCardRecurring()', null, 'firstdata.log');
		
		$payment	= $profile->getQuote()->getPayment();
		$order		= $payment->getOrder();
		
		/**
		 * Are we paying with a stored card?
		 */
		if( $order ) {
			$card_id = $order->getExtCustomerId();
		}
		else {
			$card_id = 0;
		}
		
		if( intval($card_id) <= 0 && !empty($payment) && $payment->getCcNumber() ) {
			$billing = $profile->getBillingAddressInfo();
			if( !empty($billing) ) {
				$this->api->clearParameters();
				
				$billing['street'] = explode( "\n", $billing['street'] );
				
				$this->api->setParameter( 'transaction_type', 'STORE' );
				$this->api->setParameter( 'amount', $this->api->formatAmount( 0.00 ) );
				
				$this->api->setParameter( 'client_email', $billing['email'] );
				$this->api->setParameter( 'customer_ref', $_customer->getId() );
				$this->api->setName( $billing['firstname'], $billing['lastname'] );
				$this->api->setAddr(
					$billing['street'][0],
					$billing['city'],
					$billing['region'],
					$billing['country_id'],
					$billing['postcode']
				);
				
				$this->api->setParameter( 'cc_number', $payment->getCcNumber() );
				$this->api->setParameter( 'cc_verification_str2', $payment->getCcCid() );
				$this->api->setParameter( 'cc_expiry', sprintf("%02d%02d", $payment->getCcExpMonth(), substr( $payment->getCcExpYear(), -2 ) ) );
				$this->api->setParameter( 'credit_card_type', $payment->getCcType() );
					
				if( !$this->_admin ) {
					$this->api->setParameter( 'client_ip', $_SERVER['REMOTE_ADDR'] );
				}
				
				$this->api->runTransaction();
				$this->checkErrors();
				
				$response = $this->getApiResponse();
				
				/**
				 * The transaction was successful. Create a new 'card'.
				 */
				$card = Mage::getModel('transarmor/card');
				$card->setTransId( $response->getToken() )
					 // ->setTransExpires( time() + (86400 * 365 * 3) )
					 ->setCustomerId( $_customer->getId() )
					 ->setType( $payment->getCcType() )
					 ->setLast4( substr( $payment->getCcNumber(), -4 ) )
					 ->setNotify( strtotime( '07-'.$payment->getCcExpMonth().'-'.$payment->getCcExpYear() ) - (86400 * 14) )
					 ->setFirstname( $billing['firstname'] )
					 ->setLastname( $billing['lastname'] )
					 ->setAdditionalInfo( json_encode( $this->api->getParameters() + array( 'name1' => $billing['firstname'], 'name2' => $billing['lastname'], 'addr1' => $billing['street'][0], 'city' => $billing['city'], 'state' => $billing['region'], 'country' => $billing['country_id'], 'zip' => $billing['postcode'] ) ) )
					 ->save();
				
				$card_id = $card->getId();
				
				Mage::log( json_encode( $response->getData() ), null, 'firstdata.log', true );
			}
		}

		if( intval($card_id) <= 0 ) {
			Mage::log( 'Unable to store RP credit card.', null, 'firstdata.log', true );
			Mage::log( 'API: '.$this->api->getResponse(), null, 'firstdata.log', true );
			Mage::throwException( "First Data Payment Gateway: Payment failed; unable to store your card. Please seek support." );
		}
		
		return intval($card_id);
	}
	
	/**
	 * Create/edit a card
	 */
	public function createCustomerPaymentProfile( $billing, $_customer, $card_id=0 ) {
		if( $this->_debug ) Mage::log('createCustomerPaymentProfile()', null, 'firstdata.log');
		
		$card = Mage::getModel('transarmor/card')->load( $card_id );
		
		if( !$card || $card->getId() != $card_id || ( $card->getId() && $card->getCustomerId() != intval( $_customer->getId() ) ) ) {
			Mage::throwException( 'Invalid Request' );
			
			return false;
		}
		
		$this->api->clearParameters();
		
		if( $card->getId() ) {
			$this->api->setParameter( 'transarmor_token', $card->getTransId() );
		}
		
		$this->api->setParameter( 'transaction_type', 'STORE' );
		$this->api->setParameter( 'amount', $this->api->formatAmount( 0.00 ) );
		
		$this->api->setParameter( 'client_email', $_customer->getEmail() );
		$this->api->setParameter( 'customer_ref', $_customer->getId() );
		$this->api->setName( $billing['firstname'], $billing['lastname'] );
		$this->api->setAddr(
			$billing['address1'],
			$billing['city'],
			$billing['state'],
			$billing['country'],
			$billing['zip']
		);
		
		if( strlen( $billing['cc_number'] ) > 10 || $card_id == 0 ) {
			$this->api->setParameter( 'cc_number', $billing['cc_number'] );
			
			$card->setType( $billing['cc_type'] );
			$card->setLast4( substr( $billing['cc_number'], -4 ) );
		}
		
		if( !empty($billing['cc_cid']) || $card_id == 0 ) {
			$this->api->setParameter( 'cc_verification_str2', $billing['cc_cid'] );
		}
		
		if( ( !empty($billing['cc_exp_month']) && !empty($billing['cc_exp_year']) ) || $card_id == 0 ) {
			$this->api->setParameter( 'cc_expiry', sprintf("%02d%02d", $billing['cc_exp_month'], substr( $billing['cc_exp_year'], -2 ) ) );
			
			$card->setNotify( strtotime( '07-'.$billing['cc_exp_month'].'-'.$billing['cc_exp_year'] ) - (86400 * 14) );
		}
		else {
			$this->api->setParameter( 'cc_expiry', $card->getExpiry() );
		}
			
		if( !$this->_admin ) {
			$this->api->setParameter( 'client_ip', $_SERVER['REMOTE_ADDR'] );
		}
		
		$this->api->setParameter( 'credit_card_type', $card->getType() );
		
		$this->api->runTransaction();
		$this->checkErrors();
		
		$response = $this->getApiResponse();
		
		/**
		 * The transaction was successful. Save/create a new 'card'.
		 */
		
		$card->setTransId( $response->getToken() )
			 // ->setTransExpires( time() + (86400 * 365 * 3) )
			 ->setCustomerId( $_customer->getId() )
			 ->setFirstname( $billing['firstname'] )
			 ->setLastname( $billing['lastname'] )
			 ->setAdditionalInfo( json_encode( $this->api->getParameters() + array( 'name1' => $billing['firstname'], 'name2' => $billing['lastname'], 'addr1' => $billing['address1'], 'city' => $billing['city'], 'state' => $billing['state'], 'country' => $billing['country'], 'zip' => $billing['zip'] ) ) )
			 ->save();
		
		$payment_id = $card->getId();
		
		Mage::log( json_encode( $response->getData() ), null, 'firstdata.log', true );
		
		return $payment_id;
	}
	
	/**
	 * Remove a customer's payment profile
	 */
	public function deletePaymentProfile( $payment_id ) {
		if( $this->_debug ) Mage::log('deletePaymentProfile()', null, 'firstdata.log');
		
		
		$_customer = $this->getCustomer();
		
		$card = $this->getPaymentInfoById( $payment_id );
		
		if( $this->_admin || $_customer->getId() == $card->getCustomerId() ) {
			$card->delete();
			
			/**
			 * Suspend any profiles using that card.
			 */
			$db			= Mage::getSingleton('core/resource')->getConnection('core_read');
			$rp_table	= Mage::getSingleton('core/resource')->getTableName('sales/recurring_profile');
			$sql		= $db->select()
							 ->from( $rp_table, array('internal_reference_id') )
							 ->where( 'method_code="transarmor" AND (state="active" OR state="pending") AND additional_info LIKE "%'.intval($payment_id).'%"' );
			$data		= $db->fetchAll($sql);
			
			$count = 0;
			if( count($data) > 0 ) {
				foreach( $data as $pid ) {
					$profile	= Mage::getModel('sales/recurring_profile')->loadByInternalReferenceId( $pid['internal_reference_id'] );
					$adtl		= $profile->getAdditionalInfo();
					if( $adtl['payment_id'] == $payment_id ) {
						$profile->setState( Mage_Sales_Model_Recurring_Profile::STATE_SUSPENDED );
						$profile->save();
						$count++;
					}
				}
			}
			
			if( $count > 0 ) {
				Mage::log( "Payment method deleted; automatically suspended $count recurring profiles.", null, 'firstdata.log' );
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Generate an authorize or capture transaction from existing profiles.
	 */
	protected function bill( &$payment, $amount, $type = 'AUTH' ) {
		if( $this->_debug ) Mage::log('bill(), type='.$type, null, 'firstdata.log');
		
		// Can't run a $0.00 transaction. No way around this.
		if( $amount <= 0 ) {
			return $this;
		}
		
		/**
		 * Initialize
		 */
		$this->initializeApi();
		
		$this->api->clearParameters();

		$order		= $payment->getOrder();
		$trans_id	= explode( ':', $order->getExtOrderId(), 2 );
		$payment_id = $order->getExtCustomerId();
		$card		= $this->getPaymentInfoById( $payment_id );
		$new_card	= false;
		
		// Authenticate
		if( $card && $card->getId() && $card->getCustomerId() != intval( $order->getCustomerId() ) ) {
			$card = false;
		}
		
		// Handle transaction ID for partial invoicing
		if( !is_null( $this->_invoice ) && $this->_invoice->getTransactionId() != '' ) {
			$trans_id = explode( ':', $this->_invoice->getTransactionId() );
		}
		
		// Handle PriorAuth with no auth code.
		if( ( !isset( $trans_id[1] ) || empty( $trans_id[1] ) ) && $type == 'CAPTURE' ) {
			$type = 'SALE';
		}
		elseif( isset( $trans_id[1] ) ) {
			$this->api->setParameter( 'authorization_num', $trans_id[1] );
		}
		
		$this->api->setParameter( 'transaction_type', $type );
		
		/**
		 * Set billing information:
		 * - Use order's prior transaction,
		 * - or card's prior transaction,
		 * - or new credit card.
		 */
		if( !empty( $trans_id[0] ) && $card ) {
			$this->api->setParameter( 'transarmor_token', $trans_id[0] );
			$this->api->setParameter( 'cc_expiry', $card->getExpiry() );
			$this->api->setParameter( 'credit_card_type', $card->getType() );
		}
		elseif( !empty( $payment_id ) && $card ) {
			$this->api->setParameter( 'transarmor_token', $card->getTransId() );
			$this->api->setParameter( 'cc_expiry', $card->getExpiry() );
			$this->api->setParameter( 'credit_card_type', $card->getType() );
		}
		else {
			$new_card	= true;
			
			$this->api->setParameter( 'credit_card_type', $payment->getCcType() );
			$this->api->setParameter( 'cc_number', $payment->getCcNumber() );
			$this->api->setParameter( 'cc_expiry', sprintf("%02d%02d", $payment->getCcExpMonth(), substr( $payment->getCcExpYear(), -2 ) ) );
			$this->api->setParameter( 'cc_verification_str2', $payment->getCcCid() );
			
			$card->setType( $payment->getCcType() )
				 ->setLast4( substr( $payment->getCcNumber(), -4 ) );
			
			if( $this->getStoreConfig('useccv') ) {
				$this->api->setParameter( 'cvd_presence_ind', '1' );
			}
			else {
				$this->api->setParameter( 'cvd_presence_ind', '9' );
			}
		}
		
		$billing	= $order->getBillingAddress();
		$this->api->setParameter( 'company_name', $billing->getCompany() );
		$this->api->setName( $billing->getFirstname(), $billing->getLastname() );
		$this->api->setAddr(
			$billing->getStreet(1),
			$billing->getCity(),
			$billing->getRegionCode(),
			$billing->getCountry(),
			$billing->getPostcode()
		);
		$this->api->setParameter( 'client_email', $order->getCustomerEmail() );
		$this->api->setParameter( 'customer_ref', $order->getCustomerId() );
		
		if( !$this->_admin ) {
			$this->api->setParameter( 'client_ip', $_SERVER['REMOTE_ADDR'] );
		}
		
		$this->api->setParameter( 'reference_no', $order->getIncrementId() );
		$this->api->setParameter( 'currency_code', $this->getConfigData('currency') );
		$this->api->setParameter( 'amount', $this->api->formatAmount( $amount ) );
		
		/**
		 * Run the transaction and handle the result
		 */
		$this->api->runTransaction();

		$this->checkErrors();
		
		$response = $this->getApiResponse();
		
		if( $response->hasCcType() ) {
			$payment->setCcType( $response->getCcType() );
		}
		
		$addr = array(
			'name1'		=> $billing->getFirstname(),
			'name2'		=> $billing->getLastname(),
			'addr1'		=> $billing->getStreet(1),
			'city'		=> $billing->getCity(),
			'state'		=> $billing->getRegionCode(),
			'country'	=> $billing->getCountry(),
			'zip'		=> $billing->getPostcode()
		);
		
		/**
		 * The transaction was successful. If we need to, create a new 'card'.
		 */
		if( $new_card === true ) {
			$card = Mage::getModel('transarmor/card');
			$card->setTransId( $response->getToken() )
				 // ->setTransExpires( time() + (86400 * 365 * 3) )
				 ->setCustomerId( intval( $order->getCustomerId() ) )
				 ->setType( $payment->getCcType() )
				 ->setLast4( substr( $payment->getCcNumber(), -4 ) )
				 ->setNotify( strtotime( '07-'.$payment->getCcExpMonth().'-'.$payment->getCcExpYear() ) - (86400 * 14) )
				 ->setStored( intval( $payment->getSaveCard() ) )
				 ->setFirstname( $order->getCustomerFirstname() )
				 ->setLastname( $order->getCustomerLastname() )
				 ->setAdditionalInfo( json_encode( $this->api->getParameters() + $addr ) )
				 ->save();
		}
		elseif( $new_card !== true ) {
			if( $payment->getCcType() != '' ) {
				$card->setCcType( $payment->getCcType() );
			}
			
			// Already-stored card; update the transaction ID.
			$card->setTransId( $response->getToken() )
				 ->setAdditionalInfo( json_encode( $this->api->getParameters() + $addr ) )
				 ->save();
		}

		$payment->setTransactionId( $response->getToken() . ':' . $response->getAuthCode() )
				->setCcLast4( $card->getLast4() )
				->setCcType( $card->getType() )
				->setAdditionalInformation( $response->getData() );
		
		if( $type == 'AUTH' ) {
			$payment->setIsTransactionClosed(0);
		}
		else {
			$payment->setIsTransactionClosed(1);
		}

		if( !in_array( $type, array( 'REFUND', 'CAPTURE' ) ) ) {
			$payment->getOrder()->setExtOrderId( $response->getToken() . ':' . $response->getAuthCode() )
								->setState( $this->getConfigData('order_status') )
								->setStatus( $this->getConfigData('order_status') );
		}

		$payment->getOrder()->setExtCustomerId( $card->getId() )
							->save();

		Mage::log( json_encode( $response->getData() ), null, 'firstdata.log', true );
		
		return $this;
	}
	
	/**
	 * Parse transaction results into object
	 */
	protected function getApiResponse() {
		$result = new Varien_Object;
		
		$result->setTransactionId( $this->api->getTransactionID() )
				->setToken( $this->api->getResponse('transarmor_token') )
				->setStatus( $this->api->getStatus() )
				->setAvsResponse( $this->api->getAvsResp() )
				->setCvv2Response( $this->api->getCvv2Resp() )
				->setAuthCode( $this->api->getAuthCode() )
				->setMessage( $this->api->getMessage() )
				->setCustomerId( $this->api->getParameter( 'customer_ref' ) )
				->setCustomerIp( $this->api->getParameter( 'client_ip' ) )
				->setOrderId( $this->api->getParameter( 'reference_no' ) )
				->setAmount( $this->api->getParameter( 'amount' ) )
				->setTransactionType( $this->api->getParameter( 'transaction_type' ) )
				->addData( $this->api->getResponse() );
		
		$result->unsCcVerificationStr2();
		
		$ccMap = array(
			'Discover'			=> 'DI',
			'Mastercard'		=> 'MC',
			'American Express'	=> 'AE',
			'Visa'				=> 'VI',
			'JCB'				=> 'JCB',
		);
		
		if( isset( $ccMap[ $result->getCreditCardType() ] ) ) {
			$result->setCcType( $ccMap[ $result->getCreditCardType() ] );
		}
		
		return $result;
	}

	/**
	 * Handle game-over errors
	 */
	protected function checkErrors() {
		$from = Mage::getStoreConfig('trans_email/ident_general/email');
		$code = $this->api->getStatus();
		
		Mage::log( 'exact_resp_code = ' .$code, null, 'firstdata.log', true );
		Mage::log( 'transarmor_token = ' .$this->api->getResponse('transarmor_token'), null, 'firstdata.log', true );
		
		// Bad credentials
		if( $code == '43' || $this->api->getResponse('transarmor_token') == '' ) {
			$subj = 'First Data TransArmor Payment Module - Invalid API details';
			Mage::log( $subj, null, 'firstdata.log', true );
			$body = "Warning: Your First Data Gateway ID, Password, or HMAC Key appears to be incorrect, or TransArmor has not been enabled. The payment module is unable to authenticate properly. The First Data payment method will not work properly until this is fixed.";
			mail( $from, $subj, $body, "From: " . $from . "\r\n" );
		}
		
		if( $this->api->isError() ) {
			$failure = $this->api->isDeclined() ? 'Credit card declined: ' : 'Transaction failed: ';
			
			Mage::log( json_encode( $this->getApiResponse()->getData() ), null, 'firstdata.log', true );
			
			if( in_array( $this->api->getMessage(), array_keys( $this->errors ) ) ) {
				$message = $this->errors[ $this->api->getMessage() ];
			}
			else {
				$message = $this->api->getMessage();
			}
			
			Mage::log( $failure . "\n" . print_r( $this->api->getParameters(), 1 ) . "\n" . $this->api->getRawResponse(), null, 'firstdata.log', true );
			Mage::throwException( Mage::helper('transarmor')->__( $failure ) . Mage::helper('transarmor')->__( $message ) );
		}
	}
}
