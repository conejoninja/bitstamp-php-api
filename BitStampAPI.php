<?php

class BitStampAPI {

    private $_key;
    private $_secret;
    private $_clientId;

    public function __construct($key, $secret, $clientId) {
        $this->_key = $key;
        $this->_secret = $secret;
        $this->_clientId = $clientId;
    }

    /**
     * Retrieve the ticker
     *
     * @return mixed (json)
     */
    public function ticker() {
        return $this->_doRequest('ticker');
    }

    /**
     * Retrieve the order book
     *
     * @param int $unified group orders by price
     * @return array (json)
     */
    public function orderBook($unified = 1) {
        return $this->_doRequest('order_book', array('group' => $unified));
    }

    /**
     * Retrieve transactions
     *
     * @param int $offset
     * @param int $limit
     * @param string $sort
     * @return array (json)
     */
    public function transactions($offset = 0, $limit = 100, $sort = 'desc') {
        $sort = strtolower($sort)=='asc'?'asc':'desc';
        return $this->_doRequest('transactions', array('offset' => $offset, 'limit' => $limit, 'sort' => $sort));
    }

    /**
     * Retrieve rate of conversion EUR<->USD
     *
     * @return array (json)
     */
    public function rate() {
        return $this->_doRequest('eur_usd');
    }

    /**
     * Retrieve user's balance
     *
     * @return array (json)
     */
    public function balance() {
        return $this->_doRequest('balance');
    }

    /**
     * Retrieve user's transactions
     *
     * @param int $offset
     * @param int $limit
     * @param string $sort
     * @return array (json)
     */
    public function userTransactions($offset = 0, $limit = 100, $sort = 'desc') {
        $sort = strtolower($sort)=='asc'?'asc':'desc';
        return $this->_doRequest('user_transactions', array('offset' => $offset, 'limit' => $limit, 'sort' => $sort));
    }

    /**
     * Retrieve user's open orders
     *
     * @return array (json)
     */
    public function openOrders() {
        return $this->_doRequest('open_orders');
    }

    /**
     * Cancel an user's open order
     *
     * @param $id
     * @return array (json)
     */
    public function cancelOrder($id) {
        return $this->_doRequest('cancel_order', array('id' => $id));
    }

    /**
     * Buy order
     *
     * @param $amount
     * @param $price
     * @return array (json)
     */
    public function buy($amount, $price) {
        return $this->_doRequest('buy', array('amount' => $amount, 'price' => $price));
    }

    /**
     * Sell order
     *
     * @param $amount
     * @param $price
     * @return array (json)
     */
    public function sell($amount, $price) {
        return $this->_doRequest('sell', array('amount' => $amount, 'price' => $price));
    }

    /**
     * Check BitStamp's code
     *
     * @param $code
     * @return array (json)
     */
    public function checkCode($code) {
        return $this->_doRequest('check_code', array('code' => $code));
    }

    /**
     * Redeem BitStamp's code
     *
     * @param $code
     * @return array (json)
     */
    public function redeemCode($code) {
        return $this->_doRequest('redeem', array('code' => $code));
    }

    /**
     * Retrieve user's withdrawals
     *
     * @return array (json)
     */
    public function withdrawals() {
        return $this->_doRequest('withdrawal_request');
    }

    /**
     * Withdrawal request to Bitcoin address
     *
     * @param $amount
     * @param $address
     * @return array (json)
     */
    public function withdrawalBitcoin($amount, $address) {
        return $this->_doRequest('bitcoin_withdrawal', array('amount' => $amount, 'address' => $address));
    }

    /**
     * Create Bitcoin deposit address
     *
     * @return array (json)
     */
    public function depositBitcoin() {
        return $this->_doRequest('bitcoin_deposit_address');
    }

    /**
     * Retrieve list of unconfirmed Bitcoin deposits
     *
     * @return array (json)
     */
    public function unconfirmedDeposits() {
        return $this->_doRequest('unconfirmed_btc');
    }

    /**
     * Withdrawal request to Ripple address
     *
     * @param $amount
     * @param $address
     * @param $currency
     * @return array (json)
     */
    public function withdrawalRipple($amount, $address, $currency) {
        return $this->_doRequest('ripple_withdrawal', array('amount' => $amount, 'address' => $address, 'currency' => $currency));
    }

    /**
     * Create Ripple deposit address
     *
     * @return array (json)
     */
    public function depositRipple() {
        return $this->_doRequest('ripple_address');
    }

    /**
     * Create a request to the BitStamp's API
     *
     * @param $action
     * @param array $params
     * @return array (json)
     */
    private function _doRequest($action, array $params = array()) {
        $time = explode(" ", microtime());
        $nonce = $time[1].substr($time[0], 2, 6);
        $params['nonce'] = $nonce;
        $params['key'] = $this->_key;
        $params['signature'] = $this->_signature($nonce);
        $request = http_build_query($params, '', '&');

        $headers = array();
        static $curl = null;
        if(is_null($curl)) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        }
        curl_setopt($curl, CURLOPT_URL, 'https://www.bitstamp.net/api/'.$action.'/');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $json = curl_exec($curl);
        if($json === false) {
            return array('error' => 1, 'message' => curl_error($curl));
        }
        $data = json_decode($json, true);
        if(!$data) {
            return array('error' => 2, 'message' => 'Invalid data received, please make sure connection is working and requested API exists');
        }
        return $data;
    }

    /**
     * Create a signature for the API request
     *
     * @param $nonce
     * @return string
     */
    private function _signature($nonce) {
        return strtoupper(hash_hmac('sha256', ($nonce.$this->_clientId.$this->_key), $this->_secret));

    }
}