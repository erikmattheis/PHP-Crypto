<?php

function recordPrices($exchange_id,
	$base_currency_code,
	$quote_currency_code,
	$market_name,
	$bid_price,
	$bid_size,
	$ask_price,
	$ask_size,
	$market_price,
	$price_time) {

	GLOBAL $link,
		$exchangeIds,
		$website,
		$bot_version;

	$local_time = date("Y-m-d H:i:s");
	$bot_version = 0.5;

	$query = "INSERT INTO prices(
		exchange_id,
		base_currency_code,
		quote_currency_code,
		market_name,
		bid_price,
		bid_size,
		ask_price,
		ask_size,
		market_price,
		price_time,
		bot_version
		)
	VALUES(
		?,
		?,
		?,
		?,
		?,
		?,
		?,
		?,
		?,
		?,
		?
		)";

	$stmt = mysqli_stmt_init($link);

	if (!mysqli_stmt_prepare($stmt, $query)) {

		echo "Error preparing statement " . $stmt->error;

	}

	if (!mysqli_stmt_bind_param($stmt, "issssssssss",
		$exchange_id,
		$base_currency_code,
		$quote_currency_code,
		$market_name,
		$bid_price,
		$bid_size,
		$ask_price,
		$ask_size,
		$market_price,
		$price_time,
		$bot_version
		)) {
		echo "Error binding parameters to statement "  . $stmt->error;
	}

	if (!mysqli_stmt_execute($stmt)) {

		echo "Error executing SQL statement " . $stmt->error;

	}

}