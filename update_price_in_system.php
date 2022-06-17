<?php

include "system_db_connect.php";
$id = 1;
$sell_price = $_POST['sell_price'];
$buy_price = $_POST['buy_price'];


// $query = "INSERT INTO `price_settings` (base_asset, quote_asset, sell_price, buy_price) VALUES(:base_asset, :quote_asset, :sell_price, :buy_price)";
 

$query = "UPDATE `system_settings` SET `target_base_sell_price` = :sell_price, `target_base_buy_price` = :buy_price WHERE `id` = :id";


$stmt = $conn->prepare($query);

$stmt->bindParam(':sell_price', $sell_price);
$stmt->bindParam(':buy_price', $buy_price);
$stmt->bindParam(':id', $id);

$stmt->execute();
$conn = null;

?>

end_of_page: