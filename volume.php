<?php

require_once('conn.php');
$conn = connectDB();

$sql = "SELECT * FROM tbl_criptos";
$result = $conn->query($sql);
$symbols = "";
$arrSymbols = [];

function cal_percentage($oldFigure, $newFigure) {

  $percentChange = (1 - $oldFigure / $newFigure) * 100;
  return number_format((float)$percentChange, 2, '.', '');

}


if ($result->num_rows > 0) {

  // output data of each row
  while($row = $result->fetch_assoc()) {
    
    $symbols .= $row["symbol"] . ",";
    $sum = 0;

    $sqlLast = "SELECT * FROM tbl_historical_prices 
    WHERE coin_symbol = '". $row["symbol"] ."'
    ORDER BY ID DESC
    LIMIT 14, 15";

    $resultLast = $conn->query($sqlLast);

    $rowLast = $resultLast->fetch_assoc();
    echo $row["symbol"] . "<br> ";
    $last = $rowLast['dif_vol_24'];


    while($rowLast = $resultLast->fetch_assoc()) { 
    	$per = cal_percentage( $rowLast['dif_vol_24'], $last );
    	$sum = $sum + $per;
    	$style = ($per < 0) ? ' style="color: #f00" ' : "";
    	echo "<span {$style}>";
    	echo $per;
    	echo "</span> > ";
    	$last = $rowLast['dif_vol_24'];
    }
    echo "{$sum}<br><br>";
    // $arrSymbols[ $row["symbol"] ] =  array(
    //     'last_update' => $rowLast["price_time"],
    //     'last_price'=> $rowLast['price'],
    //     'last_vol24'=> $rowLast['volume_24h'] );
  }
  
} else {
  //echo "0 results";
}

$symbols = substr($symbols, 0, -1);
//print_r($symbols);
$conn->close();
exit;
$url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest';
$parameters = [
  'symbol' => $symbols,
  'convert' => 'USD'
];

$headers = [
  'Accepts: application/json',
  'X-CMC_PRO_API_KEY: 775b4321-80bc-4250-8683-f494b9ee4440'
];
$qs = http_build_query($parameters); // query string encode the parameters
$request = "{$url}?{$qs}"; // create the request URL


$curl = curl_init(); // Get cURL resource
// Set cURL options
curl_setopt_array($curl, array(
  CURLOPT_URL => $request,            // set the request URL
  CURLOPT_HTTPHEADER => $headers,     // set the headers 
  CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
));

$response = curl_exec($curl); // Send the request, save the response
$data = json_decode($response);


foreach ($arrSymbols as $key => $value) {

	$volume24 = $data->data->$key->quote->USD->volume_24h;

	echo $key;
	echo ": ";
	echo cal_percentage( $value['last_vol24'], $volume24 );
	echo "<br>";

}

?>
