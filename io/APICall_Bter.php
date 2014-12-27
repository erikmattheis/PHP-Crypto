<?php



function assembleRequest($method, $args = array())
{

	$BTER_PUBLIC_METHODS = array(
		'pairs',
		'tickers',
		'ticker',
		'depth',
		'trade'
	);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 0.5);

	$arr = explode("/", $method);
	$methodPart = $arr[0];

	if (in_array($methodPart, $BTER_PUBLIC_METHODS)) {

		curl_setopt($ch, CURLOPT_URL, 'http://data.bter.com/api/1/' . $method);
		

	}

	else {

		$mt = explode(' ', microtime());
		$args['nonce'] = $mt[1].substr($mt[0], 2, 6);
		$postData = http_build_query($args, '', '&');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

		$signature = hash_hmac('sha512', $postData, BTER_API_SECRET);
	 
		$headers = array(
			'KEY: ' . BTER_API_KEY,
			'SIGN: ' . $signature,
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_URL, 'https://bter.com/api/1/private/' . $method);

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

	foreach ($decodedResponse as $name => $values) {

		$currency_codes = explode("_", $name);

		$prices[] = array(
			"exchange_id" => 1,
			"base_currency_code" => $currency_codes[0],
			"quote_currency_code" => $currency_codes[1],
			"market_name" => $name,
			"bid_price" => $values["buy"],
			"bid_size" => null,
			"ask_price" => $values["sell"],
			"ask_size" => null,
			"market_price" => $values["last"],
			"price_time" => date("Y-m-d H:i:s")
		);

	}

	return $prices;

}
