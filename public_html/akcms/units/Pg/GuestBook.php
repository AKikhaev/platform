<?php # Общая гостевая книга

class Pg_GuestBook extends PgUnitAbstract {

	public function initAjx()
	{
		global $page;
		return array(
		'_gbsve' => array(
			'func' => 'ajxIsve',
			'object' => $this),
		'_gbdrp' => array(
			'func' => 'ajxIdrp',
			'object' => $this),
		);
	}
  
	public function _rigthList()
	{
		return array(
		);
	}

	public function initAcl()
	{
		return array(
		'admin'=>true,
		'owner'=>true,
		'default'=>null
		);
	}
  
	public function ajxIsve()
	{
		global $cfg,$sql,$page;
		$checkRule = array();
		$checkRule[] = array('gb_id'      , '/^\\d{1,}$/');
		$checkRule[] = array('gb_name'    , '.');
		$checkRule[] = array('gb_message' , '.');
		$checkRule[] = array('gb_answer'  , '');
		$checkRule[] = array('gb_answerer_id', '/^\\d{1,}$/');
		$checkRule[] = array('gb_tags'    , '');
		$checkRule[] = array('gb_sendmail' , '/^(t|f)$/');
		$checkRule[] = array('gb_enabled' , '/^(t|f)$/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query = sprintf ('UPDATE cms_gb SET gb_name=%s,gb_message=%s,gb_answer=%s,gb_answerer_id=%d,gb_enabled=%s WHERE gb_id=%d;',
				$sql->t($_POST['gb_name']),
				$sql->t($_POST['gb_message']),
				$sql->t($_POST['gb_answer']),
				$_POST['gb_answerer_id'],
				$sql->t($_POST['gb_enabled']),
				$_POST['gb_id']
			);
			$res = $sql->command($query)>0;
			
			if ($_POST['gb_sendmail']=='t') {
				$query = sprintf ('SELECT * FROM cms_gb WHERE gb_enabled AND gb_id=%d;',
					$_POST['gb_id']
				);
				$dataset = $sql->query_all($query);
				if ($dataset!==false) {
					$title='Поступил ответ на ваш вопрос на сайте '.$_SERVER['HTTP_HOST'].'';
					$uform = array();
					$uform['urlhref'] = 'http://'.$_SERVER['SERVER_NAME'].'/'.$page->pageMainUri.'#gbia'.$_POST['gb_id'];
					$uform['url'] = 'http://'.$_SERVER['SERVER_NAME'].'/'.$page->pageMainUri;
					$htmlform = GetShape('gb_mail_user', $uform);
					sendMailHTML($dataset[0]['gb_email'], $title, $htmlform,'',$cfg['email_from_user']);
				}
			}
			
			$tags = trim($_POST['gb_tags'])==''?array():explode(',',$_POST['gb_tags']);
			$query = sprintf ('SELECT __cms_gb_tags__assign(%d,%s);',
				$_POST['gb_id'],
				$sql->pgf_array_text($tags)
			);			
			$db_res = $sql->query_first_row($query);
			
			return json_encode(($res && $db_res[0]=='t')?'t':'f');
		} 
		return json_encode(array('error'=>$checkResult));
	}
  
	public function ajxIdrp()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('gb_id', '/^\\d{1,}$/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0) 
		{			
			$query = sprintf ('SELECT __cms_gb_tags__assign(%d,%s);',
				$_POST['gb_id'],
				$sql->pgf_array_text(array())
			);			
			$db_res = $sql->query_first_row($query);
			
			$query = sprintf ('DELETE FROM cms_gb WHERE gb_id=%d;',
				$_POST['gb_id']
			);
			$res = $sql->command($query)>0?'t':'f';
			
			return json_encode($res);
		} 
		return json_encode(array('error'=>$checkResult));
	}
	
	public function getITags($id) // Теги привязанные к элементу
	{
		global $sql;
		$res = array();
		$query = sprintf ('SELECT a.gbt_text as txt FROM cms_gb_tags a INNER JOIN cms_gb_tags_gb b ON (a.gbt_id=b.gbt_id) WHERE b.gb_id=%d ORDER BY 1;', 
		$id);
		$dataset = $sql->query_all($query);
		if ($dataset!=false) foreach ($dataset as $dataItem) $res[] = $dataItem['txt']; 
		return $res;
	}

	public function getAllTags($str='') // Все теги
	{
		global $sql;
		$res = array();
		$query = sprintf ('SELECT a.gbt_text as txt FROM cms_gb_tags a where a.gbt_text LIKE %s ORDER BY 1',
            $sql->t('%'.$str.'%')
        );
		$dataset = $sql->query_all($query);
		if ($dataset!=false) foreach ($dataset as $dataItem) $res[] = $dataItem['txt']; 
		return $res;
	}
  
	public function render()
	{
		global $cfg,$sql,$page;
		$html = '';
		$editMode = $this->hasRight() && core::$inEdit;
		$pageLinkUri = '/'.($editMode?'_/':'').$page->pageMainUri;
		
		if ((count($this->unitParam)==0) || (count($this->unitParam)==1?preg_match('/^\d{1,3}$/',$this->unitParam[0])==1:false))
		{
			$t = isset($_GET['t'])?$_GET['t']:'';
			$uform['errmsg'] = ''; $uform['msg'] = '';
			if (isset($_POST['submitgb'])) {
				$uform['name']  = htmlentities(strip_tags(isset($_POST['name'])?$_POST['name']:''),ENT_QUOTES,'UTF-8');
				$uform['email'] = htmlentities(strip_tags(isset($_POST['email'])?$_POST['email']:''),ENT_QUOTES,'UTF-8');
				$uform['phone'] = htmlentities(strip_tags(isset($_POST['phone'])?$_POST['phone']:''),ENT_QUOTES,'UTF-8');
				$uform['text']  = htmlentities(strip_tags(isset($_POST['text'])?$_POST['text']:''),ENT_QUOTES,'UTF-8');
				$capcha = isset($_POST['capcha'])?$_POST['capcha']:'';	 
				$orgcodeOrig = isset($_SESSION['securityCode'])?$_SESSION['securityCode']:'_';
				$checkRule = array();
				$checkRule[] = array('name' , '.');
				$checkRule[] = array('email' , '.');
				$checkRule[] = array('phone' , '.');
				$checkRule[] = array('text' , '.');
				$checkResult = checkForm($_POST,$checkRule,$capcha==$orgcodeOrig);
				if (count($checkResult)>0) {
					if ($checkResult[0]['f']=='!') $uform['errmsg'] = 'Неверный проверочный код!';
					else $uform['errmsg'] = 'Заполнены не все поля!';
				} else {
					$query = sprintf ('INSERT INTO cms_gb(gb_name,gb_email,gb_phone,gb_message) VALUES (%s,%s,%s,%s) RETURNING gb_id;', 
						$sql->t($_POST['name']),
						$sql->t($_POST['email']),
						$sql->t($_POST['phone']),
						$sql->t($_POST['text'])
						);
					$res = $sql->query_first_row($query);
					if ($res!==false && $res!==null) {
						$title=$_SERVER['HTTP_HOST'].' Новый вопрос';
						$uform['ip'] = $_SERVER['REMOTE_ADDR']; 
						$uform['url'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
						$uform['urlhref'] = 'http://'.$_SERVER['SERVER_NAME'].'/_/'.$page->pageMainUri.'#gbia'.$res[0];
						$htmlform = GetShape('gb_mail_moder', $uform);
						sendMailHTML($cfg['email_moderator'], $title, $htmlform,'',$cfg['email_from']);
						
						$uform['name'] = ''; $uform['email'] = ''; $uform['phone'] = ''; $uform['text'] = ''; 
						$uform['msg'] = 'Ваш вопрос отправлен и в скором времени получит ответ.';
					} else $uform['errmsg'] = 'Не удалось отправить вопрос. Попробуйте снова позднее.';
				}
			} else {
				$uform['name'] = ''; $uform['email'] = ''; $uform['phone'] = ''; $uform['text'] = '';
			}
			$htmlform = GetShape('gb_send', $uform);

			$pgNum = 1;	if (count($this->unitParam)==1) $pgNum = $this->unitParam[0]>0?$this->unitParam[0]:1;
			$pgSize = 10; 
			
			//if ()
			$qpart = 'from cms_gb b LEFT JOIN cms_gallery_photos g ON(b.gb_answerer_id=g.id_cgp) WHERE (g.cgp_enabled=true OR g.cgp_enabled IS NULL)'
			.($t==''?'':' AND b.gb_id in (SELECT gb_id FROM georghram.cms_gb_tags_gb tgb INNER JOIN georghram.cms_gb_tags bt ON (tgb.gbt_id=bt.gbt_id) WHERE gbt_text = '.$sql->t(strip_tags($t)).')')
			.($editMode?'':' AND b.gb_enabled');
			$query = 'select count(*) as totalrecords '.$qpart;
			$totalset = $sql->query_first_assoc($query); $countRecords = $totalset['totalrecords'];
			$query = sprintf ('select b.*,g.cgp_name as answerer,g.cgp_file as photo '.$qpart.' order by b.gb_date desc');
			$query = sprintf ($query.' LIMIT %d OFFSET (%d-1)*%d;',
				$pgSize,
				$pgNum,
				$pgSize);
			$dataset = $sql->query_all($query);
			$pgNums = ceil($countRecords/$pgSize);
			if ($pgNums==0) $pgNums = 1;
			if ($pgNum<1 || $pgNum>$pgNums)
				throw new CmsException('page_not_found');
			
			if ($editMode) ;
			$tagsAll = $this->getAllTags();
			$tagsAllLinks = array();
			foreach ($tagsAll as $tag) $tagsAllLinks[] = '<a href="'.$pageLinkUri.'?t='.urlencode($tag).'">'.$tag.'</a>';
			$html .= '<div class="gstbktgs"><a href="?">Все теги</a>: '.implode(', ',$tagsAllLinks).'</div>';
			$html .= '<div id="gstbk">';
			$u_items = array();
			if ($dataset!==false) foreach ($dataset as $dataItem)
			{
				$dataItem['tags'] = $this->getITags($dataItem['gb_id']);
				$tagsArray = array();
				foreach ($this->getITags($dataItem['gb_id']) as $tag) $tagsArray[] = '<a href="'.$pageLinkUri.'?t='.urlencode($tag).'">'.$tag.'</a>';
				$tags = implode(', ',$tagsArray);
				$u_items[$dataItem['gb_id']] = $dataItem;
				$html .= '<div class="gbitem'.($dataItem['gb_enabled']=='f'?' imtdsbl':'').'" id="gbi'.$dataItem['gb_id'].'"><a name="gbia'.$dataItem['gb_id'].'"></a><div><span class="gbidate">'.date('y.m.d',strtotime($dataItem['gb_date'])).'</span> <span class="gbiname">'.$dataItem['gb_name'].'</span></div><div class="gbimsg">'.str_replace("\n",'<br/>',$dataItem['gb_message']).'</div>';
				if ($dataItem['gb_answer']!='') {				
					if ($dataItem['answerer']==null)
					$html .= '
					<div class="gbianswr">Ответ:</div>
					<div class="gbiansw">'.str_replace("\n",'<br/>',$dataItem['gb_answer']).'</div>
					<div class="gbitgs"><b>Теги</b>: '.$tags.'</div>';
					else
					$html .= '
					<table border="0"><tbody><tr><td valign="top"><img class="gbianswrph" alt="'.$dataItem['answerer'].'" title="'.$dataItem['answerer'].'" src="/img/gallery/answr/'.$dataItem['photo'].'"></td>
					<td valign="top"><div class="gbianswr">Отвечает <span>'.$dataItem['answerer'].'</span></div>
					<div class="gbiansw">'.str_replace("\n",'<br/>',$dataItem['gb_answer']).'</div>
					<div class="gbitgs"><b>Теги</b>: '.$tags.'</div></td></tr></tbody></table>';
				} else if (count($tagsArray)>0) $html .= '<div class="gbitgs"><b>Теги</b>: '.$tags.'</div>';
				$html .= '</div>';
			}
			$html .= '</div>';
			if ($pgNums>1)
			$html .= '<div class="pager">'.makePager($countRecords, $pgSize, $pgNum, $pageLinkUri.'{pg}').'</div>';
			
			$query = 'SELECT id_cgp as k,cgp_name as v FROM cms_gallery_photos WHERE cgp_enabled and cgp_glr_id=2;';
			$dataset = $sql->query_all($query);

			$html .= $htmlform;
			if ($editMode) {
				$u_data = array(
					'i'=>$u_items,
					'a'=>$dataset===false?array():$dataset,
					't'=>$tagsAll
				);
				$html .= '<script type="text/javascript" src="/akcms/js/v1/pg_gb_ed.js"></script><script type="text/javascript">var gbi='.json_encode($u_data).';</script>';
			}
		} else throw new CmsException('page_not_found');
		return $html;
	}
  
}
