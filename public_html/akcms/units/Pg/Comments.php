<?php # Комментарии внизу раздела

class Pg_Comments extends PgUnitAbstract {

	public function initAjx()
	{
		global $page;
		return array(
		'_cmntsve' => array(
			'func' => 'ajxIsve',
			'object' => $this),
		'_cmntdrp' => array(
			'func' => 'ajxIdrp',
			'object' => $this),
		);
	}
  
	public function _rigthList()
	{
		return array(
		);
	}

	public function ajxIsve()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('cmnt_enabled'     , '/^(t|f)$/');
		$checkRule[] = array('cmnt_name'        , '.');
		$checkRule[] = array('cmnt_id'          , '/^\\d{1,}$/');
		$checkRule[] = array('cmnt_message'     , '.');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query = sprintf ('UPDATE cms_comments SET cmnt_name=%s,cmnt_message=%s,cmnt_enabled=%s WHERE cmnt_id=%d;',
				$sql->t($_POST['cmnt_name']),
				$sql->t($_POST['cmnt_message']),
				$sql->t($_POST['cmnt_enabled']),
				$_POST['cmnt_id']
				);
			$res = $sql->command($query)>0?'t':'f';
			return json_encode($res);
		} 
		return json_encode(array('error'=>$checkResult));
	}
  
	public function ajxIdrp()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('cmnt_id', '/^\\d{1,}$/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0) 
		{
			$query = sprintf ('DELETE FROM cms_comments WHERE cmnt_id=%d;', 
				$_POST['cmnt_id']
			);
			$res = $sql->command($query)>0?'t':'f';
			return json_encode($res);
		} 
		return json_encode(array('error'=>$checkResult));
	}  
  
	public function render()
	{
		global $cfg,$sql,$page;
		$html = '<style>.cmntitem{padding-bottom: 10px;}</style>';
		$editMode = $this->hasRight() && core::$inEdit;
		$pageLinkUri = '/'.($editMode?'_/':'').$page->pageMainUri;
		
		if ((count($this->unitParam)==0) || (count($this->unitParam)==1?preg_match('/^\d{1,3}$/',$this->unitParam[0])==1:false))
		{
			$uform['errmsg'] = ''; $uform['msg'] = '';
			if (isset($_POST['submitcmnt'])) {
				$uform['name']  = strip_tags(isset($_POST['name'])?$_POST['name']:'');
				$uform['email'] = strip_tags(isset($_POST['email'])?$_POST['email']:'');
				$uform['text']  = strip_tags(isset($_POST['text'])?$_POST['text']:'');
				$capcha = isset($_POST['capcha'])?$_POST['capcha']:'';	 
				$orgcodeOrig = isset($_SESSION['securityCode'])?$_SESSION['securityCode']:'_';
				$checkRule = array();
				$checkRule[] = array('name' , '.');
				$checkRule[] = array('email' , '.');
				$checkRule[] = array('text' , '.');
				$checkResult = checkForm($_POST,$checkRule,$capcha==$orgcodeOrig);
				if (count($checkResult)>0) {
					if ($checkResult[0]['f'] === '!') $uform['errmsg'] = 'Неверный проверочный код!';
					else $uform['errmsg'] = 'Заполнены не все поля!';
				} else {
					$query = sprintf ('INSERT INTO cms_comments(cmnt_name,cmnt_email,cmnt_message,cmnt_sec_id) VALUES (%s,%s,%s,%d) RETURNING cmnt_id;', 
						$sql->t($_POST['name']),
						$sql->t($_POST['email']),
						$sql->t($_POST['text']),
						$page->page['section_id']
						);
					$res = $sql->query_first_row($query);
					if ($res!==false && $res!==null) {
						$title=$_SERVER['HTTP_HOST'].' Новый коментарий';
						$uform['ip'] = $_SERVER['REMOTE_ADDR']; 
						$uform['url'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
						$uform['urlhref'] = 'http://'.$_SERVER['SERVER_NAME'].'/_/'.$page->pageMainUri.'#cmnt'.$res[0];
						$htmlform = shp::tmpl('cmnt_mail_send', $uform);
						sendMailHTML($cfg['email_moderator'], $title, $htmlform,'',$cfg['email_from']);
						
						$uform['name'] = ''; $uform['email'] = ''; $uform['text'] = ''; 
						$uform['msg'] = 'Ваше сообщение отправлено и будет показано как только пройдет проверку.';
					} else $uform['errmsg'] = 'Не удалось отправить сообщение. Попробуйте снова позднее.';
				}
			} else {
				$uform['name'] = ''; $uform['email'] = ''; $uform['text'] = '';
			}
			$htmlform = shp::tmpl('cmnt_send', $uform);

			$pgNum = 1;	if (count($this->unitParam)==1) $pgNum = $this->unitParam[0]>0?$this->unitParam[0]:1;
			$pgSize = 10; 
			
			$qpart = 'from cms_comments where cmnt_sec_id='.$page->page['section_id'].($editMode?'':' and cmnt_enabled');
			$query = 'select count(*) as totalrecords '.$qpart;
			$totalset = $sql->query_first($query); $countRecords = $totalset['totalrecords'];
			$query = sprintf ('select * '.$qpart.' order by cmnt_date desc');
			$query = sprintf ($query.' LIMIT %d OFFSET (%d-1)*%d;',
				$pgSize,
				$pgNum,
				$pgSize);
			$dataset = $sql->query_all($query);
			$pgNums = ceil($countRecords/$pgSize);
			if ($pgNums==0) $pgNums = 1;
			if ($pgNum<1 || $pgNum>$pgNums)
				throw new CmsException('page_not_found');
			
			$html .= '<div class="cmtsupline"></div><div id="cmnts">';
			$u_items = array();
			if ($dataset!==false) foreach ($dataset as $dataItem)
			{
				$u_items[$dataItem['cmnt_id']] = $dataItem;
				$html .= '<div class="cmntitem'.($dataItem['cmnt_enabled'] === 'f'?' imtdsbl':'').'" id="cmi'.$dataItem['cmnt_id'].'"><a name="cmnt'.$dataItem['cmnt_id'].'"></a><div class="cmntiname">'.$dataItem['cmnt_name'].'</div><div class="cmntidate">'.DtTmToDtStr($dataItem['cmnt_date']).'</div><div class="cmntimsg">'.str_replace("\n",'<br/>',$dataItem['cmnt_message']).'</div></div>';
			}
			$html .= '</div>';
			if ($dataset!==false) $html .= '<div class="cmtsdwnline"></div>';
			if ($pgNums>1)
			$html .= '<div class="pager">'.makePager($countRecords, $pgSize, $pgNum, $pageLinkUri.'{pg}').'</div>';

			$html .= $htmlform;
			
			if ($editMode) $html .= '<script type="text/javascript" src="/akcms/js/v1/pg_cmnt_ed.js"></script><script type="text/javascript">var cmntsi='.json_encode($u_items).';</script>';
		} else throw new CmsException('page_not_found');
		return $html;
	}
  
}
