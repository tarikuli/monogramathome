<?php
/**
 * First Data TransArmor integration Global Gateway e4 Web Services gateway class
 * 	Ryan Hoerr
 *  ParadoxLabs, inc
 *  March 2013
 */

class ParadoxLabs_Transarmor_Model_Api
{
	protected $gatewayUrl;
	protected $secretKey;
	
	protected $fields = array(
		'gateway_id'			=> 10,
		'password'				=> 30,
		'transaction_type'		=> 0, // 00-34
		'amount'				=> 0,
		'cc_number'				=> 16,
		'transaction_tag'		=> 0,
		'authorization_num'		=> 8,
		'cc_expiry'				=> 4,
		'cardholder_name'		=> 30,
		'cc_verification_str1'	=> 41,
		'cc_verification_str2'	=> 4,
		'cvd_presence_ind'		=> 0,
		'reference_no'			=> 20,
		'customer_ref'			=> 20,
		'reference_3'			=> 30,
		'client_ip'				=> 15,
		'client_email'			=> 30,
		'currency_code'			=> 3,
		'partial_redemption'	=> 0,
		'ecommerce_flag'		=> 0,
		'transarmor_token'		=> 0,
		'credit_card_type'		=> 0, // American Express, Visa, Mastercard, Discover, Diners Club, JCB
	);
	
	protected $transTypes = array(
		'SALE'					=> '00',
		'AUTH'					=> '01',
		'CAPTURE'				=> '02',
		'REFUND'				=> '04',
		'VOID'					=> '13',
		'STORE'					=> '05',
	);
	
	protected $cardTypes = array(
		'AE'					=> 'American Express',
		'MC'					=> 'Mastercard',
		'VI'					=> 'Visa',
		'DI'					=> 'Discover',
		'JCB'					=> 'JCB',
	);
	
	protected $params	= array();
	protected $defaults	= array();
	
	protected $raw;
	protected $response;
	
	protected $transId;
	protected $status;
	protected $avsResp;
	protected $cvv2Resp;
	protected $authCode;
	protected $message;
	
	const TESTMODE			= '1';
	const POST_LIVE			= 'https://api.globalgatewaye4.firstdata.com/transaction/v12';
	const POST_TEST 		= 'https://api.demo.globalgatewaye4.firstdata.com/transaction/v12';
	
	const STATUS_APPROVED	= '00';
	
	/**
	 * Initialize the gateway
	 */
	public function init( $accountId, $password, $key, $mode=self::TESTMODE )
	{
		$this->secretKey	= $key;
		$this->gatewayUrl	= ($mode == self::TESTMODE) ? self::POST_TEST : self::POST_LIVE;
		
		$this->defaults['gateway_id']		= $accountId;
		$this->defaults['password']			= $password;
		
		$this->clearParameters();
		
		return $this;
	}
	
	/**
	 * Set the API parameters back to defaults, clearing any runtime values.
	 */
	public function clearParameters()
	{
		$this->params = $this->defaults;
	}
	
	/**
	 * Set a parameter.
	 */
	public function setParameter( $key, $val )
	{
		if( !empty($val) ) {
			$key = strtolower( $key );
			
			/**
			 * Make sure we know this parameter
			 */
			if( in_array( $key, array_keys( $this->fields ) ) ) {
				if( $key == 'transaction_type' ) {
					$this->params[ $key ] = $this->transTypes[ $val ];
				}
				elseif( $key == 'credit_card_type' ) {
					$this->params[ $key ] = $this->cardTypes[ $val ];
				}
				/**
				 * Truncate if the value is too long
				 */
				elseif( $this->fields[ $key ] > 0 ) {
					$this->params[ $key ] = substr( $val, 0, $this->fields[ $key ] );
				}
				else {
					$this->params[ $key ] = $val;
				}
			}
			else {
				Mage::log('API: Unknown parameter '.$key, null, 'firstdata.log');
			}
		}
		
		return $this;
	}
	
	public function setName( $first, $last )
	{
		$this->setParameter( 'cardholder_name', $first . ' ' . $last );
		
		return $this;
	}
	
