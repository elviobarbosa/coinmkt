<?php

require_once('conn.php');
require_once('extract-data.php');

$conn = connectDB();

$sql = "SELECT * FROM tbl_criptos";
$result = $conn->query($sql);


if ($result->num_rows > 0) {

  // output data of each row
  while($row = $result->fetch_assoc()) {
    

    $sqlLast = "SELECT * FROM tbl_historical_prices 
    WHERE coin_symbol = '". $row["symbol"] ."'
    ORDER BY price_time DESC
    LIMIT 14,1";

    $resultLast = $conn->query($sqlLast);

    $rowLast = $resultLast->fetch_assoc();


    $sqlDel = "DELETE FROM tbl_historical_prices 
    WHERE coin_symbol = '". $row["symbol"] ."' AND ID < " . $rowLast["ID"];

    $resultDel = $conn->query($sqlDel);


  }
} else {
  //echo "0 results";
}


?>
