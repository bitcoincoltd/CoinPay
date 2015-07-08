<?php
/*
Demo Integration with the CoinPay API

This demo files gives quick examples of how to use the coinpay API class.
Change the $test_type variable to test different functions
*/

include('coinpay.php');

$payment = new coinpay;
$payment->init('YOUR_API_KEY','YOUR_API_SECRET');

$test_type = 'balance';

switch($test_type){
	case 'balance':
		$balances = $payment->balances();
		
		echo '<h2>Account Balances</h2>';
		echo '<pre>';
		print_r($balances);
		echo '</pre>';
	break;
	case 'address':
		if($address = $payment->getAddress(true)){
			echo 'New Address: '.$address;
		}else{
			echo 'ERROR: '.$payment->error;
		}
	break;
	case 'price':
		if($price = $payment->price(5, 'USD')){
			echo $price->value.' '.$price->currency;
		}else{
			echo 'ERROR: '.$payment->error;
		}
	break;
	case 'create':
		// CREATE AN ORDER
		$data = array('amount' => 1495,
					  'currency' => 'THB',
					  'order_id' => 16,
					  'ipn' => 'http://mywebsite.com/call_back_url',
					  'reference_id' => 'INVOICE_1240' // Optional order reference
					  );
		$order = $payment->create($data);
		
		echo '<h2>Order is created</h2>';
		echo '<pre>';
		print_r($order);
		echo '</pre>';
		echo '<img src="data:image/png;base64,'.$order->qr_data.'">';
		
		// UPDATE THE ORDER
		$data = array('amount' => 1167,
					  'currency' => 'THB',
					  'ipn' => 'http://mywebsite.com/call_back_url',
					  'reference_id' => 'INVOICE_4241' // Optional order reference
					  );
		$payment->order_id = $order->order_id;
		$updated_order = $payment->create($data);
		
		echo '<h2>Order is updated</h2>';
		echo '<pre>';
		print_r($updated_order);
		echo '</pre>';
	break;
	case 'check';
		$check_order = $payment->checkorder(16);
		
		echo '<h2>Order Status</h2>';
		echo '<pre>';
		print_r($check_order);
		echo '</pre>';
	break;
	case 'verifyipn':
		$ipn_verify = $payment->verifyIPN(46, '09s8d0f8sd');
		
		echo '<h2>Verify IPN</h2>';
		echo '<pre>';
		print_r($ipn_verify);
		echo '</pre>';
	break;
	case 'savereference':
		$reference = $payment->sendReference(46,'INVOICE_124');
		echo '<h2>Save Reference ID</h2>';
		echo '<pre>';
		print_r($reference);
		echo '</pre>';
	break;
	case 'orders':
		$status = array(1,2,4);
		$orders = $payment->listOrders($status, '2015-06-22 00:00:00','2016-01-31 23:59:59');
		
		echo '<h2>List Orders</h2>';
		echo '<pre>';
		print_r($orders);
		echo '</pre>';
	break;
	case 'paylink':
		$paylink = $payment->payLink(10,'USD');
		if($paylink){
			echo 'Payment Link: '.$paylink;
		}else{
			echo 'ERROR: '.$payment->error;
		}
	break;
}
?>