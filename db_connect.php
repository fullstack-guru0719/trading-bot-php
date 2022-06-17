<?php
	$conn = new PDO('sqlite:system.db');
 
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 
	$query ="CREATE TABLE IF NOT EXISTS price_settings (id PRIMARY KEY AUTOINCREMENT NOT NULL, base_asset TEXT, quote_asset TEXT, sell_price REAL, buy_price REAL)";
 
	$conn->exec($query);
?>