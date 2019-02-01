<?php

/**
 * API Client library
 * /
 *
 * /**
 * REST API Client
 *
 * @category   Payment
 * @package    CardCadou
 * @subpackage API_Client
 * @author     Alin Pop <alin.pop@pitechnologies.ro>
 * @author     Izabella Papp <izabella.papp@pitechnologies.ro>
 * @license    https://cardcadou.online/ CardCadou
 * @link       https://cardcadou.online/
 */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'SHA3.php';

class Client_CardCadou
{
    const VERIFY_API_URL = 'https://www.cardcadou.online/apicardcadou/v1/verify/';
    const ORDER_API_URL = 'https://www.cardcadou.online/apicardcadou/v1/order/';
    const CONFIRM_API_URL = 'https://www.cardcadou.online/apicardcadou/v1/confirm/';

    const SANDBOX_VERIFY_URL = 'https://sandbox.cardcadou.online/apicardcadou/v1/verify/';
    const SANDBOX_ORDER_URL = 'https://sandbox.cardcadou.online/apicardcadou/v1/order/';
    const SANDBOX_CONFIRM_URL = 'https://sandbox.cardcadou.online/apicardcadou/v1/confirm/';

    const REQUEST_TYPE_POST = 'POST';
    const CRYPTO = 'sha3-512';

    const TYPE_ORDER_CARDCADOU = 1;
    const TYPE_ORDER_ONLINE = 2;
    const TYPE_ORDER_RAMBURS = 3;
    
    const TYPE_CONFIRM_ONLINE = 1;
    const TYPE_CONFIRM_RAMBURS = 2;

    const TRANSACTION_CONFIRMED = 1;
    const TRANSACTION_CANCELED = 2;

    private $_httpError = array(
        '400' => 'API Invalid data.',
        '401' => 'API authentication failed.',
        '403' => 'API invalid hash code.',
        '429' => 'API request limit exceeded.',
        '503' => 'API server not responding.'
    );

    private $_orderError = array(
        '1' => 'Card is not valid.',
        '2' => 'Card is already used.',
        '3' => 'Card amount is not correct.',
        '4' => 'Card is expired.',
        '5' => 'Internal error.'
    );

    private $_confirmError = array (
        '1' => 'Order reference number incorrect.',
        '2' => 'Order confirmed/canceled already.',
        '3' => 'Internal error.'
    );

    private $_cURLHandle = null;

    private $_partnerCode = null;

    private $_secretKey = null;

    private $_response = array();

    private $_isSandbox = false;
    
    private $_retry = 0;

    public function Client_CardCadou($partnerCode = null, $secretKey = null, $isSandbox = false)
    {
        $this->_partnerCode = $partnerCode;
        $this->_secretKey = $secretKey;
        $this->_isSandbox = $isSandbox;
        $this->_retry = 0;
        $this->_startcURL();
    }

    private function _startcURL()
    {
        $this->_cURLHandle = curl_init();
        curl_setopt($this->_cURLHandle, CURLOPT_CUSTOMREQUEST, self::REQUEST_TYPE_POST);
        curl_setopt($this->_cURLHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_cURLHandle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->_cURLHandle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->_cURLHandle, CURLOPT_SSLVERSION, 6);
    }

    private function _generateHash($data = null)
    {
        if (version_compare(phpversion(), '7.1.0', '<')) {
            $sha3512 = SHA3::init(SHA3::SHA3_512);
            $sha3512->absorb($data);
            $sha3512->absorb($this->_secretKey);
            return bin2hex($sha3512->squeeze(64));
        }
        $payload = $data . $this->_secretKey;
        return hash(self::CRYPTO, $payload, FALSE);
    }
    
    private function _verifyHash() 
    {
		$data = json_decode($this->_response, true);
		if (isset($data['value'])) {
			$data['value' ] = round($data['value']);
		} 
		$hash = array_pop($data);
		$hashData = implode($data);

		if ($hash == $this->_generateHash($hashData)) {
			return true;
		}
		
		return false; 
	}

    private function _parseResponse($type)
    {
			if ($data = json_decode($this->_response, true)) {
				if (isset($data['deny_code']) && ($data['deny_code'] > 0)) {
					if ($type == 'order') {
						$data['deny_message'] = $this->_orderError[$data['deny_code']];
					}
					if ($type == 'confirm') {
						$data['deny_message'] = $this->_confirmError[$data['deny_code']];
					}
				}
				return $data;
			} else {
				$data = curl_getinfo($this->_cURLHandle);
				return array (
					'error' => array (
						'http_code' => $data['http_code'],
						'message' => $this->_httpError[$data['http_code']]
					)
				);
			}
    }

