<?php

function getHistory($base_currency_code, $quote_currency_code, $num_minutes) {
	
	GLOBAL $link;

	$query = "SELECT price_time, bid_price, ask_price
			FROM prices
			WHERE price_time > DATE_SUB(NOW(), INTERVAL ? MINUTE)
				AND base_currency_code = ?
				AND quote_currency_code = ?
			ORDER BY price_time;";

	$stmt = mysqli_stmt_init($link);

	if (!mysqli_stmt_prepare($stmt, $query)) {

		echo "Error preparing statement " . $stmt->error;

	}

	$stmt->bind_param('iss',
			$num_minutes,
			$base_currency_code,
			$quote_currency_code
			);
	$stmt->execute();
	$result = $stmt->get_result();

	return $result;
}