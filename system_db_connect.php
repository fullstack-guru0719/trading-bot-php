<?php
	$conn = new PDO('sqlite:system.db');
 
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 
	// $query ="CREATE TABLE IF NOT EXISTS system_settings (id PRIMARY KEY AUTOINCREMENT NOT NULL)";


 
	// $conn->exec();
?>