    public function verifyCard($cardNumber = null)
    {
        $url = ($this->_isSandbox) ? self::SANDBOX_VERIFY_URL : self::VERIFY_API_URL;

        $hashData = $this->_partnerCode . $cardNumber;
        $params = array(
            'partner_code' => $this->_partnerCode,
            'card_series' => $cardNumber,
            'hash_code' => $this->_generateHash($hashData)
        );
        $jsonData = json_encode($params);

        curl_setopt($this->_cURLHandle, CURLOPT_URL, $url);
        curl_setopt($this->_cURLHandle, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($this->_cURLHandle, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData))
        );

        $this->_response = curl_exec($this->_cURLHandle);

		if (json_decode($this->_response, true) == null) {
			$data = curl_getinfo($this->_cURLHandle);
			if ($data['http_code'] == '503' && $this->_retry < 2) {
				sleep(5);
				$this->_retry++;
				$this->verifyCard($cardNumber);
			}
		}
		
        return $this->_parseResponse('verify');
    }

    public function blockCard($cardNumber = null, $amount = 0, $orderRef = null, $orderType = null)
    {
        $url = ($this->_isSandbox) ? self::SANDBOX_ORDER_URL : self::ORDER_API_URL;

        $hashData = $this->_partnerCode . $cardNumber . $amount . $orderRef . $orderType;
        $params = array(
            'partner_code' => $this->_partnerCode,
            'card_series' => $cardNumber,
            'used_amount' => $amount,
            'order_reference' => $orderRef,
            'order_type' => $orderType,
            'hash_code' => $this->_generateHash($hashData)
        );

        $jsonData = json_encode($params);

        curl_setopt($this->_cURLHandle, CURLOPT_URL, $url);
        curl_setopt($this->_cURLHandle, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($this->_cURLHandle, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData))
        );

        $this->_response = curl_exec($this->_cURLHandle);

		if (json_decode($this->_response, true) == null) {
			$data = curl_getinfo($this->_cURLHandle);
			if ($data['http_code'] == '503' && $this->_retry < 2) {
				sleep(5);
				$this->_retry++;
				$this->blockCard($cardNumber, $amount, $orderRef, $orderType);
			}
		}
		
        return $this->_parseResponse('order');
    }

    public function confirmCard($orderRefCC = null, $orderType = null)
    {
        $url = ($this->_isSandbox) ? self::SANDBOX_CONFIRM_URL : self::CONFIRM_API_URL;

        $hashData = $this->_partnerCode . $orderRefCC . self::TRANSACTION_CONFIRMED . $orderType;
        $params = array(
            'partner_code' => $this->_partnerCode,
            'order_reference' => $orderRefCC,
            'confirmation_code' => self::TRANSACTION_CONFIRMED,
            'order_type' => $orderType,
            'hash_code' => $this->_generateHash($hashData)
        );

        $jsonData = json_encode($params);

        curl_setopt($this->_cURLHandle, CURLOPT_URL, $url);
        curl_setopt($this->_cURLHandle, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($this->_cURLHandle, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData))
        );

        $this->_response = curl_exec($this->_cURLHandle);

		if (json_decode($this->_response, true) == null) {
			$data = curl_getinfo($this->_cURLHandle);
			if ($data['http_code'] == '503' && $this->_retry < 2) {
				sleep(5);
				$this->_retry++;
				$this->confirmCard($orderRefCC, $orderType);
			}
		}
		
        return $this->_parseResponse('confirm');
    }

    public function cancelCard($orderRefCC = null, $orderType = null)
    {
        $url = ($this->_isSandbox) ? self::SANDBOX_CONFIRM_URL : self::CONFIRM_API_URL;

        $hashData = $this->_partnerCode . $orderRefCC . self::TRANSACTION_CONFIRMED . $orderType;
        $params = array(
            'partner_code' => $this->_partnerCode,
            'order_reference' => $orderRefCC,
            'confirmation_code' => self::TRANSACTION_CANCELED,
            'order_type' => $orderType,
            'hash_code' => $this->_generateHash($hashData)
        );

        $jsonData = json_encode($params);

        curl_setopt($this->_cURLHandle, CURLOPT_URL, $url);
        curl_setopt($this->_cURLHandle, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($this->_cURLHandle, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData))
        );

        $this->_response = curl_exec($this->_cURLHandle);

		if (json_decode($this->_response, true) == null) {
			$data = curl_getinfo($this->_cURLHandle);
			if ($data['http_code'] == '503' && $this->_retry < 2) {
				sleep(5);
				$this->_retry++;
				$this->cancelCard($orderRefCC, $orderType);
			}
		}
		
        return $this->_parseResponse('confirm');
    }
    
    public function getOrderErrors()
    {
		return $this->_orderError;
	}
	
    public function getConfirmErrors()
    {
		return $this->_confirmError;
	}

    private function _stopcURL()
    {
        curl_close($this->_cURLHandle);
    }

    public function __destruct()
    {
        $this->_stopcURL();
    }
}
