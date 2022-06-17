<?php

include "db_connect.php";

$base_asset = $_POST['base_asset'];
$quote_asset =$_POST['quote_asset'];
$sell_price = $_POST['sell_price'];
$buy_price = $_POST['buy_price'];

$query = "INSERT INTO `price_settings` (base_asset, quote_asset, sell_price, buy_price) VALUES(:base_asset, :quote_asset, :sell_price, :buy_price)";
 
$stmt = $conn->prepare($query);
$stmt->bindParam(':base_asset', $base_asset);
$stmt->bindParam(':quote_asset', $quote_asset);
$stmt->bindParam(':sell_price', $sell_price);
$stmt->bindParam(':buy_price', $buy_price);

$stmt->execute();
$conn = null;

?>

end_of_page: