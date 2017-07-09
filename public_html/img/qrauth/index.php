<?php
	error_reporting(E_ALL);
	try {
		if (!isset($_GET['c']))
		require_once('../../u/config/config.php');
		require_once('../../akcms/classes/QRcode.php');
		header('X-Powered-By: itTeka.ru');
		QRcode::png('http://'.$_SERVER['HTTP_HOST'].'/_mng/?c='.$_GET['c'],false,'m',4,2,false);
	} catch(Exception $e) {
		header("HTTP/1.0 404 Not Found"); 
		exit($e->getMessage());	
	}
