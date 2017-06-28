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
$path = getcwd ().'/..'.$pathstr;
$fileinf = pathinfo($path);
if (isset($fileinf['extension'])) {
	if (!in_array($fileinf['extension'],array('js','css','htm','html','xml'))) $encoding = false;
	if (!in_array($fileinf['extension'],array('js','css','htm','html','gif','png','jpg','swf','xml'))) { header("HTTP/1.0 423 Locked"); exit('Locked'); }
} else { header("HTTP/1.0 423 Locked"); exit('Unexpected locked'); }
if (file_exists($path)) {
	$data = file_get_contents($path);
	header('Expires: ' . gmdate('D, d M Y H:i:s',time()+604800) . ' GMT');
	header('Last-Modified: ' . gmdate ("D, d M Y H:i:s", filemtime($path)) . ' GMT');
	header('Vary: Accept-Encoding');
	header('Cache-Control:public, max-age=604800');
	header('X-Powered-By: kubado.ru');
	switch ($fileinf['extension']) {
		case 'js'  : header('Content-Type: application/x-javascript'); {
			//require_once('../akcms/classes/jsMin.php'); $data = JSMin::minify($data);
			//require_once('../akcms/classes/jsMinifier.php'); $jsMin = new Minifier(); $data = $jsMin->minify($data);
		}
		break;
		case 'css' : header('Content-Type: text/css'); {
			#require_once('../akcms/classes/cssMin.php'); $data = CssMin::minify($data);		
		}
		break;
		case 'htm' :
		case 'html': header('Content-Type: text/html; charset=UTF-8'); break;
		case 'gif' : header('Content-Type: image/gif'); break;
		case 'png' : header('Content-Type: image/png'); break;
		case 'jpg' : header('Content-Type: image/jpeg'); break;
		case 'swf' : header('Content-Type: application/x-shockwave-flash'); break;
	}
	if ($encoding!=false) {
		header('Content-Encoding: '.$encoding);
		echo gzencode($data, 9);
	} else echo $data;
	exit;
}
header("HTTP/1.0 404 Not Found");
echo 'Not Found';
