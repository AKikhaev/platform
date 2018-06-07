<?php // Storage optimizer



if(php_sapi_name()!=='cli')die('<!-- not allowed -->');
ini_set('display_errors',1);
error_reporting (E_ALL);
$sql->command('set transform_null_equals=true;');
require_once('akcms/classes/ImgResizer.php');


function rrmdir($dir) {
    foreach(glob($dir . '/*') as $file) {
        if(is_dir($file))
            rrmdir($file);
        else unlink($file);
    }
    rmdir($dir);
}

$saved = 0;
$removed = 0;
function show_saved() {
	global $saved,$removed;
	toLogInfo('Сэкономлено '.number_format($saved/1024,2,'.',' ')." кб, Удалено ".$removed."                                 ");
}
register_shutdown_function('show_saved');

try {

	toTitle('Loading... ');
	if (!core::$OS_WIN) {
		$width = exec('tput cols');
		$w_blank = "\r" . str_repeat(' ', $width - 1);
	} else {

	}



    $startFrom = isset($_SERVER["argv"][1])? (int)$_SERVER["argv"][1] :0;
	$query = 'SELECT section_id,sec_nameshort,sec_content,sec_url_full FROM cms_sections WHERE section_id>='.$sql->d($startFrom).' ORDER by 1'.($startFrom>0?'':' DESC');
    $itemObj = $sql->queryObj($query);
    $remain = new remainCalc();
    $remain->init($itemObj->count(),'processing',0);
    $i = 0;
    foreach ($itemObj as $item) {
		$folder = 's/images/'.$item['sec_url_full'];
		$inHtmlFiles = array();
        preg_match_all('/<img.+?>/iu',$item['sec_content'],$imgList);
        $f = 0;
		if ($imgList[0]!=false)
        foreach($imgList[0] as $imgHtml) {
		    var_dump__($imgList[0]);
            preg_match('/(?<=src="\/|src=\'\/)(s\/images\/[^\s]+?\.jpg)(?="|\')/iu',$imgHtml,$img_src); $img_src = @$img_src[0];
            preg_match('/(?<=width="|width=\'|width=)(\d+)(?="|\'|)/iu',$imgHtml,$img_width); $img_width = @$img_width[0];
            preg_match('/(?<=height="|height=\'|height=)(\d+)(?="|\'|)/iu',$imgHtml,$img_height); $img_height = @$img_height[0];
			#toLog($img_src);
			if ($img_src !==null) {
				if (mb_stripos($img_src,$folder)==0) $inHtmlFiles[] = basename($img_src);
				$imginfo = @getimagesize($img_src);
				if ($img_height == null && $img_width != null) {
					$img_height = round($imginfo[1]/$imginfo[0]*$img_width);
				} elseif ($img_height != null && $img_width == null) {
					$img_width = round($imginfo[0]/$imginfo[1]*$img_height);
				} 
				if ($img_src !==null AND $img_width ==null AND $img_height == null) {}
				elseif ($img_src !==null AND $img_width == $imginfo[0] AND $img_height == $imginfo[1]) {}
				elseif ($img_src !==null AND $img_width !==null AND $img_height != null) {

					if ($imginfo!=null? $img_width<$imginfo[0] || $img_height<$imginfo[1] :false) {
						$f++;
						if ($f==1) toLog($item['section_id'].': '.$item['sec_nameshort'].': '.count($imgList));
						$ir = new ImgResizer();
						$size = @filesize($img_src);
						$ir->ResizeSave($img_src,$img_src,$img_width,$img_height,0);
						clearstatcache();
						$newsize = @filesize($img_src);
						$sizesave = $size - $newsize;
						toLogInfo($imginfo[0].'x'.$imginfo[1].' => '.$img_width.'x'.$img_height.' '.$img_src.' '.
						//'('.number_format($size/1024,2,'.',' ').' '.' => '.number_format($newsize/1024,2,'.',' ').' '.') '.
						number_format($sizesave/1024,2,'.',' ').' кб | '.number_format($saved/1024,2,'.',' ').' кб');
						
						$saved += $sizesave;
						usleep(450000);
					} // else toLog($img_width.'x'.$img_height.' '.$img_src);

				}  else {
					$f++;
					if ($f==1) toLog($item['section_id'].': '.$item['sec_nameshort'].': '.count($imgList));
					toLogError('ERROR '.$img_width.'x'.$img_height.' '.$img_src.' '.$item['sec_url_full']);
					var_dump__($item);
				}
			}
        }
		foreach (array_merge( 
			glob($folder.'*.[jJ][pP][gG]')
			//,glob($folder.'*.[pP][nN][gG]')
		) as $folderFile) if (!in_array(basename($folderFile),$inHtmlFiles)) {
			$saved += @filesize($folderFile);
			++$removed;
			unlink($folderFile);
			toLogError($folderFile);
		}
		#die;
        $remain->plot($i++,true,number_format($saved/1024,2,'.',' ').' кб ['.$item['section_id'].']');
        usleep(110000);
    }
	echo PHP_EOL.'Готово'.PHP_EOL;

} catch (Exception $e) {
	$sql->command('rollback');
	echo 'Caught exception: ',  $e->getMessage(), ' ', $e->getFile(), ' (',$e->getLine(),')', "\n";
	var_dump($item,$imginfo);
	sleep(6); echo PHP_EOL;
}
