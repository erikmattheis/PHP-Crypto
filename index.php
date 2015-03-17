<?php

/* cryptsy's JSON file is large enough to require extra processing time and memory */
set_time_limit (120);
ini_set('memory_limit', '264M');

require_once("cfg.php");
require_once("io/create_tables.php");
/* require_once("io/APICall_Bter.php"); */
require_once("io/APICall_Cryptsy.php");
require_once("io/record_prices.php");
require_once("model/get_pair.php");

$data = callMethod('marketdatav2');

foreach ($data as $market) {

	recordPrices($market["exchange_id"],
		$market["base_currency_code"],
		$market["quote_currency_code"],
		$market["market_name"],
		$market["bid_price"],
		$market["bid_size"],
		$market["ask_price"],
		$market["ask_size"],
		$market["market_price"],
		$market["price_time"]);
}

?>

<html>
<head>
<title>GRAPH!</title>
<script src="http://code.jquery.com/jquery.min.js"></script>
<script src="./highstock.js"></script>
<script src="./highcharts-more.js"></script>

</head>
<body>

<?php

	$num_minutes = 60*24*5;
	$base_currency_code = 'DVC';
	$quote_currency_code = 'XRP';
	
	$result = getHistory($base_currency_code, $quote_currency_code, $num_minutes);

	echo "\n<script>";

	$highchartsString = "$(function () {\n"
		. "$('#graph').highcharts({\n"
		. "	chart: {\n"
		. "		type: 'arearange',\n"
		. "		zoomType: 'x'\n"
		. "	},\n"
		. "	title: {\n"
		. "		text: 'Bid/Ask Price History'\n"
		. "	},\n"
		. "	xAxis: {\n"
		. "		type: 'datetime'\n"
		. "	},\n"
		. "	yAxis: {\n"
		. "		title: {\n"
		. "		text: 'Price'\n"
		. "	}\n"
		. "},\n"
		. "legend: {\n"
		. "	enabled: true\n"
		. "},\n"
		. "series: [\n";

	$highchartsString .= "\n{"
			. 'name: "' . $base_currency_code . '/' . $quote_currency_code . '",'
			. ' data: [';
	while ($row = $result->fetch_assoc()) {

		if (is_numeric($row['bid_price']) &&  is_numeric($row['ask_price'])) {

			$highchartsString .= '[' . (strtotime($row['price_time']) * 1000) . ',' . $row['bid_price'] . ',' . $row['ask_price'] . '],';

		}

	}

	$highchartsString .= "]},\n";
	$highchartsString .= "]\n"
		. "});\n"
		. "});\n";

	echo $highchartsString;
	echo '</script>';

?>

<div id="graph" style="width:90%; height:500px;"></div>';

</body>
</html>
