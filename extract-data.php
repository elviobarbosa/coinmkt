<?php
require_once('conn.php');

function cal_percentage($oldFigure, $newFigure) {

  $percentChange = (1 - $oldFigure / $newFigure) * 100;
  return number_format((float)$percentChange, 2, '.', '');

}


function fcExtractData($obj, $coins) {
	$connIn = connectDB();
	$data = json_decode($obj);


	foreach ($coins as $key => $value) {
		$price = $data->data->$key->quote->USD->price;
		$volume24 = $data->data->$key->quote->USD->volume_24h;
		//$volume24 = abs($volume24 - $value['last_vol24']);

		$today = date("Y-m-d H:i:s");
		
		$to_time = strtotime($today);
		$from_time = strtotime($value['last_update']);

		$volDif = abs( intval($volume24) - intval($value['last_vol24']) );

		
		//$dateDif = round(abs(strtotime("now") - $from_time) / 60,2);

		$sql = "INSERT INTO tbl_historical_prices (coin_symbol, price, price_time, volume_24h, dif_vol_24) VALUES ('{$key}', {$price},'{$today}', {$volume24}, {$volDif})";

		
		

		if ($connIn->query($sql) === TRUE) {
						
			//if ($doubleVol24 >= 80) echo "TELEGRAM<br><br><br>";
			
			$perc = cal_percentage( $value['last_price'], $price );
			// echo $value['last_price'] . "<br><br>";
			$lastPrice = number_format((float)$value['last_price'], 4, '.', '');
			
			if ($perc >= 2.6 && $lastPrice > 0 ) {
				
				$token='2105890962:AAHO9D7S4zOi8gpXcXVVA2yoBu8T-M-C1go';
				$grupo=-649294533;
				$out = ($price * 2.5)/100;
				$out = $price + $out;
				$out = number_format((float)$out, 3, '.', '');

				$parametros['chat_id']=$grupo;
				$parametros['text']=$key . ": " . $perc . "% | USD: " .  number_format((float)$price, 3, '.', '') . " | OUT: " . $out;

				// PARA ACEITAR TAGS HTML
				$parametros['parse_mode']='html'; 
				// PARA NÃO MOSTRAR O PREVIW DE UM LINK
				$parametros['disable_web_page_preview']=true; 

				$options = array(
					'http' => array(
					'method'  => 'POST',
					'content' => json_encode($parametros),
					'header'=>  "Content-Type: application/json\r\n" .
								"Accept: application/json\r\n"
					)
				);

				$context  = stream_context_create( $options );
				file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage', false, $context );
			}



		} else {
		  //echo "Error: " . $sql . "<br>" . $conn->error;
		}
		
		
	}
	$connIn->close();

}
?>
