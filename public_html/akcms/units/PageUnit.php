<?php
class PageUnit extends CmsPage {
	public $pageUri;
	public $pageMainUri;
	public $imgpath;
	private $pagePath = array();		// Крошки
	private $pagePath_ids = array();	// Крошки_ids
	private $pageMenu = array();		// Меню страницы
	private $pageAllMenu = array();		// Полное меню
	private $pageSections = array();	// массив разделов для ajax
	public $pageUnits = array();
	public static $imgthmbpath = 'img/pages/';
    public $params = array();
	private $imgthmb_w = 500;
	private $imgthmb_h = 500;
	private $imgthmb_m = 0;

	public function initAjx()
	{
	    $ajaxesUnits = [];
		$ajaxes = array(
            '_cntsve' => array(
                'func' => 'ajxCntsve'),
            '_optsve' => array(
                'func' => 'ajxOptSve'),
            '_secsve' => array(
                'func' => 'ajxSecsve'),
            '_secins' => array(
                'func' => 'ajxSecins'),
            '_secdrp' => array(
                'func' => 'ajxSecdrp'),
            '_secup' => array(
                'func' => 'ajxSecUp'),
            '_sectop' => array(
                'func' => 'ajxSecTop'),
            '_secdwn' => array(
                'func' => 'ajxSecDown'),
            '_secbttm' => array(
                'func' => 'ajxSecBottom'),
            '_seciupl' => array(
                'func' => 'ajxSecIUpload'),
            '_seciuplurl' => array(
                'func' => 'ajxSecIUploadUrl'),
            '_secidrp' => array(
                'func' => 'ajxSecIDrp'),
            '_imggrb' => array(
                'func' => 'ajxImagesGrab'),
            '_filebrws' => array(
                'func' => 'ajxFileList'),
            '_fileupl' => array(
                'func' => 'ajxFileUpload'),
            '_filermv' => array(
                'func' => 'ajxFileRemove'),
            '_smdmstk' => array(
                'func' => 'ajxSendMistake'),
            '_sse' => array(
                'func' => 'ajxSSELoad'),
            '_sse_save' => array(
                'func' => 'ajxSSESave'),
		);
		foreach ($this->pageUnits as $pageUnit)
		{
		    foreach ($pageUnit->initAjx() as $k=>$v)
                $ajaxes[$k] = $v;
		}
		return $ajaxes;
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
  
	public function __construct(&$pageTemplate)
	{
		global $sql,$cfg;
        $loadAnyway = core::$isAjax || core::$inEdit || $this->hasRight();
		define('MENU_FIELDS','select section_id,sec_parent_id,sec_url_full,sec_url,sec_nameshort,sec_namefull,sec_imgfile,sec_showinmenu,sec_openfirst,sec_to_news,sec_enabled,sec_title,sec_keywords,sec_description,sec_units,sec_from,sec_howchild,sec_page,sec_page_child, not sec_enabled or not sec_showinmenu as sec_hidden ');

		$pathstr_str = $GLOBALS['pathstr'];

		$query = sprintf ('select * from cms_sections where %s ilike sec_url_full || %s '.($loadAnyway?'':'and sec_enabled and now()>sec_from').' order by length(sec_url_full) desc limit 1;', 
			$sql->t($pathstr_str),
			"'%'");
		$this->page = $sql->query_first_assoc($query);
		#echo $query;
		if ($this->page===false)
		throw new CmsException('page_not_found');

		if ($this->page['sec_openfirst'] === 't')
		{
			$query = sprintf ('select * from cms_sections where sec_parent_id=%d '.($loadAnyway?'':'and sec_enabled and now()>sec_from').' order by sec_sort limit 1;', 
			$this->page['section_id']);
			$pagenew = $sql->query_first_assoc($query);
			
			if ($pagenew===false)$this->page['sec_content']='Нет дочерних страниц!';
				else $this->page = $pagenew;
		}
		
		$params_str = trim(mb_substr($pathstr_str,mb_strlen($this->page['sec_url_full'])),'/');
		$params_arr = $params_str === ''?array():explode('/',$params_str);
		 
		$pageTemplate = (core::$inEdit?'editpage':$this->page['sec_page']);
		$this->title = $this->page['sec_title'] !== '' ? $this->page['sec_title'] : $this->page['sec_namefull'].' - '.$cfg['site_title'];
		#if ($this->hasRight()) var_dump_($this->page['sec_title']);

		$this->pageUri = $pathstr_str === '/'?'':$pathstr_str;
		$this->pageMainUri = $this->page['sec_url_full'] === '/'?'':$this->page['sec_url_full'];
		$this->imgpath = '/'.self::$imgthmbpath.($this->pageUri === '/'?'/_/':'/'.$GLOBALS['pathstr']);
		//$this->getMenu();
		$this->getBreadcrumbs();
		#var_dump($this->getMenu()); exit;
		
		$unitsCount = 0;
		if (trim($this->page['sec_units']) !== '') foreach (explode(',',$this->page['sec_units']) as $pgUnitClass)
			if (isset($cfg['pgunits'][$pgUnitClass]))
			{
				$unitsCount++;
				$this->pageUnits[$pgUnitClass] = new $pgUnitClass($unitsCount==1?$params_arr:array());
			} else trigger_error('Wrong unit "'.$pgUnitClass.'" on page '.$this->pageUri.'. All list:'.$this->page['sec_units'],E_USER_WARNING);

		if ($unitsCount==0 && count($params_arr)>0)
			throw new CmsException('page_not_found');

        #Получение параметров
        try { 
            if ($this->page['sec_params']!=='') $this->params = unserialize($this->page['sec_params']);
        } catch (Exception $e) {
            $this->params = '';
            $this->paramsSave();
            throw new CmsException('wrong_page_params');
        }

	}

    #Сохраняет все параметры в бд
    public function paramsSave() {
        global $sql;
        $query = sprintf ('update cms_sections set sec_params=%s where section_id=%d;',
            $sql->t(serialize($this->params)),
            $this->page['section_id']);
			$res_count = $sql->command($query);
			return $res_count>0;
    }

    #Получить параметры
    public function paramsGet($name, array $default = array()) {
        return (isset($this->params[$name])?$this->params[$name]:$default);
    }

    #Сохранить параметры
    public function paramsSet($name, $value) {
        if ($value === null || count($value)==0) unset($this->params[$name]);
        else $this->params[$name] = $value;
        $this->paramsSave();
    }

	#Хлебные крошки  
	public function getBreadcrumbs($prefix=false)
	{
		global $sql;
		if (count($this->pagePath) != 0) return $this->pagePath;

		$currNode = array(
			'section_id'    =>$this->page['section_id'],
			'sec_parent_id' =>$this->page['sec_parent_id'],
			'sec_url_full'  =>$this->page['sec_url_full'],
			'sec_nameshort' =>$this->page['sec_nameshort'],
			'sec_namefull' =>$this->page['sec_namefull'],
			'sec_showinmenu'=>$this->page['sec_showinmenu'],
			'sec_enabled'   =>$this->page['sec_enabled'],
			'sec_hidden'	=>$this->page['sec_enabled'] === 'f' || $this->page['sec_showinmenu'] === 'f'?'t':'f',
			'_current' => true
		);

		$this->pagePath[] = $currNode;
		$this->pagePath_ids[$currNode['section_id']] = $currNode;
		while ($currNode['sec_parent_id']!=0) 
		{
			$query = sprintf ('select section_id,sec_parent_id,sec_url_full,sec_nameshort,sec_namefull,sec_showinmenu,sec_enabled, not sec_enabled or not sec_showinmenu as sec_hidden from cms_sections where section_id=%d limit 1;', 
				$currNode['sec_parent_id']);
			$dataset = $sql->query_all($query);
			if (count($currNode)==0) {echo '.'; break;}
			$currNode = $dataset[0];
			$this->pagePath[] = $currNode;
			$this->pagePath_ids[$currNode['section_id']] = $currNode;
		}
		$this->pagePath = array_reverse($this->pagePath);
		return $this->pagePath;
	}


    /**
     * @param $howchild
     * @return bool|string
     */
    private function _howchildToOrder($howchild){
		switch ($howchild) {
			case 1:	return 'sec_sort';
			case 2:	return 'sec_from DESC';
			case 3:	return 'sec_from';
		}
		return false;
	}
    private function _getMenuItemByUrl($urlFull,$showHidden = false,$prefix=false)
    {
        global $sql;
        $query = sprintf (MENU_FIELDS.' from cms_sections where sec_url_full=%s '.($showHidden?'':'and sec_enabled and sec_showinmenu and now()>sec_from').';',
            $sql->t($urlFull));
        $dataset = $sql->query_first_assoc($query);
        if ($prefix!==false && $dataset!==false) $dataset['sec_url_full'] = $prefix.$dataset['sec_url_full'];
        return $dataset;
    }
    private function _getMenuItem($Id,$showHidden = false,$prefix=false)
    {
        global $sql;
        $query = sprintf (MENU_FIELDS.' from cms_sections where section_id=%d '.($showHidden?'':'and sec_enabled and sec_showinmenu and now()>sec_from').';',
            $Id);
        $dataset = $sql->query_first_assoc($query);
        if ($prefix!==false && $dataset!==false) $dataset['sec_url_full'] = $prefix.$dataset['sec_url_full'];
        return $dataset;
    }
    private function _getMenuItems($parentId,$howchild,$showHidden = false,$prefix=false)
    {
        global $sql;
		$order = $this->_howchildToOrder($howchild); 
		if (core::$inEdit && $order==false) {
            $order = 'sec_enabled,sec_showinmenu,sec_from DESC';
			$wherespec = ($showHidden?' ':'and sec_enabled and sec_showinmenu and now()>sec_from').' and not sec_system';
			//'and (not sec_enabled or not sec_showinmenu or now()<=sec_from)'
		}
		else {
			if ($order==false) return false;
			$wherespec = ($showHidden?'':'and sec_enabled and sec_showinmenu and now()>sec_from').' and not sec_system';
		}
		$fields = MENU_FIELDS;
        if ($parentId<0) {
			$query = sprintf ($fields.' from cms_sections inner join cms_menu_items ON (mnui_sec_id=section_id) where mnui_mnu_id=%d '.$wherespec.' order by mnui_sort,'.$order,
				-$parentId);
		} else 
		$query = sprintf ($fields.' from cms_sections where sec_parent_id=%d '.$wherespec.' order by '.$order,
            $parentId);
        $dataset = $sql->query_all($query);
        if ($prefix!==false && $dataset!==false) foreach ($dataset as $data) $data['sec_url_full'] = $prefix.$data['sec_url_full'];
        return $dataset;
    }

    private function _getAllMenuItems(&$putInto,$parentId, $howchild=1,
			$showHidden = false, $prefix = false, $markSelected = false, $markCurrent = false, $expByPath = false, $deep = 999) {
		global $sql;
		if ($deep==0) return false;
		$order = $this->_howchildToOrder($howchild); if ($order==false) return false;
		$mnulist = $this->_getMenuItems($parentId,$howchild,$showHidden,$prefix);
		if ($mnulist!==false) {
			$putInto = $mnulist;
			foreach ($putInto as &$menuAllItem) {
				if (isset($this->pagePath_ids[$menuAllItem['section_id']])) {
					if ($markSelected && !isset($this->pagePath_ids[$menuAllItem['section_id']]['_current'])) $menuAllItem['_selected'] = true;
					if ($markCurrent && isset($this->pagePath_ids[$menuAllItem['section_id']]['_current'])) $menuAllItem['_current'] = true;
				}
				$menuAllItem['_children'] = array();
				if (!$showHidden && $menuAllItem['sec_openfirst'] === 't') // Подменяем url openfirst раздела первым подразделом
				{
					$query = sprintf ('select sec_url_full from cms_sections where sec_parent_id=%d and sec_enabled and now()>sec_from order by sec_sort limit 1;', 
						$menuAllItem['section_id']);
					$dataset = $sql->query_first_assoc($query);
					if ($dataset!==false) {
                        $menuAllItem['sec_url_full'] = ($prefix!==false?$prefix:'').$dataset['sec_url_full'];
					}
				}
				if (!$expByPath || ($expByPath && isset($this->pagePath_ids[$menuAllItem['section_id']]))) // Раскрытие по крошкам
					$this->_getAllMenuItems($menuAllItem['_children'],$menuAllItem['section_id'],$menuAllItem['sec_howchild'],
						$showHidden,$prefix,$markSelected, $markCurrent,$expByPath, $deep-1);
				if ($menuAllItem['sec_units'] !== '') foreach (explode(',',$menuAllItem['sec_units']) as $pgUnitClass) if (isset($cfg['pgunits'][$pgUnitClass]))
                    call_user_func_array(array($pgUnitClass,'buildLevelSiteMap'),array(&$menuAllItem['_children'],$menuAllItem['section_id'],$menuAllItem['sec_url_full']));
                if (count($menuAllItem['_children'])==0) unset($menuAllItem['_children']);
			}
		}
		return true;
	}
 
	/* Вся структура меню для карты */
	public function getAllMenu($showHidden = false)
	{
		if (count($this->pageAllMenu) != 0) return $this->pageAllMenu;
		$showHidden = $this->hasRight() && $showHidden;
		$this->_getAllMenuItems($this->pageAllMenu,0,1,$showHidden);
		return $this->pageAllMenu;
	}

	/* структура, начиная от url */
	public function getMenuSubByPath(&$putInto, $menuPath='', $markSelected = false, $markCurrent = false, $expByPath = false, $deep = 999, $howchild = 1) {
		if ($menuPath==='') {
			$parentId = 0;
		} else {
			$parentSec = $this->_getMenuItemByUrl($menuPath,true);
			$parentId = $parentSec['section_id'];
			$howchild = $parentSec['sec_howchild'];
		}
		$this->_getAllMenuItems($putInto,$parentId,$howchild,false,false,$markSelected,$markCurrent,$expByPath,$deep);
	}	
	
	/* структура, начиная от SpecId */
	public function getMenuSubBySpecId(&$putInto, $menuSpecId, $markSelected = false, $markCurrent = false, $expByPath = false, $deep = 999, $howchild = 1) {
		$this->_getAllMenuItems($putInto,$menuSpecId,$howchild,false,false,$markSelected,$markCurrent,$expByPath,$deep);
	}	
	
	/* полная структура, хорошо для админки*/
	public function getMenu()
	{
        if (count($this->pageMenu) != 0) return $this->pageMenu;
		$this->getBreadcrumbs();
		$showHidden = $this->hasRight();
		$howchild = 1;
		$putInto = &$this->pageMenu;
		$lastPathItem = false;
		foreach ($this->pagePath as $pathItem)
		{
			$mnulist = $this->_getMenuItems($pathItem['sec_parent_id'],$howchild,$showHidden);
			if ($mnulist!==false) {
				$putInto = $mnulist;
				foreach ($putInto as &$mnuitem) {
					$mnuitem['_p_hc'] = $howchild;
					if ($mnuitem['section_id']==$pathItem['section_id'])
					{
						if ($mnuitem['section_id']==$this->page['section_id']) $mnuitem['_current'] = true;
						$howchild = $mnuitem['sec_howchild'];
						$mnuitem['_selected'] = true;
						$mnuitem['_children'] = array();
						$putInto = &$mnuitem['_children'];
					}
				}
				unset($mnuitem);
			} else break;
			$lastPathItem = $pathItem;
		}
		if ($lastPathItem !== false)
		{
			$mnulist = $this->_getMenuItems($lastPathItem['section_id'],$howchild,$showHidden);
			if ($mnulist!==false) { 
				$putInto = $mnulist; $this->page['_children'] = &$mnulist; 
				foreach ($putInto as &$mnuitem1) $mnuitem1['_p_hc'] = $howchild;
                unset($mnuitem);
			}
		}
        if (core::$inEdit) {
            $root = $this->_getMenuItem(1);
            $root['sec_url_full'] = '';
            if ($root !== false) array_unshift($this->pageMenu,$root);
        }
		return $this->pageMenu;
	}

	private function _buildPageSections($menuItems) /* Необходимо для режима редактирования - массив разделов для ajax */ 
	{
		foreach($menuItems as $v)
		{
			if (isset($v['_children'])?$v['_children']!=false:false) $this->_buildPageSections($v['_children']);
			if (isset($v['_children'])) unset($v['_children']);
			$this->pageSections[$v['section_id']] = $v;
		}
	}

	public function buildSiteMapXml() { // Карта сайта
		global $cfg,$sql;
		$siteAllMenu = $this->getAllMenu();
		array_unshift($siteAllMenu,array('sec_url_full'=>''));
		$html = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
		
		$query = 'SELECT \'\' as sec_url_full UNION ALL SELECT sec_url_full FROM  cms_sections WHERE sec_enabled AND not sec_system AND sec_from < now();';
		foreach($sql->queryObj($query) as $item) {
			$html .= "  <url>\n";		
			$html .= '    <loc>'.$cfg['site_map_urlpref'].$item['sec_url_full']."</loc>\n";
			$html .= "    <changefreq>weekly</changefreq>\n";
			$html .= "  </url>\n";
		}		
		$html .= '</urlset>';
		file_put_contents($cfg['site_map_file'],$html);
		return $html;
	}

	public static function makeSrchWords($indstr)
	{
		$indstr = str_replace(array(chr(10),chr(13)),array(' ',' '),$indstr);
		$indstr = preg_replace('/&[a-zA-Zа-яА-Я0-9]+;/u', '', strUpCorr($indstr));
		preg_match_all('/[a-zA-Zа-яА-Я0-9]+/u',$indstr,$findArray); $indarr = $findArray[0];

        /* @var $morphy phpMorphyAdapter */
        $morphy = phpMorphyAdapter::getInstance();
		$base_forms = $morphy->getBaseForm($indarr);
		return $base_forms;
	}

	public function reindex($indxpage = false)
	{
	    return 't';//////
		global $sql;
		if (!$this->hasRight()) return false;
		if ($indxpage==false) $indxpage = &$this->page;#
		global $sql;
		$indstr = strip_tags($indxpage['sec_enabled'] === 't'?$indxpage['sec_nameshort'].' '.$indxpage['sec_namefull'].' '.$indxpage['sec_title'].' '.$indxpage['sec_content'].' '.$indxpage['sec_keywords'].' '.$indxpage['sec_description']:'');
		$base_forms = self::makeSrchWords($indstr);

		$query = sprintf ('select __cms_srchwords_sections__assign(%d,%s);', 
			$indxpage['section_id'],
			$sql->pgf_array_text($base_forms)
		);
		$res=$sql->query_fr($query);
		return $res[0]; //'t':'f'
	}

	//Gallery
	public function getRandomGallery()
	{
		global $sql;
		$res = array();
		$query = sprintf ('select id_glr,glr_name,glr_file,glr_type from cms_galeries where glr_enabled and not glr_sys and glr_type=1 order by random() limit 1;'
		//$this->page['section_id']
        );
		$dataset = $sql->query_first($query);
		if ($dataset!==false) {
			$glr = $dataset; //id_glr glr_name
			$query = sprintf ('select a.id_cgp,a.cgp_glr_id,a.cgp_name,a.cgp_file from cms_gallery_photos a inner join cms_galeries b on (a.cgp_glr_id=b.id_glr) where a.cgp_enabled and b.glr_enabled and id_glr=%d;', 
				$glr['id_glr']);
			$res['glr'] = $glr;
			$dataset = $sql->query_all($query);
			if ($dataset!=false) {
				$glrPhotos = array();
				foreach ($dataset as $photoItem)
					$glrPhotos[$photoItem['cgp_glr_id']][] = $photoItem;
				$res['glr_ph'] = $glrPhotos;
			}
		}
		return $res;
	} 

	public function getGalleriesList() // Список всех несистемных галерей
	{
		global $sql; // and not glr_sys
		$query = sprintf ('select id_glr,glr_name,glr_file,glr_type from cms_galeries where glr_enabled and glr_type=1 order by id_glr desc;');
		$dataset = $sql->query_all($query);
		return ($dataset===false?array():$dataset);
	} 

	public function getSecGalleryPhotos() // Фото привязанной галлереи
	{
		global $sql;
		$res = array();
		if ($this->page['sec_glr_id']!==0) {
			$query = sprintf ('select a.id_cgp,a.cgp_glr_id,a.cgp_name,a.cgp_file from cms_gallery_photos a inner join cms_galeries b on (a.cgp_glr_id=b.id_glr) where a.cgp_enabled and b.glr_enabled and a.cgp_glr_id=%d order by a.id_cgp', 
			$this->page['sec_glr_id']);
			$dataset = $sql->query_all($query);
			if ($dataset!=false) 
			foreach ($dataset as $photoItem) $res[] = $photoItem;  
		}
		return $res;
	}    

	public function getSecTags() // Теги привязанные к странице
	{
		global $sql;
		$res = array();
		$query = sprintf ('select a.tag_text from cms_tags a inner join cms_tags_sections b on (a.tag_id=b.tag_id) where b.sec_id=%d order by a.tag_text', 
		$this->page['section_id']);
		$dataset = $sql->query_all($query);
		if ($dataset!=false) foreach ($dataset as $dataItem) $res[] = $dataItem['tag_text']; 
		return $res;
	}

	public function getAllTags($str='') // Все теги
	{
		global $sql;
		$res = array();
		$query = sprintf ('select a.tag_text from cms_tags a where a.tag_text like %s order by a.tag_text',
            $sql->t('%'.$str.'%')
        );
		$dataset = $sql->query_all($query);
		if ($dataset!=false) foreach ($dataset as $dataItem) $res[] = $dataItem['tag_text']; 
		return $res;
	}

 	/*================ ajax =====================*/

	public function ajxSendMistake()
	{
		global $cfg;
		$checkRule = array();
		$checkRule[] = array('comment',	'');
		$checkRule[] = array('href',	'.');
		$checkRule[] = array('selText',	'.');
		$checkRule[] = array('title',	'.');
		$checkResult = checkForm($_POST,$checkRule);
		if (count($checkResult)==0) {
			$uform['comment']	= htmlentities(strip_tags(isset($_POST['comment'])?$_POST['comment']:''),ENT_QUOTES,'UTF-8');
			$uform['href']		= htmlentities(strip_tags(isset($_POST['href'])?$_POST['href']:''),ENT_QUOTES,'UTF-8');
			$uform['selText']	= strip_tags(isset($_POST['selText'])?$_POST['selText']:'','<u>');
			$uform['title']		= htmlentities(strip_tags(isset($_POST['title'])?$_POST['title']:''),ENT_QUOTES,'UTF-8');
			$uform['ip']		= $_SERVER['REMOTE_ADDR'];
			$title=$_SERVER['HTTP_HOST'].' ошика на странице';
			$htmlform = GetShape('sndmstk_mail', $uform);
			$res = sendMailHTML($cfg['email_moderator'], $title, $htmlform,'',$cfg['email_from']);//email_moderator email_error
			return json_encode($res?'t':'f');
		} 
		return json_encode(array('error'=>$checkResult));
	}

	public function ajxSecUp()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('section_id'     , '/^\\d{1,}$/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0) {
			$query = sprintf ('SELECT __cms_sections__up(%d) as res;', 
				$_POST['section_id']);
			$dataset = $sql->query_first_assoc($query);
			return json_encode($dataset['res']);
		} 
		return json_encode(array('error'=>$checkResult));
	}