	public function setAddr( $street, $city, $state, $country, $zip )
	{
		$tmp = array( $street, $zip, $city, $state, $country );
		
		$this->setParameter( 'cc_verification_str1', implode( '|', $tmp ) );
		
		return $this;
	}
	
	/**
	 * Get parameters. Useful for debugging.
	 */
	public function getParameters()
	{
		$tmp = $this->params;
		
		/**
		 * Don't return a full CC Number or CVV.
		 */
		if( isset( $tmp['cc_number'] ) ) {
			$tmp['cc_number'] = 'XXXX-' . substr( $tmp['cc_number'], -4 );
		}
		if( isset( $tmp['cc_verification_str2'] ) ) {
			$tmp['cc_verification_str2'] = 'XXX';
		}
		if( isset( $tmp['cc_expiry'] ) ) {
			$tmp['cc_expiry'] = 'XXXX';
		}
		
		unset( $tmp['password'] );
		
		return $tmp;
	}
	
	/**
	 * Get a single parameter
	 */
	public function getParameter( $key )
	{
		$key = strtolower( $key );
		
		return ( isset( $this->params[ $key ] ) ? $this->params[ $key ] : '' );
	}
	
	/**
	 * Will format an amount value to be in the
	 * expected format for the POST.
	 */
	public static function formatAmount( $amount )
	{
		return sprintf( "%01.2f", (float) $amount );
	}
	
	/**
	 * First Data API request
	 */
	public function runTransaction()
	{
		$json_request = json_encode( $this->params );

		// HMAC Hash - c.f. https://transarmor.zendesk.com/entries/22069302-api-security-hmac-hash.
		$hashtime = gmdate('c');
		$content_digest = sha1($json_request);
		$hashstr = "POST\napplication/json; charset=UTF-8\n" . $content_digest . "\n" . $hashtime . "\n/transaction/v12";
		$authstr = base64_encode(hash_hmac('sha1', $hashstr, $this->secretKey, TRUE));

		$curl_headers = array(
			'Content-Type: application/json; charset=UTF-8',
			'Accept: application/json',
			'Authorization: GGE4_API ' . $this->defaults['gateway_id'] . ':' . $authstr,
			'X-GGe4-Date: ' . $hashtime,
			'X-GGe4-Content-SHA1: ' . $content_digest
		);

		// Setup the cURL request.
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $this->gatewayUrl );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $curl_headers );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_NOPROGRESS, 1 );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_request );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $ch, CURLOPT_VERBOSE, 0 );
		$this->raw = curl_exec( $ch );
		curl_close( $ch );
		
		$this->response = json_decode( $this->raw, 1 );
		
		if( isset( $this->response['cc_expiry'] ) ) {
			unset( $this->response['cc_expiry'] );
		}
		
		if( $this->response === null ) {
			$this->response = array(
				'exact_resp_code'	=> preg_replace( '/[^0-9]/', '', $this->raw ),
				'exact_message'		=> $this->raw
			);
		}
		
		return $this;
	}
	
	public function getResponse( $key='' )
	{
		if( !empty( $key ) ) {
			if( isset( $this->response[ $key ] ) ) {
				return $this->response[ $key ];
			}
			
			return '';
		}
		
		return $this->response;
	}
	
	public function getRawResponse()
	{
		return $this->raw;
	}
	
	public function getTransactionID()
	{
		return $this->getResponse('transaction_tag');
	}
	
	public function getStatus()
	{
		return $this->getResponse('exact_resp_code');
	}
	
	public function getAvsResp()
	{
		return $this->getResponse('avs');
	}
	
	public function getCvv2Resp()
	{
		return $this->getResponse('cvv2');
	}
	
	public function getAuthCode()
	{
		return $this->getResponse('authorization_num');
	}
	
	public function getMessage()
	{
		return $this->getResponse('exact_message');
	}
	
	public function isDeclined()
	{
		return in_array( $this->getResponse('exact_resp_code'), array( '08', '22', '25', '26', '27', '28', '31', '32', '57', '58', '60', '63', '64', '68', '72', '93' ) );
	}
	
	public function isError()
	{
		return ( $this->getStatus() !== self::STATUS_APPROVED || $this->getResponse('transaction_approved') != 1 || $this->getResponse('transaction_error') == 1 );
	}
}
