<?php



function assembleRequest($method, $args = array())
{
	// API settings
	$CRYPTSY_API_KEY = 'your key';
	$CRYPTSY_API_SECRET = '[your secret';
	$CRYPTSY_PUBLIC_METHODS = array(
		'marketdata',
		'marketdatav2',
		'orderdata',
		'singleorderdata',
		'singlemarketdata'
	);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 0.5);

	$arr = explode("/", $method);
	$methodPart = $arr[0];

	if (in_array($methodPart, $CRYPTSY_PUBLIC_METHODS)) {

		curl_setopt($ch, CURLOPT_URL, 'http://pubapi.cryptsy.com/api.php?method=' . $method);

	}

	else {

		$mt = explode(' ', microtime());
		$args['nonce'] = $mt[1].substr($mt[0], 2, 6);
		$postData = http_build_query($args, '', '&');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

		$signature = hash_hmac('sha512', $postData, $CRYPTSY_API_SECRET);
	 
		$headers = array(
			'KEY: ' . $CRYPTSY_API_KEY,
			'SIGN: ' . $signature,
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		curl_setopt($ch, CURLOPT_URL, 'https://api.cryptsy.com/api' . $method);

	}

	return $ch;

}

function callMethod($method, $args = array(), $isRetry = FALSE)
{

	$MAX_API_CALL_RETRIES = 5;
	$numRequests = $isRetry;

	if ($isRetry == FALSE
		|| $isRetry == 0) {

		$numRequests = 0;

	}
	else {

		echo $method . " call failed. Retry #" . $numRequests . "<br>";

	}

	
	$ch = assembleRequest($method, $args);
	$result = curl_exec($ch);
	
	if ($result == FALSE) {

		throw new Exception('API did not respond: ' . curl_error($ch));

	}

	$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	$decodedResponse = json_decode($result, TRUE);

	if (!$decodedResponse) {

		echo 'Status: ' . $responseCode . '. Repeating request, retry #' . $numRequests . '<br>';

		$numRequests++;
		
		if ($numRequests < $MAX_API_CALL_RETRIES) {
		
			callMethod($method, $args, $numRequests);
		}
		else {

			throw new Exception('Maximum number of requests exceeded. Giving up after ' . $numRequests . ' requests.');
		}

	}
	
	$translatedResponse = translateResponde($decodedResponse);
	return $translatedResponse;

}

function translateResponde($decodedResponse) {

	$prices = array();

	foreach ($decodedResponse["return"]["markets"] as $name => $values) {

		$prices[] = array(
			"exchange_id" => 2,
			"base_currency_code" => $values["primarycode"],
			"quote_currency_code" => $values["secondarycode"],
			"market_name" => $values["label"],
			"bid_price" => $values["buyorders"][0]["price"],
			"bid_size" => $values["buyorders"][0]["quantity"],
			"ask_price" => $values["sellorders"][0]["price"],
			"ask_size" => $values["sellorders"][0]["quantity"],
			"market_price" => $values["recenttrades"][0]["price"],
			"price_time" => date("Y-m-d H:i:s")
		);

	}

	return $prices;

}
