<?php
require_once('Face_Detector.php');

try {
	if (isset($_REQUEST['u'])) {
		$filePath = str_replace('..', '_', $_REQUEST['u']);
		$pathstrOrgn = getcwd().'/../../img/'.$filePath;
		$pathstrMeta = getcwd().'/../../s/_metadata/'.$filePath.'.dat';	
		#die($pathstrMeta);
		if (file_exists($pathstrOrgn)) {
			$dirpath = dirname($pathstrMeta);
			if (!file_exists($dirpath)) mkdir($dirpath,0755,true);       

			@set_time_limit(5 * 60);
			$detector = new Face_Detector('detection.dat');
			$detector->face_detect($pathstrOrgn);
			$face = $detector->getFace();
			$imgM = array(
				'x'=>floor($face['x']+$face['w'] / 2),
				'y'=>floor($face['y']+$face['w'] / 2),
				'r'=>'t',
			);
			$imgMstr = $imgM['x'].'|'.$imgM['y'];
			$dirpath = dirname($pathstrOrgn);
			$filename = basename($pathstrOrgn);
			@array_map('unlink',glob($dirpath.'/*/'.$filename));
			echo json_encode($imgM);
			file_put_contents($pathstrMeta,$imgMstr);
		} else  throw new Exception('imgr_wrong_path');
	} else throw new Exception('imgr_no_path');

} catch(Exception $e) {
	header("HTTP/1.0 404 Not Found"); 
	exit($e->getMessage());	
}