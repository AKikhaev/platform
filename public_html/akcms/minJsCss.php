<?php
$acceptEncodings = (isset($_SERVER['HTTP_ACCEPT_ENCODING'])?$_SERVER['HTTP_ACCEPT_ENCODING']:'').' '.(isset($_SERVER['HTTP_TE'])?$_SERVER['HTTP_TE']:'');
if(strpos($acceptEncodings, 'x-gzip') !== false ){
	$encoding = 'x-gzip';
}elseif(strpos($acceptEncodings,'gzip') !== false ){
	$encoding = 'gzip';
}else{
	$encoding = false;
}
if (isset($_SERVER['SCRIPT_URL'])) $pathstr = $_SERVER['SCRIPT_URL'];
else {
	$pathstr = $_SERVER['REQUEST_URI'];
	if (mb_strpos($pathstr,'?')!==false) $pathstr = mb_substr($pathstr,0,mb_strpos($pathstr,'?'));
	if (mb_strpos($pathstr,'#')!==false) $pathstr = mb_substr($pathstr,0,mb_strpos($pathstr,'#'));
	if (mb_strpos($pathstr,'&')!==false) $pathstr = mb_substr($pathstr,0,mb_strpos($pathstr,'&'));
} 
if (strpos($pathstr,'..')!==false) {
	header("HTTP/1.0 404 Not Found");
	die('Not Found nahuj!');
}
$path = array();  
foreach (explode('/',$pathstr) as $item) if ($item != '') $path[] = $item;
$pathstr = '/'.implode('/',$path);

$pathPre = getcwd ().'/..';
$delpoyFile = $pathPre.$pathstr;
$origFile = str_replace('.min.js','.js',$pathPre.$pathstr);
$fileinf = pathinfo($delpoyFile);

$listFile = $fileinf['dirname'].'/../'.$fileinf['filename'].'.set';

if (isset($fileinf['extension']) && (mb_strpos($pathstr,'/akcms/js/v1/')===0 || mb_strpos($pathstr,'/css/')===0)) {
	if (!in_array($fileinf['extension'],array('js','css'))) { header("HTTP/1.0 423 Locked"); exit('Locked'); }
} else { header("HTTP/1.0 423 Locked"); exit('Unexpected locked'); }
$data = '';
if (file_exists($listFile)) {
	foreach (file($listFile,FILE_IGNORE_NEW_LINES) as $i) {
		$i_sitePath = array();
		if (preg_match('/(?<= src="| href=")([^\s]*?)(?=")/ui',$i,$i_sitePath)==0) {
			header("HTTP/1.0 404 Not Found");
			die('Not Found: source: '.$i);
		}

		$i_path = $pathPre.$i_sitePath[0];
		if (file_exists($i_path)) {
			$data .= file_get_contents($i_path);
		} else {
			header("HTTP/1.0 404 Not Found");
			die('Not Found Resource: '.$i_sitePath[0]);
		}
	}
}/* elseif (file_exists($origFile)) { //to create .min.js
	$data = file_get_contents($origFile);
}*/ 
if ($data != '') {
	switch ($fileinf['extension']) {
		case 'js'  : header('Content-Type: application/x-javascript'); {
			//require_once('../akcms/classes/jsMin.php'); $data = JSMin::minify($data);
			require_once('../akcms/classes/jsMinifier.php'); $jsMin = new jsMinifier(); $data = $jsMin->minify($data);
		}
		break;
		case 'css' : header('Content-Type: text/css'); {
			require_once('../akcms/classes/cssMin.php'); $data = CssMin::minify($data);		
		}
		break;
	}
	@file_put_contents($delpoyFile,$data);
	if ($encoding!=false) {
		header('Content-Encoding: '.$encoding);
		header('Vary: Accept-Encoding');
		header('Cache-Control:public, max-age=604800');
		header('X-Powered-By: itTeka.ru');
		echo gzencode($data, 9);
	} else echo $data;	
}
else {
	header("HTTP/1.0 404 Not Found");
	die('Not Found');
}