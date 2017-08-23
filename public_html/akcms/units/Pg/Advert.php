<?php # Галлереи раздела

class Pg_Advert extends PgUnitAbstract {
	
	function initAjx()
	{
		global $page;
		return array(
		'_updad' => array(
			'func' => 'ajxParamsUpd',
			'object' => $this)
		);
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

	static public function paramsGet()
	{
		global $page;
		return $page->paramsGet('Pg_Advert',array(
			'head'=>'Внимание',
			'title'=>'Подзаголовок',
			'message'=>'Текст объявления',
			'image'=>'0.jpg',
			'show'=>'t'
		));
	}
	
	function ajxParamsUpd()
	{
		global $sql,$page;
		$checkRule = array();
		$checkRule[] = array('head'  , '.');
		$checkRule[] = array('title'  , '');
		$checkRule[] = array('message', '.');
		$checkRule[] = array('show', '/(t|f)/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$photos = Obj_Gallery::getCopList(__CLASS__,1);
			$page->paramsSet('Pg_Advert',array(
				'head'=>$_POST['head'],
				'title'=>$_POST['title'],
				'message'=>$_POST['message'],
				'image'=>count($photos)>0?$photos[0]['cop_file']:'0.jpg',
				'show'=>$_POST['show']
			));
			return json_encode('t');
		}
		return json_encode(array('error'=>$checkResult));   
	}

	function render()
	{
		global $page;
		$res = '<div id="adedit"></div>';
		$editMode = $this->hasRight() && core::$inEdit;
		$pageLinkUri = ($editMode?'_/':'').$page->pageMainUri;
		if ($editMode) $res .= '<script type="text/javascript">var addata='.json_encode(self::paramsGet()).';</script><script type="text/javascript" src="/akcms/js/v1/pg_ad_ed.js"></script>';
		return $res;
	}
  
}
