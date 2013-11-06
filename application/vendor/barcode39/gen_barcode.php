<?php

if (isset($_GET['code'])) {
	$code = $_GET['code'];
} else {
	echo 'Code expected in URL';
	exit;
}

// include Barcode39 class 
include "Barcode39.php";

$bc = new Barcode39($code); 

// set text size 
//$bc->barcode_text_size = 5; 

// set barcode bar thickness (thick bars) 
//$bc->barcode_bar_thick = 4; 

// set barcode bar thickness (thin bars) 
//$bc->barcode_bar_thin = 2; 

// save barcode GIF file 
$bc->draw();