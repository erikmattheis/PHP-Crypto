<?php

$query = "CREATE TABLE IF NOT EXISTS prices (
	price_history_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	exchange_id INT UNSIGNED NOT NULL,
	base_currency_code VARCHAR(7),
	quote_currency_code VARCHAR(7),
	market_name VARCHAR(16),
	bid_price DECIMAL(20,8),
	bid_size FLOAT,
	ask_price DECIMAL(20,8),
	ask_size FLOAT,
	market_price DECIMAL(20,8),
	price_time DATETIME,
	bot_version VARCHAR(8)
)";

if (mysqli_query($link, $query) !== TRUE) {
	echo "Error creating price_history table<br>";
}

