<?php # Написать письмо на сайт

class Pg_SendLetter extends PgUnitAbstract {

	function initAjx()
	{
		global $page;
		return array();
	}
  
	function _rigthList()
	{
		return array(
		);
	}

	function initAcl()
	{
		return array(
		'admin'=>true,
		'owner'=>true,
		'default'=>null
		);
	}
  
	function render()
	{
		global $cfg,$page;
		$html = '';
		$editMode = $this->hasRight() && core::$inEdit;
		$pageLinkUri = '/'.($editMode?'_/':'').$page->pageMainUri;
		
		if ((count($this->unitParam)==0) || (count($this->unitParam)==1?preg_match('/^\d{1,3}$/',$this->unitParam[0])==1:false))
		{
			$uform['errmsg'] = ''; $uform['msg'] = '';
			if (isset($_POST['submiteml'])) {
				$uform['name'] = isset($_POST['name'])?htmlentities($_POST['name'],ENT_QUOTES,'UTF-8'):'';
				$uform['email'] = isset($_POST['email'])?htmlentities($_POST['email'],ENT_QUOTES,'UTF-8'):'';
				$uform['text'] = isset($_POST['text'])?htmlentities($_POST['text'],ENT_QUOTES,'UTF-8'):'';
				$capcha = isset($_POST['capcha'])?$_POST['capcha']:'';	 
				$orgcodeOrig = isset($_SESSION['securityCode'])?$_SESSION['securityCode']:'_';
				$checkRule = array();
				$checkRule[] = array('name' , '.');
				$checkRule[] = array('email' , '/^[a-zA-Z0-9\._+-]+@[a-zA-Z0-9\._-]+\.[a-zA-Z]{2,4}$/');
				$checkRule[] = array('text' , '.');
				$checkResult = checkFormAssoc($_POST,$checkRule,$capcha==$orgcodeOrig);
				if (count($checkResult)>0) {
					if (isset($checkResult['!'])) $uform['errmsg'] = 'Неверный проверочный код!';
					elseif (isset($checkResult['email'])) $uform['errmsg'] = 'Неверный адрес e-mail!';
					else $uform['errmsg'] = 'Заполнены не все поля!';
				} else {
					$title=$_SERVER['HTTP_HOST'].' Письмо с сайта';
					$uform['ip'] = $_SERVER['REMOTE_ADDR']; 
					$uform['url'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$htmlform = GetShape('lttr_mail_send', $uform);
					$replyTo = 'Reply-To:'.$uform['email'];
					if (sendMailHTML($cfg['email_moderator'], $title, $htmlform,$replyTo,$cfg['email_from'])) {
						$uform['name'] = ''; $uform['email'] = ''; $uform['text'] = ''; 
						$uform['msg'] = 'Ваше сообщение отправлено.';
					} else $uform['errmsg'] = 'Не удалось отправить сообщение. Попробуйте снова позднее.';
				}
			} else {
				$uform['name'] = ''; $uform['email'] = ''; $uform['text'] = '';
			}
			$html .= GetShape('lttr_send', $uform);

		} else throw new CmsException("page_not_found");
		return $html;
	}
  
}
?>
