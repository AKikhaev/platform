<?php # import from wp
if(php_sapi_name()!=='cli')die('<!-- not allowed -->');
die();
chdir('../..');
ini_set('memory_limit', '228M');
require_once('akcms/core/functs.php'); # functions
require_once('akcms/core/core.php'); LOAD_CORE_CLI();

$remote_sec = $_SERVER['argv'][1];
$local_sec = $_SERVER['argv'][2];
$remote_arts_raw = (file_get_contents('http://yugtimes.com/__exprt/e.php?s='.$remote_sec));
$remote_arts = json_decode($remote_arts_raw);

function wp_cleaner($str) {
	$str = preg_replace('/wp-image-\d+/','',$str);
	$str = str_replace('size-medium','',$str);
	$str = preg_replace('/wp-image-\d+/','',$str);
	$str = preg_replace('/\s{2,}/',' ',trim($str));
	return $str;
}

function rrmdir($dir) {
	foreach(glob($dir . '/*') as $file) {
		if(is_dir($file))
		rrmdir($file);
		else unlink($file);
	}
	rmdir($dir);
}

try {
	$i = 0; $c = count($remote_arts);
	toLogInfo('Всего статей '.$c);
	foreach($remote_arts as $r_id) {
		$i++;
		$query = 'select * from pasted where r_id='.$r_id;
		$rowPasted = $sql->query_fa($query);
		if ($rowPasted===false || $rowPasted['again']==='t') {
			toLogInfo($r_id.': Получение данных ('.$i.' из '.$c.')'.($rowPasted!==false?' ПОВТОРНО':''));
			$remote_art_raw = (file_get_contents('http://yugtimes.com/__exprt/e.php?a='.$r_id));
			$remote_art = json_decode($remote_art_raw);
			#toLogDie__($remote_art->content);
			$sql->command('begin');
			//Доавляем статью
			$query = $sql->pr_i('cms_sections',array(
				'sec_parent_id'=>$sql->d($local_sec),
				'sec_url'=>$sql->t(Title2Uri($r_id.'_'.$remote_art->title)),
				'sec_nameshort'=>$sql->t($remote_art->title),
				'sec_namefull'=>$sql->t($remote_art->title),
				'sec_page'=>$sql->t('second'),
				'sec_content'=>$sql->t($remote_art->content),
				'sec_from'=>$sql->t($remote_art->date),
				'sec_created'=>$sql->t($remote_art->date),
			)).' returning section_id,sec_url_full';
			if ($rowPasted===false) {
				$row = $sql->query_fa($query);
				$l_id = $row['section_id']; $l_urlfull = $row['sec_url_full'];
			} else {
				$l_id = $rowPasted['l_id']; $l_urlfull = $rowPasted['urlfull'];
			}
			$pathto = $GLOBALS['cfg']['imagespath'].mb_strtolower($l_urlfull);
			if (is_dir($pathto)) rrmdir($pathto);
			//Главное изображение

			$res = PageUnit::SecIUploadUrl($l_id,$remote_art->img,500,500,0);
				if ($res['res']!==true) toLogError($r_id.': Bad image: '.$remote_art->img);
			
			//Теги
			$query = sprintf ('select __cms_tags_sections__assign(%d,%s);', 
				$l_id,
				$sql->pgf_array_text($remote_art->tags)
			);			
			$db_res = $sql->query_first_row($query);			
				if ($db_res[0]!='t') toLogError($r_id.': Bad tags');

			//Удалить классы wordpres
			$remote_art->content = preg_replace('/(?<= class=")[^"]+(?=")/uie','wp_cleaner(\'\\0\')',$remote_art->content);
			$remote_art->content = preg_replace('/(?<= class=\')[^\']+(?=\')/uie','wp_cleaner(\'\\0\')',$remote_art->content);
			
			//Выкачиваем изображения
			$res = PageUnit::ImagesGrab($remote_art->content,$l_urlfull);
			$remote_art->content = $res['html']; toLogInfo($r_id.': Изображений '.count($res['imggrbd']));
			
			$query = $sql->pr_u('cms_sections',array(
				'sec_content'=>$sql->t($remote_art->content),
			),'section_id='.$l_id);
			if ($sql->command($query)!==1) toLogError($r_id.': Can not saved grabbed html');

			//Поиск
			$indstr = strip_tags($remote_art->title.' '.$remote_art->content);
			$base_forms = PageUnit::makeSrchWords($indstr);
			$query = sprintf ('select __cms_srchwords_sections__assign(%d,%s);', 
				$l_id,
				$sql->pgf_array_text($base_forms));
			$res=$sql->query_fr($query);
			if ($res[0]!=='t') toLogError($r_id.': Can not make search');

			$sql->command('commit');

			if ($rowPasted===false) $query = $sql->pr_i('pasted',array(
				'r_id'=>$sql->d($r_id),
				'l_id'=>$sql->d($l_id),
				'urlfull'=>$sql->t($l_urlfull),
				'prntsec_id'=>$sql->d($remote_sec),
			));
			else $query = $sql->pr_u('pasted',array('again'=>'false'),'r_id='.$r_id);
			if ($sql->command($query)) toLogInfo($r_id.': Готово');
				else toLogError($r_id.': Не удалось сохранить в обработанные');
			#toLogDie__('Первый круг');
			
			toLogInfo('memory:'.(memory_get_peak_usage()/1024/1024));

		} else toLogError($r_id.': Статья уже существует ');
	}
	toLogInfo('Завершено!');
	//$query = "";
	//$res = $sql->command($query);
	echo "\nГотово\n";
} catch (Exception $e) {
	$sql->command('rollback');
	echo 'Caught exception: ',  $e->getMessage(), "\n";
}
