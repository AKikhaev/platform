<?php

class Pg_News extends PgUnitAbstract {
	public $imgnewspath = 'img/news/';
	
	public function initAjx()
	{
		global $page;
		return array(
		'_newsins' => array(
			'func' => 'ajxNewsins',
			'object' => $this),
		'_newssve' => array(
			'func' => 'ajxNewssve',
			'object' => $this),
		'_newsiupl' => array(
			'func' => 'ajxNewsIUpload',
			'object' => $this),	
		'_newsidrp' => array(
			'func' => 'ajxNewsIDrp',
			'object' => $this),			
		'_newsdrp' => array(
			'func' => 'ajxNewsdrp',
			'object' => $this),
		);
	}
  
	public function _rigthList()
	{
		return array(
		);
	}

	public function reindex($indxnews)
	{
		if (!$this->hasRight()) return false;
		global $sql,$page;
		$indstr = strip_tags($indxnews['news_head'].' '.$indxnews['news_short'].' '.$indxnews['news_content']);
		$base_forms = $page->makeSrchWords($indstr);

		$query = sprintf ('select __cms_srchwords_news__assign(%d,%s);', 
			$indxnews['news_id'],
			$sql->a_t($base_forms));
		$res_count = $sql->command($query);
		return $res_count>0?'t':'f';
	}

  
	public function ajxNewsins()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('news_content'   , '');
		$checkRule[] = array('news_date'      , '/^(19|20)\d{2}-\d{2}-\d{2}$/');
		$checkRule[] = array('news_detaillink', '/^(t|f)$/');
		$checkRule[] = array('news_enabled'   , '/^(t|f)$/');
		$checkRule[] = array('news_head'      , '');
		#$checkRule[] = array('news_id'       , '/^\\d{1,}$/');
		$checkRule[] = array('news_short'     , '.');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query = sprintf ('INSERT INTO cms_news(news_date,news_head,news_short,news_content,news_enabled,news_detaillink) VALUES (%s,%s,%s,%s,%s,%s) RETURNING news_id;', 
			$sql->t($_POST['news_date']),
			$sql->t($_POST['news_head']),
			$sql->t($_POST['news_short']),
			$sql->t($_POST['news_content']),
			$sql->t($_POST['news_enabled']),
			$sql->t($_POST['news_detaillink'])
			);
			$res = $sql->query_first_row($query);
			if ($res!==false) {
				$_POST['news_id'] = $res[0];
				$this->reindex($_POST);
			}
			return json_encode($res!==false?'t':'f');
		} 
		return json_encode(array('error'=>$checkResult));
	}    
  
	public function ajxNewssve()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('news_content'   , '');
		$checkRule[] = array('news_date'      , '/^(19|20)\d{2}-\d{2}-\d{2}$/');
		$checkRule[] = array('news_detaillink', '/^(t|f)$/');
		$checkRule[] = array('news_enabled'   , '/^(t|f)$/');
		$checkRule[] = array('news_head'      , '');
		$checkRule[] = array('news_id'       , '/^\\d{1,}$/');
		$checkRule[] = array('news_short'     , '.');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query = sprintf ('UPDATE cms_news SET news_date=%s,news_head=%s,news_short=%s,news_content=%s,news_enabled=%s,news_detaillink=%s WHERE news_id=%d;', 
				$sql->t($_POST['news_date']),
				$sql->t($_POST['news_head']),
				$sql->t($_POST['news_short']),
				$sql->t($_POST['news_content']),
				$sql->t($_POST['news_enabled']),
				$sql->t($_POST['news_detaillink']),
				$_POST['news_id']
				);
			$res = $sql->command($query)>0?'t':'f';
			if ($res === 't') $this->reindex($_POST);
			return json_encode($res);
		} 
		return json_encode(array('error'=>$checkResult));
	}
	
	public function ajxNewsIUpload()
	{
		global $sql,$page;
		$res_msg = ''; $res_stat = 0; $res_i_file = '';
		$JsHttpRequest = new JsHttpRequest('UTF-8');
		$checkRule = array();
		$checkRule[] = array('news_id', '/^\d+/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			if (isset($_FILES['uplfile'])?$_FILES['uplfile']['tmp_name']:false)
			{
				$upl = $_FILES['uplfile'];
				if (is_uploaded_file($upl['tmp_name']))
				{
					if ($upl['size']>0)
					{
						if ($this->hasRight()) {
							$file_name = mb_strtolower($upl['name']);
							$file_ext = str_replace('.','',mb_substr($file_name,mb_strrpos($file_name,'.')));
							if ($file_ext === 'jpg')
							{
								$res_stat = 1;
								$max_width = 85;
								$max_height = 93;
								$imginfo = false;
								
								try {
									$imgRszr = new ImgResizer();
									$dst = $imgRszr->simpleResize($upl['tmp_name'],$max_width,$max_height,1);
									$imginfo = $imgRszr->imginfo;						
								} catch(Exception $e) {}									

								if ($imginfo!==false)
								{
									$res_stat = 2;

									if ($_POST['news_id']>0)
									{
										//$res_stat = 3;
										$res_stat = 4;
										$i_file = $_POST['news_id'].'.jpg';
										$pathstr = $this->imgnewspath.$i_file;
										$dirpath = dirname($pathstr);
										if (!file_exists($dirpath)) mkdir($dirpath,0755,true);       
										@unlink($this->imgnewspath.$i_file);
										@unlink($this->imgnewspath.'s/'.$i_file);
										@unlink($this->imgnewspath.'t/'.$i_file);
										imagejpeg($dst,$pathstr,90);

										$query = sprintf ('UPDATE cms_news SET news_image = %s WHERE news_id = %d;', 
											$sql->t($i_file),
											$_POST['news_id']);
										$res_count = $sql->command($query);

										$res_i_file = $i_file;
										
									} else $res_msg = 'Неверный код новости!';

									imagedestroy($dst);               
									/**/
											
								} else $res_msg = 'Неверный формат файла!';
							} else $res_msg = 'Неверный формат! Поддерживается: .jpg';
						} else $res_msg = 'У вас нет прав!';
					} else $res_msg = 'Файл пуст!';
				} else $res_msg = 'Не тот файл!';
			} else $res_msg = 'Файл не передан!';
		} else $res_msg = 'Неверные значения!';
		$GLOBALS['_RESULT'] = array(
			'status'=> $res_stat,
			'msg'   => $res_msg,
			'i_file'=> $res_i_file
		);
		return $JsHttpRequest->_obHandler('');
	}

	public function ajxNewsIDrp()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('news_id', '/^\d+/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query =  sprintf('UPDATE cms_news SET news_image=\'\' WHERE news_id = %d RETURNING news_image;',
				$_POST['news_id']);
			$result = $sql->query_first_row($query);
			if ($result!=false) {
				$filename = $_POST['news_id'].'.jpg';//$result[0];
				@unlink($this->imgnewspath);
				@unlink($this->imgnewspath.'s/'.$filename);
				@unlink($this->imgnewspath.'t/'.$filename);
				$res = 't';
				return json_encode($res);
			} else $checkResult['db'] = 'mstk';
		}
		return json_encode(array('error'=>$checkResult));   
	} 	
  
	public function ajxNewsdrp()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('news_id'       , '/^\\d{1,}$/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0) 
		{
			$query = sprintf ('DELETE FROM cms_news WHERE news_id=%d; DELETE FROM cms_srchwords_news WHERE news_id=%d;', 
				$_POST['news_id'],$_POST['news_id']
			);
			$res = $sql->command($query)>0?'t':'f';
			$filename = $_POST['news_id'].'.jpg';
			@unlink($this->imgnewspath.$filename);
			@unlink($this->imgnewspath.'s/'.$filename);
			@unlink($this->imgnewspath.'t/'.$filename);
			return json_encode($res);
		} 
		return json_encode(array('error'=>$checkResult));
	}  
  
	public function render()
	{
		global $sql,$page;
		$res = '';
		$editMode = $this->hasRight();
		
		if (count($this->unitParam)==1?preg_match('/^n\d{1,3}$/',$this->unitParam[0])==1:false)
		{
			$newsId = substr($this->unitParam[0],1);
			$query = sprintf ('select * from cms_news where '.($editMode?'':'news_enabled and').' news_id=%d;', 
			$newsId);
			$dataset = $sql->query_all($query);
			if (count($dataset)==0 || $dataset==false) throw new CmsException('page_not_found');
			$newsItem = $dataset[0];
			$res .= '<div id="news"><div class="newsitem" id="newsi'.$newsItem['news_id'].'"><div class="newidate">'.DtDbFormatDate('d/m/Y',$newsItem['news_date']).'</div><div class="newicnt">'.$newsItem['news_content'].'</div></div></div>';  
			$res .= '<div class="news_under"><a href="/'.$page->pageMainUri.'" title="Все новости">Все новости <img src="/img/arr_r.gif" border="0" alt="" height="7" width="8" /></a></div>'; 
			if ($editMode) $res .= '<script type="text/javascript" src="/akcms/js/v1/pg_news_ed.js"></script><script type="text/javascript">var newsi='.json_encode(array($newsItem['news_id']=>$newsItem,'noadd'=>true)).';</script>';
		} elseif ((count($this->unitParam)==0) || (count($this->unitParam)==1?preg_match('/^\d{1,3}$/',$this->unitParam[0])==1:false))
		{
			$pgNum = 1;	if (count($this->unitParam)==1) $pgNum = $this->unitParam[0]>0?$this->unitParam[0]:1;
			$pgSize = 10;
			
			$query = 'select count(*) as totalrecords from cms_news '.($editMode?'':'where news_enabled');
			$totalset = $sql->query_first($query); $countRecords = $totalset['totalrecords'];
			$query = sprintf ('select * from cms_news '.($editMode?'':'where news_enabled').' order by news_date desc LIMIT %d OFFSET (%d-1)*%d;',
				$pgSize,
				$pgNum,
				$pgSize);
			$dataset = $sql->query_all($query);
			$pgNums = ceil($countRecords/$pgSize);
			if ($pgNum<1 || $pgNum>$pgNums)
				throw new CmsException('page_not_found');
			
			$res .= '<div id="news">';
			$news_items = array();
			if ($dataset!==false) foreach ($dataset as $newsItem)
			{
				$news_items[$newsItem['news_id']] = $newsItem;
				$res .= '<div class="newsitem'.($newsItem['news_enabled'] === 'f'?' imtdsbl':'').'" id="newsi'.$newsItem['news_id'].'"><div class="newidate">'.DtTmToDtStr($newsItem['news_date']).'</div><div class="newihead">'.$newsItem['news_head'].'</div><div class="newicnt">'.$newsItem['news_short'].($newsItem['news_detaillink'] === 't'?' <a href="/'.$page->pageMainUri.'n'.$newsItem['news_id'].'/">Подробнее...</a>':'').'</div></div>';
			}
			$res .= '</div>';
			if ($pgNums>1)
			$res .= '<div class="pager">'.makePager($countRecords, $pgSize, $pgNum, '/'.$page->pageMainUri.'{pg}/').'</div>';
			if ($editMode) $res .= '<script type="text/javascript" src="/akcms/js/v1/pg_news_ed.js"></script><script type="text/javascript">var newsi='.json_encode($news_items).';</script>';
		} else throw new CmsException('page_not_found');
		return $res;
	}
  
}
