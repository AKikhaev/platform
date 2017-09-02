<?php
	@set_time_limit(60*3);
	@ini_set('memory_limit', '64M');
	require_once '../../akcms/u/config/config.php';
	
	if (!isset($_SERVER['SCRIPT_URL'])) {
		$pathstr = $_SERVER['REQUEST_URI'];
		if (mb_strpos($pathstr,'?')!==false) $pathstr = mb_substr($pathstr,0,mb_strpos($pathstr,'?'));
		if (mb_strpos($pathstr,'#')!==false) $pathstr = mb_substr($pathstr,0,mb_strpos($pathstr,'#'));
		if (mb_strpos($pathstr,'&')!==false) $pathstr = mb_substr($pathstr,0,mb_strpos($pathstr,'&'));
	} else $pathstr = $_SERVER['SCRIPT_URL'];

	$url = isset($_REQUEST['url'])?$_REQUEST['url']:'';
	$urlpath = getcwd().'/../..'.$url;
	$width = (int)(isset($_REQUEST['w'])?$_REQUEST['w']:'');
	$height = (int)(isset($_REQUEST['h'])?$_REQUEST['h']:'');
	$justOutput = isset($_REQUEST['jo']);
	try {
		if (basename($pathstr)==='index.php') throw new Exception('imgr_wrong_url');
		if (strpos($url,'/'.$cfg['imagespath'])!==0 || strlen($url)<=strlen($cfg['imagespath'])+5) throw new Exception('imgr_wrong_path');
		if (!file_exists($urlpath)) throw new Exception('imgr_not_found');
		if ($width===0 || $height===0) throw new Exception('imgr_wrong_size');
		
		require_once '../../akcms/classes/ImgResizer.php';
		$imgRszr = new ImgResizer();
		echo json_encode($imgRszr->ResizeSave($urlpath,$urlpath,$width,$height,$justOutput?1:2,$justOutput?1:false,null)===true?'t':'f');
	} catch(Exception $e) {
		header('HTTP/1.0 404 Not Found');
		exit($e->getMessage());	
	}