	public function ajxSecTop()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('section_id'     , '/^\\d{1,}$/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0) {
			$query = sprintf ('SELECT __cms_sections__top(%d) as res;', 
				$_POST['section_id']);
			$dataset = $sql->query_first_assoc($query);
			return json_encode($dataset['res']);
		} 
		return json_encode(array('error'=>$checkResult));
	}

	public function ajxSecDown()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('section_id'     , '/^\\d{1,}$/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0) {
			$query = sprintf ('SELECT __cms_sections__down(%d) as res;', 
				$_POST['section_id']);
			$dataset = $sql->query_first_assoc($query);
			return json_encode($dataset['res']);
		} 
		return json_encode(array('error'=>$checkResult));
	}
	
	public function ajxSecBottom()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('section_id'     , '/^\\d{1,}$/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0) {
			$query = sprintf ('SELECT __cms_sections__bttm(%d) as res;', 
				$_POST['section_id']);
			$dataset = $sql->query_first_assoc($query);
			return json_encode($dataset['res']);
		} 
		return json_encode(array('error'=>$checkResult));
	}
	
	public function dropCachesByBreadcrumbs()
	{
		global $Cacher;
		foreach ($this->getBreadcrumbs() as $item) {
			$Cacher->cache_drop(trim($item['sec_url_full'],'/'));
		}
		$Cacher->cache_drop(''); //Главная
	}
	
	public function ajxCntsve()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('sec_content'   , '');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$_POST['sec_content'] = preg_replace('/(<img.*? src=")(\.\.\/){1,}(.*?)"/ui','\1/\3',$_POST['sec_content']); // Заменяем любое количество начальных ../../../ на /
			$_POST['sec_content'] = preg_replace('/(<img.*? src=")(?!\/|http:|https:)(.*?)"/ui','\1/\2',$_POST['sec_content']); // Если это наш сервер и нет / в начале - добавляем
			$query = sprintf ('update cms_sections set sec_content=%s where section_id=%d;', 
				$sql->t($_POST['sec_content']),
				$this->page['section_id']);
			$res_count = $sql->command($query);
			if ($res_count>0) {
				$this->page['sec_content'] = $_POST['sec_content'];
				$this->reindex();
				$this->dropCachesByBreadcrumbs();
			}
			return json_encode($res_count>0?'t':'f');
		} 
		return json_encode(array('error'=>$checkResult));
	}
  
	public function ajxOptSve()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('sec_contshort', '');
		$checkRule[] = array('sec_tags', '');
		//$checkRule[] = array('sec_units', '');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			/*
			$_POST['sec_contshort'] = preg_replace('/(<img.*? src="|<a.*? href=")(\.\.\/){1,}(.*?)"/ui','\1/\3',$_POST['sec_contshort']);
			$_POST['sec_contshort'] = preg_replace('/(<img.*? src="|<a.*? href=")(?!\/|http:|https:)(.*?)"/ui','\1/\2',$_POST['sec_contshort']);
			*/
			$_POST['sec_contshort'] = preg_replace('/(<img.*? src=")(\.\.\/){1,}(.*?)"/ui','\1/\3',$_POST['sec_contshort']); // Заменяем любое количество начальных ../../../ на /
			$_POST['sec_contshort'] = preg_replace('/(<img.*? src=")(?!\/|http:|https:)(.*?)"/ui','\1/\2',$_POST['sec_contshort']); // Если это наш сервер и нет / в начале - добавляем
            if (!isset($_POST['sec_units'])) $_POST['sec_units'] = array();
            $sec_units = array();
            foreach ($_POST['sec_units'] as $unit) if (trim($unit) !== '') $sec_units[] = $unit;
			$query = sprintf ('update cms_sections set sec_contshort=%s,sec_units=%s where section_id=%d;', #, sec_glr_id=%d
				$sql->t($_POST['sec_contshort']),
                $sql->t(implode(',',$sec_units)),
				$this->page['section_id']);
			$res_count = $sql->command($query);
			if ($res_count>0) {
				$this->page['sec_contshort'] = $_POST['sec_contshort'];
				$this->reindex();
			}
			
			$tags = trim($_POST['sec_tags']) === ''?array():explode(',',$_POST['sec_tags']);
			$query = sprintf ('select __cms_tags_sections__assign(%d,%s);', 
				$this->page['section_id'],
				$sql->pgf_array_text($tags)
			);			
			$db_res = $sql->query_first_row($query);
			
			return json_encode(($res_count>0 && $db_res[0] === 't')?'t':'f');
		} 
		return json_encode(array('error'=>$checkResult));
	}
  
	public static function _addClass($str, $needclass) {
		$clases = explode(' ',$str);
		if (!in_array($needclass, $clases, true)) $clases[] = $needclass;
		return implode(' ',$clases);
	}
	
	// Добавляет указанный класс к текстовому представления hmtl элемента <a>
	public static function addClass($el, $needclass) {
		$el=stripcslashes($el);
		#toLogDie__($el);
		if (mb_stripos($el,'class=')===false) return str_replace(array('/>','>'),array(' class="'.$needclass.'" />',' class="'.$needclass.'" >'),$el);
		else {
			$el = preg_replace('/(?<= class=")[^"]*(?=")/uie','self::_addClass(\'\\0\',$needclass)',$el);
			$el = preg_replace('/(?<= class=\')[^\']*(?=\')/uie','self::_addClass(\'\\0\',$needclass)',$el);
		}
		return $el;
	}  
  
	public static function _imgduplicate($pathold, $pathto, &$imggrabbed) { // Загружает изображения с других северов
		$dirpath = mb_substr($pathto, 0, -1);
		if(!file_exists($dirpath)) mkdir($dirpath,0755,true);  
		$result = '';

		$path_prts = pathinfo(mb_strtolower($pathold));
		$path_prts['filename'] = basename(Title2Uri($path_prts['filename']),'.'.$path_prts['extension']);
		
		if ($path_prts['extension'] === 'jpeg') $path_prts['extension'] = 'jpg';
		if ($path_prts['extension'] === 'png') $path_prts['extension'] = 'jpg';
		$pathnew = $pathto.$path_prts['filename'].'.'.$path_prts['extension'];
		// Уникальное имя
		for ($i=1;$i<=11;$i++) if (file_exists($pathnew)) {
			$pathnew = $pathto.$path_prts['filename'].'_'.$i.'.'.$path_prts['extension'];
		} else break;
		$tmpfile = tempnam('/tmp','akimg'); copy($pathold,$tmpfile);
		try {
			$imgRszr = new ImgResizer();
			if ($imgRszr->ResizeSave($tmpfile,$pathnew,1200,1200,0)) {
				$imggrabbed[] = basename($pathnew);
				$result = '/'.$pathnew;
			}			
		} catch(Exception $e) {$res_msg = $e->getMessage();}
		@unlink($tmpfile);
		return $result;
	}

	public static function ImagesGrab($html, $urlfull)
	{
		$html = preg_replace('/(<img.*? src=")(\.\.\/){1,}([^\s]*?)"/ui','\1/\3',$html); // Заменяем любое количество начальных ../../../ на /
		$html = preg_replace('/(<img.*? src=")(?!\/|http:|https:)([^\s]*?)"/ui','\1/\2',$html); // Если это наш сервер и нет / в начале - добавляем /
		$imggrabbed = array();
		$pathto = $GLOBALS['cfg']['imagespath'].mb_strtolower($urlfull);
		$html = preg_replace('/(?<= src="| href=")(http:|https:)([^\s]*?.)(jpg|jpeg|gif|png)(?=")/uie','self::_imgduplicate(\'\\0\',$pathto,$imggrabbed)',$html);
		
		//Добавляем класс _imgview
		$html = preg_replace('/<a[^>]+href=(["\']{1})(http[s]{0,1}:|\/)[^\s]*?\.(jpg|jpeg|gif|png)\1[^>]*>/uie','self::addClass(\'\\0\',\'_imgview\')',$html);		
		return array('html'=>$html,'imggrbd'=>$imggrabbed);
	}	
	
	public function ajxImagesGrab()
	{
		$checkRule = array();
		$checkRule[] = array('html', '');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$res = self::ImagesGrab($_POST['html'],$this->pageUri);
			$res['imggrbd'] = implode(', ',$res['imggrbd']);
			return json_encode($res);
		} 
		return json_encode(array('error'=>$checkResult));
	}
  
	public function ajxSecins()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('sec_url'   	  , '/^[a-z0-9_-]+$/');
		$checkRule[] = array('sec_enabled'    , '/^(t|f)$/');
		$checkRule[] = array('sec_namefull'   , '.');
		$checkRule[] = array('sec_nameshort'  , '.');
		$checkRule[] = array('sec_title'      , '');
		$checkRule[] = array('sec_description', '');
		$checkRule[] = array('sec_keywords'   , '');
		$checkRule[] = array('sec_openfirst'  , '/^(t|f)$/');
		$checkRule[] = array('sec_to_news'    , '/^(t|f)$/');
		$checkRule[] = array('sec_showinmenu' , '/^(t|f)$/');
		$checkRule[] = array('section_id'     , '/^\\d{1,}$/');
		$checkRule[] = array('sec_parent_id'  , '/^\\d{1,}$/');
		$checkRule[] = array('sec_from'  , '/^\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}$/');
		$checkRule[] = array('sec_page'  , '/^[a-zA-Z0-9\-\_]+$/');
		$checkRule[] = array('sec_howchild'  , '/^(0|1|2|3)$/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0) {
			$res = 0;
			$query = sprintf ('INSERT INTO cms_sections(sec_url,sec_description,sec_enabled,sec_title,sec_keywords,sec_namefull,sec_nameshort,sec_openfirst,sec_to_news,sec_showinmenu,sec_from,sec_page,sec_howchild,sec_parent_id) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%d,%d) RETURNING section_id,sec_url_full;', 
				$sql->t($_POST['sec_url']),
				$sql->t($_POST['sec_description']),
				$sql->t($_POST['sec_enabled']),
				$sql->t($_POST['sec_title']),
				$sql->t($_POST['sec_keywords']),
				$sql->t($_POST['sec_namefull']),
				$sql->t($_POST['sec_nameshort']),
				$sql->t($_POST['sec_openfirst']),
				$sql->t($_POST['sec_to_news']),
				$sql->t($_POST['sec_showinmenu']),
				$sql->t($_POST['sec_from']),
				$sql->t($_POST['sec_page']),
				$_POST['sec_howchild'],
				$_POST['sec_parent_id']
			);
			try {
				$res = @$sql->query_fa($query);
			} catch (DBException $e) {
				if ($e->isDuplicate)
					$checkResult[] = array('f'=>'sec_url','s'=>'Это url уже занято');
			}
			if (count($checkResult)==0) {
				return json_encode(array('r'=>$res!==false?'t':'f','url'=>$res['sec_url_full']));
			}
		} 
		return json_encode(array('error'=>$checkResult));
	}
    
	public function ajxSecsve()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('sec_url'		  , '/^(\/|[a-z0-9_-]+)$/');
		$checkRule[] = array('sec_enabled'    , '/^(t|f)$/');
		$checkRule[] = array('sec_namefull'   , '.');
		$checkRule[] = array('sec_nameshort'  , '.');
		$checkRule[] = array('sec_title'      , '');
		$checkRule[] = array('sec_description', '');
		$checkRule[] = array('sec_keywords'   , '');
		$checkRule[] = array('sec_openfirst'  , '/^(t|f)$/');
		$checkRule[] = array('sec_to_news'    , '/^(t|f)$/');
		$checkRule[] = array('sec_showinmenu' , '/^(t|f)$/');
		$checkRule[] = array('section_id'     , '/^\\d{1,}$/');
		$checkRule[] = array('sec_from'  , '/^\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}$/');
		$checkRule[] = array('sec_howchild'  , '/^(0|1|2|3)$/');
		$checkRule[] = array('sec_page'  , '/^[a-zA-Z0-9\-\_]+$/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0) {
            if ($_POST['section_id'] == 1) {
                $_POST['sec_url'] = '/';
                $_POST['sec_url_'] = '';
            } else $_POST['sec_url_'] = $_POST['sec_url'];
			$query = sprintf ('update cms_sections set sec_url=%s, sec_url_full=__cms_sections__full_path(sec_parent_id,%s)||\'/\', sec_description=%s, sec_enabled=%s, sec_title=%s, sec_keywords=%s, sec_namefull=%s, sec_nameshort=%s, sec_openfirst=%s, sec_to_news=%s, sec_showinmenu=%s, sec_from=%s, sec_page=%s, sec_howchild=%d where section_id=%d;', 
				$sql->t(mb_strtolower($_POST['sec_url'])),
				$sql->t(mb_strtolower($_POST['sec_url_'])),
				$sql->t($_POST['sec_description']),
				$sql->t($_POST['sec_enabled']),
				$sql->t($_POST['sec_title']),
				$sql->t($_POST['sec_keywords']),
				$sql->t($_POST['sec_namefull']),
				$sql->t($_POST['sec_nameshort']),
				$sql->t($_POST['sec_openfirst']),
				$sql->t($_POST['sec_to_news']),
				$sql->t($_POST['sec_showinmenu']),
				$sql->t($_POST['sec_from']),
				$sql->t($_POST['sec_page']),
				$_POST['sec_howchild'],
				$_POST['section_id']
			);
			try {
				@$res_count = $sql->command($query);
			} catch (DBException $e) {
				if ($e->isDuplicate)
					$checkResult[] = array('f'=>'sec_url','s'=>'Это url уже занято');
				$res_count = false;
			}
			if (count($checkResult)==0) {
				$new_url='';
				$query = sprintf ('select * from cms_sections where section_id = %d;', 
					$_POST['section_id']);
				$indxpage =  $sql->query_first_assoc($query);
				$new_url=$indxpage['sec_url_full'];
				$this->reindex($indxpage);
				$this->buildSiteMapXml();
				return json_encode(array('r'=>$res_count>0?'t':'f','url'=>$new_url));
			}
			
		}
		return json_encode(array('error'=>$checkResult));
	}

	public function ajxSecIUpload() # Загрузка изображения раздела
	{
		global $sql,$page;
		$res_msg = ''; $res_stat = 0; $res_i_file = '';
		$JsHttpRequest = new JsHttpRequest('UTF-8');
		$checkRule = array();
		$checkRule[] = array('section_id', '/^\d+$/');
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
						$path_parts = pathinfo(mb_strtolower($url));
						$file_ext = $path_parts['extension'];
						if (in_array($file_ext, array('jpg','jpeg','png'), true))
						{
							$res_stat = 1;
							$imginfo = false;

                            $dst = null;
							try {
								$imgRszr = new ImgResizer();
								$dst = $imgRszr->simpleResize($upl['tmp_name'],$this->imgthmb_w,$this->imgthmb_h,$this->imgthmb_m);
								$imginfo = $imgRszr->imginfo;						
							} catch(Exception $e) {}									

							if ($imginfo!==false)
							{
								$res_stat = 2;

								if ($_POST['section_id']>0)
								{
									#$res_stat = 3;
									$res_stat = 4;
									$i_file = $_POST['section_id'].'.jpg';
									$pathstr = self::$imgthmbpath.$i_file;
									$dirpath = dirname($pathstr);
									if (!file_exists($dirpath)) mkdir($dirpath,0755,true);       
									@unlink(self::$imgthmbpath.$i_file);
									@array_map('unlink',glob(self::$imgthmbpath.'*/'.$i_file));
									imagejpeg($dst,$pathstr,90);

									$query = sprintf ('UPDATE cms_sections SET sec_imgfile = %s WHERE section_id = %d;', 
										$sql->t($i_file),
										$_POST['section_id']);
									$res_count = $sql->command($query);

									$res_i_file = $i_file;
									
								} else $res_msg = 'Неверный код раздела!';

								if ($dst != null) imagedestroy($dst);
										
							} else $res_msg = 'Неверный формат файла!';
						} else $res_msg = 'Неверный формат! Поддерживается: .jpg';
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
	
	public static function SecIUploadUrl($section_id, $url, $imgthmb_w, $imgthmb_h, $imgthmb_m) # Загрузка изображения раздела по URL
	{
		global $sql,$page;
		$res_msg = ''; $res_stat = false; $res_i_file = '';

		$path_parts = pathinfo(mb_strtolower($url));
		$file_ext = $path_parts['extension'];
		if (in_array($file_ext, array('jpg','jpeg','png'), true))
		{
			$imginfo = false;
			$tmpfile = tempnam('/tmp','akimg');
			copy($url,$tmpfile);
			
			try {
				$imgRszr = new ImgResizer();
				$dst = $imgRszr->simpleResize($tmpfile,$imgthmb_w,$imgthmb_h,$imgthmb_m);
				$imginfo = $imgRszr->imginfo;						
			} catch(Exception $e) {$res_msg = $e->getMessage();}
			@unlink($tmpfile);

			if ($imginfo!==false)
			{
				if ($section_id>0)
				{
					$res_stat = true;
					$i_file = $section_id.'.jpg';
					$pathstr = self::$imgthmbpath.$i_file;
					$dirpath = dirname($pathstr);
					if (!file_exists($dirpath)) mkdir($dirpath,0755,true);
					@unlink(self::$imgthmbpath.$i_file);
					@array_map('unlink',glob(self::$imgthmbpath.'*/'.$i_file));
					imagejpeg($dst,$pathstr,90);

					$query = sprintf ('UPDATE cms_sections SET sec_imgfile = %s WHERE section_id = %d;', 
						$sql->t($i_file),
						$section_id);
					$res_count = $sql->command($query);

					$res_i_file = $i_file;
					
				} else $res_msg = 'Неверный код раздела!';

				imagedestroy($dst);               
						
			} else $res_msg = 'Неверный формат файла!';
		} else $res_msg = 'Неверный формат! Поддерживается: .jpg';
		return array(
			'res'=> $res_stat,
			'msg'   => $res_msg,
			'i_file'=> $res_i_file
		);
	}

	public function ajxSecIUploadUrl() # Загрузка изображения раздела по URL
	{
		global $sql,$page;
		$res = array(
			'res'=> false,
			'msg'   => '',
			'i_file'=> '',
		);
		$checkRule = array();
		$checkRule[] = array('section_id', '/^\d+$/');
		$checkRule[] = array('url'       ,'/^http:\/\//');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$res = self::SecIUploadUrl($_POST['section_id'],$_POST['url'],$this->imgthmb_w,$this->imgthmb_h,$this->imgthmb_m);
		} else $res['msg'] = 'Неверные значения!';
		
		$res['res'] = $res['res']===true?'t':'f';
		return json_encode($res);
	}
	
	public function ajxSecIDrp()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('section_id', '/^\d+/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query =  sprintf('UPDATE cms_sections SET sec_imgfile=\'\' WHERE section_id = %d RETURNING sec_imgfile;',
				$_POST['section_id']);
			$result = $sql->query_first_row($query);
			if ($result!=false) {
				$filename = $_POST['section_id'].'.jpg';
				@unlink(self::$imgthmbpath.$filename);
				@array_map('unlink',glob(self::$imgthmbpath.'*/'.$filename));
				$res = 't';
				return json_encode($res);
			} else $checkResult['db'] = 'mstk';
		}
		return json_encode(array('error'=>$checkResult));   
	}
	
	public function ajxSecdrp()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('section_id'     , '/^\\d{1,}$/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0) {
			$query = sprintf ('select count(*) as sec_count from cms_sections where sec_parent_id=%d;', 
			$_POST['section_id']);
			$dataset = $sql->query_first_assoc($query);
			if ($dataset['sec_count']>0) $checkResult[] = array('f'=>'dpndc','s'=>'haschild');

			$query = sprintf ('select count(*) as sec_count from cms_sections where section_id=%d and sec_units<>\'\';', 
			$_POST['section_id']);
			$dataset = $sql->query_first_assoc($query);
			if ($dataset['sec_count']>0) $checkResult[] = array('f'=>'dpndc','s'=>'hasunits');			
		}
		if (count($checkResult)==0) {
			$query = sprintf('select __cms_tags_sections__assign(%d,%s);', 
				$this->page['section_id'],
				$sql->pgf_array_text(array())
			);			
			$db_res = $sql->query_first_row($query);
			
			$query = sprintf ('SELECT sec_url_full FROM cms_sections WHERE section_id=(SELECT sec_parent_id FROM cms_sections i WHERE section_id=%d);', 
				$_POST['section_id']);
			$dataset = $sql->query_first_row($query);
			$new_url= $dataset[0] === null ?'':$dataset[0];
			$filename = $_POST['section_id'].'.jpg';
			@unlink(self::$imgthmbpath.$filename);
			@array_map('unlink',glob(self::$imgthmbpath.'*/'.$filename));
			
			$query = sprintf ('delete from cms_sections where section_id=%d;', 
				$_POST['section_id']);
			$res = $sql->command($query)>0?'t':'f';
			return json_encode(array('r'=>$res,'url'=>$new_url));
		} 
		return json_encode(array('error'=>$checkResult));
	}
	
	public function ajxFileList()
	{
		global $sql,$cfg;
		$checkRule = array();
		$checkRule[] = array('url'     , '.');
		$checkRule[] = array('type'    , '/^(file|image)$/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (strpos($_POST['url'],'..')!==false) {
			$checkResult[] = array('f'=>'url','s'=>'tryhack');
		}
		if (count($checkResult)==0) {
			if ($_POST['url'] === '/') $_POST['url'] = '_';
			$_POST['url'] = trim($_POST['url'],'/').'/';
			$dirurl = ($_POST['type'] === 'file'?$cfg['filespath']:$cfg['imagespath']).$_POST['url'];
			$res = '';
			if (file_exists($dirurl)) {
				$i=0;
				$files = array();
				foreach (scandir($dirurl,SCANDIR_SORT_NONE) as $file) if (!in_array($file, array('.','..'), true) && is_file($dirurl.$file))
					$files[$file] = filemtime($dirurl.$file);
				arsort($files);
				$files = array_keys($files);
				foreach($files as $file) {
					if ($_POST['type'] === 'image')
						$img = '<img src="/img/resizer/?url='.urlencode('/'.$dirurl.$file).'&w=50&h=50&jo=1" style="padding:2px;" /> ';
					else $img = '';
					$res .= '<tr class="'.(++$i%2==0?'':'even').'"><td colspan=2><a onclick="selectURL(\'/'.$dirurl.$file.'\');" href="#">'.$img.$file.'</a><div class="fsize">'.
					number_format(filesize($dirurl.$file)/1024,2).' Кб.<br/><a onclick="removeFile(\''.$file.'\',this);" href="#">Удалить</a></div></td></tr>';
				}
			} else $res .= '';
			return json_encode($res);
		} 
		return json_encode(array('error'=>$checkResult));
	}	
	
	public function ajxFileRemove()
	{
		global $sql,$cfg;
		$checkRule = array();
		$checkRule[] = array('url'     , '.');
		$checkRule[] = array('type'    , '/^(file|image)$/');
		$checkRule[] = array('file'    , '.');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if ((strpos($_POST['url'],'..')!==false) || (strpos($_POST['file'],'..')!==false)) {
			$checkResult[] = array('f'=>'url','s'=>'tryhack');
		}
		if (count($checkResult)==0) {
			if ($_POST['url'] === '/') $_POST['url'] = '_';
			$_POST['url'] = trim($_POST['url'],'/').'/';
			$filepath = ($_POST['type'] === 'file'?$cfg['filespath']:$cfg['imagespath']).$_POST['url'].$_POST['file'];
			$res = false;
			if (file_exists($filepath) && is_file($filepath)) {
				$res = @unlink($filepath);
			}
            return json_encode($res?'t':'f');
		} 
		return json_encode(array('error'=>$checkResult));
	}
	
	public function ajxFileUpload()
	{
		global $sql,$cfg;
		$checkRule = array();
		$checkRule[] = array('url'     , '.');
		$checkRule[] = array('type'    , '/^(file|image)$/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (strpos($_POST['url'],'..')!==false || stripos($_POST['name'],'.htaccess')!==false) {
			$checkResult[] = array('f'=>'url','s'=>'tryhack');
		}
		if (count($checkResult)==0) {
			$res = '';
			if ($_POST['url'] === '/') $_POST['url'] = '_';
			$_POST['url'] = trim($_POST['url'],'/').'/';
			#pluploader
			$chunk = isset($_POST['chunk']) ? (int)$_POST['chunk'] : 0;
			$chunks = isset($_POST['chunks']) ? (int)$_POST['chunks'] : 0;
			$fileName = isset($_POST['name']) ? $_POST['name'] : '';
			$dirurl = ($_POST['type'] === 'file'?$cfg['filespath']:$cfg['imagespath']).$_POST['url'];

			$fileName = preg_replace('/[^\w\._0-9]+/', '_', Translit($fileName)); //security
			$targetDir = $dirurl;
			$maxFileAge = 5 * 3600; // Temp file age in seconds
			@set_time_limit(5 * 60);

			if ($chunks < 2 && file_exists($targetDir.$fileName)) { //unique name  if not chunking
				$ext = strrpos($fileName, '.');
				$fileName_a = substr($fileName, 0, $ext);
				$fileName_b = substr($fileName, $ext);
				$count = 1;
				do {
					$fileName = $fileName_a.'_'.$count++.$fileName_b;
				} while (file_exists($fileName));
			}

			$filePath = $targetDir.'/'.$fileName;
			$filePathPart = $targetDir.'/'.$fileName.'part';
			if (!file_exists($targetDir)) @mkdir($targetDir,0755,true);

			$contentType = (isset($_SERVER['HTTP_CONTENT_TYPE'])?$_SERVER['HTTP_CONTENT_TYPE']:'').
						   (isset($_SERVER['CONTENT_TYPE'])?$_SERVER['CONTENT_TYPE']:'');

			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
				if ($in = fopen($_FILES['file']['tmp_name'], 'rb')) {
					if ($out = fopen($filePathPart, $chunk == 0 ? 'wb':'ab'))
						while ($buff = fread($in, 4096)) fwrite($out, $buff);
					else $res = '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}';
					fclose($in);
					fclose($out);
					@unlink($_FILES['file']['tmp_name']);
				} else $res = '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}';
			} else $res = '{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}';

			if (!$chunks || $chunk == $chunks - 1) 
				rename($filePathPart, $filePath);

			if ($res = '') $res = '{"jsonrpc" : "2.0", "result" : null, "id" : "id"}';			

			return $res;
		} 
		return json_encode(array('error'=>$checkResult));
	}

	public static function buildAdminMenu(&$subItems, $urlprefix='', $ul='', $li='')
	{
		$str = '<ul class="mnu_ul'.$ul.'">';
		foreach($subItems as $subItem) {
			$str .= 
			'<li class="mnu_li'.$li.' mnuitem" id="sec'.$subItem['section_id'].'">
			<a href="/'.$urlprefix.$subItem['sec_url_full'].'" class="mnu_hc'.$subItem['sec_howchild'].(isset($subItem['_current'])?' mnu_curr':'').(isset($menuItem['_selected'])?' mnu_slct':'').($subItem['sec_hidden'] === 't'?' mnu_hddn':'').'">'.$subItem['sec_nameshort'].'</a>'.
			(isset($subItem['_children'])?(count($subItem['_children'])>0?self::buildAdminMenu($subItem['_children'],$urlprefix,$ul.'l',$li.'i'):''):'').'</li>';
		}
		$str .= '</ul>';
		return $str;
	}

    public function ajxSSELoad()
    {
        global $sql;
        $checkRule = array();
        $checkResult = checkForm($_GET,$checkRule);
        if (count($checkResult)==0)
        {
            return GetShape('pages/ss_edit');
        }
        return json_encode(array('error'=>$checkResult));
    }

    public function ajxSSESave()
    {
        global $sql;
        $checkRule = array();
        $checkRule[] = array('code', '/^\w{2}_\w+/');
        $checkRule[] = array('data', '');
        $checkRule[] = array('mult', '/^(m|s|l)$/');
        $checkResult = checkForm($_POST, $checkRule, $this->hasRight());
        if (count($checkResult) == 0) {
            $data = $_POST;
            $code_parts = explode('_',$data['code']);
            $sec = $code_parts[0]; unset($code_parts[0]);
            $name = implode('_',$code_parts); unset($code_parts);
            $secs = array(
                'eg'=>0,
                'ep'=>$this->page['section_id']
            );
            /* @var $sql pgdb */
            if ($data['code'] === 'ep_content') {

                $query = $sql->pr_u('cms_sections', array(
                    'sec_content' => $sql->t($data['data']),
                ), 'section_id=' . $sql->d($secs[$sec]));
                $res_count = $sql->command($query);

            } elseif ($data['code'] === 'ep_namefull') {

                $query = $sql->pr_u('cms_sections', array(
                    'sec_namefull' => $sql->t($data['data']),
                ), 'section_id=' . $sql->d($secs[$sec]));
                $res_count = $sql->command($query);

            } else {

                $query = $sql->pr_u('cms_sections_string', array(
                    'secs_str' => $sql->t($data['data']),
                    'secs_multiline' => $sql->b($data['mult'] === 'm'),
                ), 'sec_id=' . $sql->d($secs[$sec]) . ' AND secs_code=' . $sql->t($name));
                $res_count = $sql->command($query);
                if ($res_count === 0) {
                    $query = $sql->pr_i('cms_sections_string', array(
                        'sec_id' => $sql->d($secs[$sec]),
                        'secs_code' => $sql->t($name),
                        'secs_str' => $sql->t($data['data']),
                        'secs_multiline' => $sql->b($data['mult'] === 'm'),
                    ));
                    $res_count = $sql->command($query);
                }

            }
            return json_encode($res_count > 0 ? 't' : 'f');
        }
        return json_encode(array('error' => $checkResult));
    }


	#Content
	public function getContent()
	{
		global $shape,$cfg;

		$editMode = $this->hasRight();
		$adminPart = $editMode && core::$inEdit;
        shp::$editMode = $editMode && !$adminPart;
		$shape['jses'] = '';

		#Модули
		$unitNum = 0;
		foreach ($this->pageUnits as $pageUnit) {
			$shape['pgunit_'.$unitNum] = $pageUnit->render();
			$unitNum++;
		}
        while($unitNum<=9)
            $shape['pgunit_'.$unitNum++] = ''; #Clean lasts

		$shape['head'] = $this->page['sec_namefull'];
		$shape['description'] = $this->page['sec_description'];
		$shape['keywords'] = $this->page['sec_keywords'];
		
		#Теги
		$secTagsArr = $this->getSecTags();
		$secTags = implode(',',$secTagsArr);
		
		if ($adminPart) {
			$vieweditLink = "new Element('a',{'href':'/".$this->pageMainUri."'+'?'+new Date().getTime()}).inject(usrcntrldiv).grab(new Element('img',{'src':'/img/edt/btnview.png','title':'Просмотреть страницу'}));";

			$shape['jses']  .= "
			<link href=\"/akcms/css/v1/style_adm_cntrl.css\" rel=\"stylesheet\" type=\"text/css\"/>
			<script type=\"text/javascript\">
			window.addEvent('domready', function() {
				var userControl = function() {
					var usrcntrldiv = new Element('nobr').inject(new Element('div',{'class':'admcntrl','id':'admcntrl'}).inject(new Element('div',{'class':'admcntrl_cnt".(core::$inEdit?' inedit':'')."'}).inject(document.body)));
					new Element('img',{'src':'/img/adm/adm_logo.png','class':'admlogo','width':212,'height':19}).inject(usrcntrldiv);
					new Element('img',{'src':'/img/edt/btnlgout.png','title':'Выход'}).inject(usrcntrldiv).addEvent('click',function() { if (confirm('Выйти из панели управления?')) document.location='/_logout/'; });
					new Element('a',{'href':'/_/'}).inject(usrcntrldiv).grab(new Element('img',{'src':'/img/edt/btnhome.png','title':'На главную редактора'}));
					".$vieweditLink. '
				};
				userControl();
			});
			</script>';
			$this->_buildPageSections($this->getMenu());
			
            #Заполняем массивы модулей
            $sec_all_units = array();
            $sec_units_array = explode(',',$this->page['sec_units']);
            $sec_units = array();
            if (trim($this->page['sec_units']) !== '') foreach ($sec_units_array as $k)
                $sec_units[]=array('k'=>$k,'v'=>$cfg['pgunits'][$k]);
            foreach ($cfg['pgunits'] as $k=>$v)
                if (!in_array($k, $sec_units_array, true)) $sec_all_units[]=array('k'=>$k,'v'=>$v);
				
			
			if ($this->page['section_id']!=1) {
				$pageEdt = $this->page;
				$pageEdt['_selected']=true;
                unset($pageEdt['sec_content'], $pageEdt['sec_sort'], $pageEdt['sec_created'], $pageEdt['sec_system'], $pageEdt['sec_glr_id'], $pageEdt['sec_contshort'], $pageEdt['sec_lst_mofify'], $pageEdt['sec_url_priority'], $pageEdt['sec_params']);
                $this->pageSections[$pageEdt['section_id']]=$pageEdt;
			}
			$shape['jses']  .= '
			<script type="text/javascript">currpage='.json_encode(array(
				'pageurl'=>$this->pageUri,
				'pagemainurl'=>$this->pageMainUri,
				'sec_contshort'=>$this->page['sec_contshort'],
				'id'=>$this->page['section_id'],
				'sec_tags'=>$secTags,
				'all_tags'=>$this->getAllTags(),
				'secs'=>$this->pageSections,
				'glr_id'=>$this->page['sec_glr_id'],
				'sec_all_units'=>$sec_all_units,
				'sec_units'=>$sec_units,
                'sec_page_child'=>$this->page['sec_page_child'],
				'sec_pages'=>assocArray2ajax($cfg['pages']))).';
			function tinyBrowser (field_name, url, type, win) {
				var cmsURL = "/akcms/js/v1/plupload/_ub.html" + "?type=" + type + "&url='.($this->pageUri!==''?$this->pageUri:'/').'&rnd=" + Math.random(1,999999);
				tinyMCE.activeEditor.windowManager.open({
					file : cmsURL,
					title : "ITteka Browser",
					width : 470, 
					height : 400,
					resizable : "yes",
					scrollbars : "yes",
					inline : "yes",
					close_previous : "no"
				}, {
					window : win,
					input : field_name
				});
				return false;
			}
			</script>		
			';//,'glrsall'=>$this->getGalleriesList()
			
			#Меню
			$shape['menuedit'] = self::buildAdminMenu($this->pageMenu,(core::$inEdit?'_/':'')); #
		} 
		if (!core::$inEdit) {
			VisualTheme::buildPageExtension($this); // Расширение страницы под конкретный сайт находится в теме
		}

        $shape['pageuri'] = $this->pageUri;
        $shape['pagemainuri'] = $this->pageMainUri;
		$pagecontent = $this->page['sec_content'];

		return $pagecontent;
	}
}