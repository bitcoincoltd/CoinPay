<?php
/*
TITLE: 			Coinpay API class
VERISON: 		1.0
AUTHOR:  		CoinPay
EMAIL: 			info@coinpay.in.th
DESCRIPTION:  	The coinpay class allows you to easily interact with the http://coinpay.in.th API.  
				Find a full copy of the API documentation at https://coinpay.in.th/docs/
*/
class coinpay
{
	var $access_id, $access_key;
	var $api_url = 'https://coinpay.in.th/api/';
	var $order_id=0;
	var $error;
	public function init($api_id, $api_key){
		if(strlen($api_id) < 12 || strlen($api_key) < 12){
			return false;
		}
		$this->access_id = $api_id;
		$this->access_key = $api_key;
		return true;
	}
	
	public function validate($amount, $currency){
		$params = $this->authParam();
		$params['amount'] = $amount;
		$params['currency'] = $currency;
		if($data = $this->apiFetch('validate',$params)){
			$this->error = $data->errors;
			return $data->success;
		}
		return false;
	}
	
	public function price($amount=0, $currency=''){
		$params = $this->authParam();
		if($amount > 0){
			$params['amount'] = $amount;
			$params['currency'] = $currency;
		}
		if($data = $this->apiFetch('price',$params)){
			if(isset($data->success) && $data->success){
				return $data;
			}else{
				$this->error = $data->error;
				return false;
			}
		}
		return false;
	}
	
	public function getAddress($new = false){
		$params = $this->authParam();
		if($new){
			$params['new'] = true;
		}
		if($data = $this->apiFetch('address',$params)){
			if(isset($data->success) && $data->success){
				return $data->address;
			}else{
				$this->error = $data->error.':'. $data->address;
				return false;
			}
		}
		return false;
	}
	
	public function checkorder($order_id, $reference_id=''){
		$params = $this->authParam();
		$params['order_id'] = $order_id;
		if($reference_id != ''){
			$params['reference_id'] = $reference_id;
		}
		if($data = $this->apiFetch('checkorder',$params)){
			return $data;
		}
		return false;
	}
	
	public function sendReference($order_id, $reference_id){
		$params = $this->authParam();
		$params['order_id'] = $order_id;
		$params['reference_id'] = $reference_id;
		if($data = $this->apiFetch('reference',$params)){
			return $data;
		}
		return false;
	}
	
	public function balances(){
		$params = $this->authParam();
		if($data = $this->apiFetch('balance',$params)){
			return $data;
		}
		return false;
	}
	
	public function create($data){
		$params = $this->authParam();
		$params['amount'] = $data['amount'];
		$params['currency'] = $data['currency'];
		$params['ipn'] = $data['ipn'];
		$params['order_id'] = (isset($data['order_id']) ? $data['order_id'] : (int)$this->order_id);
		$params['reference_id'] = $data['reference_id'];
		if($data = $this->apiFetch('paybox',$params)){
			$this->order_id = $data->order_id;
			return $data;
		}
		return false;
	}
	
	public function countDown($expire,$selector, $text = 'You must send the bitcoins within the next %s Minutes %s Seconds',$expiremsg = 'Bitcoin payment time has expired, please refresh the page to get a new address'){
		return '<p class="bitcoincountdown">'.sprintf($text,'<span id="btcmins">'.$expire.'</span>','<span id="btcsecs">0</span>').'</p><script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script>
			if(typeof bitcointhaitimer == \'undefined\'){
			var bitcointhaitimer = '.(60*$expire).';
			jQuery.noConflict();
			jQuery(function($){
				function btccountDown(){
					bitcointhaitimer -= 1;
					var minutes = Math.floor(bitcointhaitimer / 60);
					var seconds = bitcointhaitimer - minutes * 60;
					$("#btcmins").text(minutes);
					$("#btcsecs").text(seconds);
					if(bitcointhaitimer <= 0){
						$("#btcmins").closest("'.$selector.'").after("<p>'.$expiremsg.'</p>").remove();
					}else{
						setTimeout(btccountDown,1000);
					}
				}
				setTimeout(btccountDown,1000);
			});
			}
		</script>';
	}
	
	public function listOrders($status='',$start_date='',$end_date=''){
		$params = $this->authParam();
		if(is_array($status)){
			$params['status'] = implode(',',$status);
		}elseif($status != '' && intval($status) == $status){
			$params['status'] = (int)$status;
		}
		
		$params['start_date'] = $start_date;
		$params['end_date'] = $end_date;
		
		if($data = $this->apiFetch('orders',$params)){
			return $data;
		}
		return false;
	}
	
	public function verifyIPN($order_id, $verify){
		if($order_id > 0){
			$params = $this->authParam();
			$params['verify'] = $verify;
			$params['order_id'] = $order_id;
			if($data = $this->apiFetch('verifyipn',$params)){
				return $data->success;
			}
		}
		return false;
	}
	
	public function payLink($amount, $currency, $reference_id='',$ipn_url='', $redirect=''){
		$params = $this->authParam();
		$params['amount'] = $amount;
		$params['currency'] = $currency;
		$params['reference_id'] = $reference_id;
		$params['ipn_url'] = $ipn_url;
		$params['redirect'] = $redirect;
		if($result = $this->apiFetch('paylink',$params)){
			if($result->success){
				return $result->link;
			}
		}
		return false;
	}
	
	public function createAccount($data){
		$params = $this->authParam();
		if($result = $this->apiFetch('account/create',array_merge($params, $data))){
			return $result;
		}
		return false;
	}
	
	public function listAccount($account_id=''){
		$params = $this->authParam();
		$params['account_id'] = $account_id;
		if($result = $this->apiFetch('account/details',$params)){
			return $result;
		}
		return false;
	}
	
	public function setBank($account_id, $bank, $bank_holder, $account_number){
		$params = $this->authParam();
		$params['account_id'] = $account_id;
		$params['branch'] = $bank;
		$params['owner'] = $bank_holder;
		$params['account'] = $account_number;
		if($result = $this->apiFetch('account/bank',$params)){
			return $result;
		}
		return false;
	}
	
	private function apiFetch($action,$params){
		if($ch = curl_init ()){
			curl_setopt ($ch, CURLOPT_URL, $this->api_url.$action.'/');
			curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5 );
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt ($ch, CURLOPT_POST, count($params));
			curl_setopt ($ch, CURLOPT_POSTFIELDS,$params);
			
			$str = curl_exec ( $ch );
			curl_close ( $ch );
			if($data = json_decode($str)){
				return $data;
			}
		}
		return false;
	}
	
	private function authParam(){
		$nonce = time();
		$signature = hash('sha256',$this->access_id.$nonce.$this->access_key);
		
		return array('key' => $this->access_id, 'signature' => $signature, 'nonce' => $nonce);
	}
}
?>