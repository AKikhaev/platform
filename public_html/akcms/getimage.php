<?php

class ImgEffcts_glowborder {
	private $space = 2;
	function __construct($space) {$this->space = $space;}	
	function apply($img,$width,$height){
		imagerectangle($img,$this->space,$this->space,$width-$this->space-1,$height-$this->space-1,imagecolorallocate($img,255,255,255));
	}
}

class ImgEffcts_watermark {
	private $waterfile = '';
	private $corner = 0;
	private $x_offset = 5;
	private $y_offset = 5;
	/*******
	 *0 4 1*
	 *7   5*
	 *3 6 2*
	 ********/
			
	function __construct($waterfile,$corner=2,$x_offset=2,$y_offset=2) {
				$this->waterfile = $waterfile;
				$this->corner = $corner;
				$this->x_offset = $x_offset;
				$this->y_offset = $y_offset;
			}
			
	function apply($img,$width,$height){
				$stamp = imagecreatefrompng($this->waterfile);
				$s_w = imagesx($stamp); $s_h = imagesy($stamp);
				switch ($this->corner) {
					case 0: $x = $this->x_offset; $y = $this->y_offset;
							break;
					case 1: $x = $width - $this->x_offset - $s_w; $y = $this->y_offset;
							break;
					case 2: $x = $width - $this->x_offset - $s_w; $y = $height - $this->y_offset - $s_h;
							break;
					case 3: $x = $this->x_offset; $y = $height - $this->y_offset - $s_h;
							break;
					case 4: $x = ($width - $s_w) / 2; $y = $this->y_offset;
							break;
					case 5: $x = $width - $this->x_offset - $s_w; $y = ($height - $s_h) / 2;
							break;
					case 6: $x = ($width - $s_w) / 2; $y = $height - $this->y_offset - $s_h;
							break;
					case 7: $x = $this->x_offset; $y = ($height - $s_h) / 2;
							break;
				}
				imagecopy($img, $stamp, $x, $y, 0, 0, $s_w, $s_h);
	}
}
	
header('X-Powered-By: itteka.ru');
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
#if (strpos($pathstr,'/img/')===0) $pathstr = substr($pathstr,5);
$path = array();  
foreach (explode('/',$pathstr) as $item) if ($item != '') $path[] = $item;
$pathstr = implode('/',$path);

$pathlen = count($path);
$max_width = -1;
$max_height = -1;
$mode = 0;

try {
	require_once('../akcms/classes/ImgResizer.php');
	$imgRszr = new ImgResizer();
	$prePath = getcwd().'/../';

	if ($pathlen==4) 
	{
		require('../akcms/u/config/images.php');
	}
	else throw new Exception('imgr_wrong_path_len');

	if (strpos($pathstrOrgn,'.flv.jpg')==strlen($pathstrOrgn)-8)
		$pathstrOrgn = substr($pathstrOrgn,0,strlen($pathstrOrgn)-4);
		
	$meta = ImgMetaData::getMetaByName($metaPathStr,$prePath);
	//var_dump($meta);die();

	$pathstrOrgn = $prePath.$pathstrOrgn;
	$pathstr = $prePath.$pathstr;


	if ($meta==false) $imgRszr->ResizeSave($pathstrOrgn,$pathstr,$max_width,$max_height,$mode,true,$effector); 
	else $imgRszr->ResizeSave($pathstrOrgn,$pathstr,$max_width,$max_height,$mode,true,$effector,$meta[0],$meta[1]);	
} catch(Exception $e) {
	header("HTTP/1.0 404 Not Found"); 
	exit($e->getMessage());	
}