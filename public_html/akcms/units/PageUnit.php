<?php
final class PageUnit extends CmsPage {
	public $pageUri;
	public $pageMainUri;
	public $imgpath;
	protected $pageSections = array();	// массив разделов для ajax
	public $pageUnits = array();
	public static $imgthmbpath = 'img/pages/';
    public $params = array();
	private $imgthmb_w = 973;
	private $imgthmb_h = 615;
	private $imgthmb_m = 3;
	public $editMode = false;
	public $inEditCan = false;

	public function initAjx()
	{
		$ajaxes = array(
            '_cntsve' => array(
                'func' => 'ajxCntSve'),
            '_optsve' => array(
                'func' => 'ajxOptSve'),
            '_secsve' => array(
                'func' => 'ajxSecInsSve'),
            '_secins' => array(
                'func' => 'ajxSecInsSve'),
            '_secdrp' => array(
                'func' => 'ajxSecDrp'),
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
            '_sec_data' => array(
                'func' => 'ajxSecData'),
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

	public function __construct(&$pageTemplate)
	{

        global $sql,$cfg;
		define('MENU_FIELDS','section_id,sec_parent_id,sec_url_full,sec_url,sec_nameshort,sec_namefull,sec_imgfile,sec_showinmenu,sec_openfirst,sec_to_news,sec_enabled,sec_title,sec_keywords,sec_description,sec_units,sec_from,sec_howchild,sec_page,sec_page_child, not sec_enabled or not sec_showinmenu as sec_hidden ');
		$pathstr_str = $GLOBALS['pathstr'];

		$query = sprintf ('select *,now()>sec_from as sec_from_showing from cms_sections where %s ilike sec_url_full || %s order by length(sec_url_full) desc limit 1;',
			$sql->t($pathstr_str),
			"'%'");
		$this->page = $sql->query_first_assoc($query);
		//($loadAnyway?'':'and sec_enabled and now()>sec_from')

		// Добавляем в список разрешений всех родителей и себя
        foreach ($sql->da_a($this->page['sec_ids_closest']) as $id) {
            $this->acl[] = 'pg'.$id;
        }

        $this->inEditCan = $this->hasRight('inEdit',false,true);
        $this->editMode = $this->hasRight();
        $loadAnyway = core::$isAjax || core::$inEdit || $this->editMode;
        // Отклонить страницу если прав недостаточно
        if (!$loadAnyway && ($this->page['sec_enabled']==='f' || $this->page['sec_from_showing']==='f')) {
            $this->page=false;
        }

		if ($this->page===false) throw new CmsException('page_not_found');

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

        $this->pageUri = $pathstr_str === '/'?'':$pathstr_str;
        $this->pageMainUri = $this->page['sec_url_full'] === '/'?'':$this->page['sec_url_full'];
        $this->imgpath = '/'.self::$imgthmbpath.($this->pageUri === '/'?'/_/':'/'.$GLOBALS['pathstr']);

        $pageTemplate = $this->page['sec_page'];
        if (core::$inEdit) {
            if (!$this->inEditCan) { //!$this->editMode
                header('Location: /'.$this->pageMainUri);
                //throw new CmsException('login_needs_');
            }
            $pageTemplate = 'editpage2';
            //if (core::$devTest) $pageTemplate = 'editpage2';

            //if ($_SERVER['REMOTE_ADDR']=='109.172.77.170') $pageTemplate = 'editpage2';
        }

		$this->title = $this->page['sec_title'] !== '' ? $this->page['sec_title'] : $this->page['sec_namefull'].' - '.$cfg['site_title'];

		//$this->getMenu();
		$this->getBreadcrumbs();

		$unitsCount = 0;
		if (trim($this->page['sec_units']) !== '') foreach (explode(',',$this->page['sec_units']) as $pgUnitClass)
			if (isset($cfg['pgunits'][$pgUnitClass]))
			{
				$unitsCount++;
				$this->pageUnits[$pgUnitClass] = new $pgUnitClass($unitsCount===1?$params_arr:array());
			} else trigger_error('Wrong unit "'.$pgUnitClass.'" on page '.$this->pageUri.'. All list:'.$this->page['sec_units'],E_USER_WARNING);

		if ($unitsCount===0 && count($params_arr)>0)
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
			$htmlform = shp::tmpl('sndmstk_mail', $uform);
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
	
	public function ajxCntSve()
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
  
	public static function _imgduplicate($pathold, $pathto, &$imggrabbed) { // Загружает изображения с других серверов
		$dirpath = mb_substr($pathto, 0, -1);
		if(!file_exists($dirpath)) mkdir($dirpath,0775,true);
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
  
	public function ajxSecInsSve()
	{
		global $sql;
		$checkRule = array();
        $checkRule[] = array('section_id'     , '/^\\d{1,}$/');
        $checkRule[] = array('sec_parent_id'     , '/^\\d{1,}$/');
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
		$checkRule[] = array('sec_from'  , '/^\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}$/');
		$checkRule[] = array('sec_howchild'  , '/^(0|1|2|3)$/');
		$checkRule[] = array('sec_page'  , '/^[a-zA-Z0-9\-\_]+$/');
		$checkResult = checkForm($_POST,$checkRule,($this->page['section_id']==1 && $_POST['section_id']==='0' && $_POST['sec_parent_id']==='0'?$this->hasRight('admin',false,true):$this->hasRight()));
		if (count($checkResult)===0) {
            if ($_POST['section_id'] === 1) $_POST['sec_url'] = '/';
            $data = array(
                'sec_url'=>$sql->t(mb_strtolower($_POST['sec_url'])),
                'sec_description'=>$sql->t($_POST['sec_description']),
                'sec_enabled'=>$sql->b($_POST['sec_enabled']),
                'sec_title'=>$sql->t($_POST['sec_title']),
                'sec_keywords'=>$sql->t($_POST['sec_keywords']),
                'sec_namefull'=>$sql->t($_POST['sec_namefull']),
                'sec_nameshort'=>$sql->t($_POST['sec_nameshort']),
                'sec_openfirst'=>$sql->t($_POST['sec_openfirst']),
                'sec_to_news'=>$sql->b($_POST['sec_to_news']),
                'sec_showinmenu'=>$sql->b($_POST['sec_showinmenu']),
                'sec_from'=>$sql->t($_POST['sec_from']),
                'sec_howchild'=>$sql->d($_POST['sec_howchild']),
                'sec_parent_id'=>$sql->d($_POST['sec_parent_id']),
                'sec_page'=>$sql->t($_POST['sec_page']),
            );

            if ($_POST['section_id']!=='0')
                $query = $sql->pr_u('cms_sections',$data,'section_id='.$sql->d($_POST['section_id']));
            else
                $query = $sql->pr_i('cms_sections',$data);

            $query .= ' RETURNING '.MENU_FIELDS.',sec_content';

            $res = false;
			try {
                $res = @$sql->query_fa($query);
			} catch (DBException $e) {
				if ($e->isDuplicate)
					$checkResult[] = array('f'=>'sec_url','s'=>'Это url уже занято');
				else throw $e;
			}
			if (count($checkResult)===0) {
			    if ($res && isset($_POST['section_id'])) {
			        $this->reindex($res);
                    $this->buildSiteMapXml();
                }

                $icon = 'fa fa-file-text-o';
                if ($res['sec_enabled']=='f') $icon = 'fa fa-times-circle';
                else if ($res['sec_hidden']=='t') $icon = 'fa fa-eye-slash';
                else if (strtotime($res['sec_from'])>time()) $icon = 'fa fa-clock-o';

                return json_encode(array('r'=>$res!==false?'t':'f','url'=>$res['sec_url_full'],'id'=>$res['section_id'],'icon'=>$icon));
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
                        $file_ext = pathinfo(mb_strtolower($upl['name']),PATHINFO_EXTENSION);
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
									if (!file_exists($dirpath)) mkdir($dirpath,0775,true);
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
					if (!file_exists($dirpath)) mkdir($dirpath,0775,true);
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
		$checkRule[] = array('url'       ,'/^(http|https):\/\//');
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
	
	public function ajxSecDrp()
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
		global $cfg;
		$checkRule = array();
		$checkRule[] = array('type'    , '/^(file|image)$/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (strpos($_POST['url'],'..')!==false) {
			$checkResult[] = array('f'=>'url','s'=>'tryhack');
		}
		if (count($checkResult)==0) {
            $targetDir =
                ($_POST['type'] === 'file'?$cfg['filespath']:$cfg['imagespath']).
                floor($this->page['section_id']/1000).'/'.
                ($this->page['section_id']%1000);
			$res = '';
			if (file_exists($targetDir)) {
				$i=0;
				$files = array();
                foreach(new DirectoryIterator($targetDir) as $item)
                {
                    if (!$item->isDot() && $item->isFile())
                    {
                        $files[] = [
                            'mTime'=>$item->getMTime(),
                            'file'=>$item->getFilename(),
                            'mTimeText'=>VisualTheme::dateRus('j M в H:i',$item->getMTime()),
                            'size'=>$item->getSize()
                        ];
                    }
                }
                arsort($files);

				foreach($files as $file) {
					if ($_POST['type'] === 'image')
						$img = '<img src="/img/resizer/?url='.urlencode('/'.$targetDir.'/'.$file['file']).'&w=50&h=50&jo=1" style="padding:2px;" /> ';
					else $img = '';
					$res .= '<tr class="'.(++$i%2===0?'':'even').'">'.
                        '<td colspan=2><a onclick="selectURL(\'/'.$targetDir.'/'.$file['file'].'\');" href="#">'.$img.$file['file'].'</a>'.
                        '<div class="fsize">'.$file['mTimeText'].'<br/>'.prettySize($file['size']).'<br/>'.
                        '<a onclick="removeFile(\''.$file['file'].'\',this);" href="#">Удалить</a>'.
                        '</div></td></tr>';
				}
			} else $res .= '';
			return json_encode($res);
		} 
		return json_encode(array('error'=>$checkResult));
	}	
	
	public function ajxFileRemove()
	{
		global $cfg;
		$checkRule = array();
		$checkRule[] = array('type'    , '/^(file|image)$/');
		$checkRule[] = array('file'    , '.');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if ((strpos($_POST['url'],'..')!==false) || (strpos($_POST['file'],'..')!==false)) {
			$checkResult[] = array('f'=>'url','s'=>'tryhack');
		}
		if (count($checkResult)===0) {
            $targetDir =
                ($_POST['type'] === 'file'?$cfg['filespath']:$cfg['imagespath']).
                floor($this->page['section_id']/1000).'/'.
                ($this->page['section_id']%1000);
            $filepath = $targetDir.'/'.$_POST['file'];
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
		global $cfg;
		$checkRule = array();
		$checkRule[] = array('type'    , '/^(file|image)$/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (strpos($_POST['url'],'..')!==false || stripos($_POST['name'],'.htaccess')!==false) {
			$checkResult[] = array('f'=>'url','s'=>'tryhack');
		}
		if (count($checkResult)===0) {
			$res = '';
			if ($_POST['url'] === '/') $_POST['url'] = '_';
			#pluploader
			$chunk = isset($_POST['chunk']) ? (int)$_POST['chunk'] : 0;
			$chunks = isset($_POST['chunks']) ? (int)$_POST['chunks'] : 0;
			$fileName = isset($_POST['name']) ? $_POST['name'] : '';
            $targetDir =
                ($_POST['type'] === 'file'?$cfg['filespath']:$cfg['imagespath']).
                floor($this->page['section_id']/1000).'/'.
                ($this->page['section_id']%1000);

			$fileName = preg_replace('/[^\w\._0-9]+/', '_', Translit($fileName)); //security
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
			$filePathPart = $targetDir.'/'.$fileName.'-part';
            ChromePhp::log($targetDir);
			if (!file_exists($targetDir)) mkdir($targetDir,0775,true);

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

			if ($res === '') $res = '{"jsonrpc" : "2.0", "result" : null, "id" : "id", "location":"/'.$filePath.'"}';

			return $res;
		} 
		return json_encode(array('error'=>$checkResult));
	}

	public static function buildAdminMenu($subItems, $urlprefix='', $ul='', $li='')
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
            return shp::tmpl('pages/ss_edit');
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
        http_response_code(405);
        return json_encode(array('error' => $checkResult));
    }

    /** Формируем меню для TreeView
     * @param $item
     */
    function menuToTreeView(&$item){
       if (isset($item[0])) foreach ($item as &$subitem) $this->menuToTreeView($subitem);
       else {
           //if (!isset($item['section_id'])) return;
           $item['id'] = $item['section_id'];
           $item['text'] = $item['sec_nameshort'];
           $item['href'] = '/'.$item['sec_url_full'];
           if ($item['sec_enabled']=='f') $item['icon'] = 'fa fa-times-circle';
           else if ($item['sec_hidden']=='t') $item['icon'] = 'fa fa-eye-slash';
           else if (strtotime($item['sec_from'])>time()) $item['icon'] = 'fa fa-clock-o';

           unset($item['section_id']);
           unset($item['sec_parent_id']);
           unset($item['sec_url_full']);
           unset($item['sec_url']);
           unset($item['sec_nameshort']);
           unset($item['sec_namefull']);
           unset($item['sec_imgfile']);
           unset($item['sec_showinmenu']);
           unset($item['sec_openfirst']);
           unset($item['sec_to_news']);
           unset($item['sec_enabled']);
           unset($item['sec_title']);
           unset($item['sec_keywords']);
           unset($item['sec_description']);
           unset($item['sec_units']);
           unset($item['sec_from']);
           unset($item['sec_howchild']);
           unset($item['sec_page']);
           unset($item['sec_page_child']);
           unset($item['sec_hidden']);

           $item['tags'] = '';
           if (isset($item['_children'])) {
               $item['nodes'] = $item['_children'];
               unset($item['_children']);
               $item['tags'] = [count($item['nodes'])];
               foreach ($item['nodes'] as &$subitem) $this->menuToTreeView($subitem);
           }
       }
    }

    private function variables(){
        global $cfg;

        $secTags = implode(',',$this->getSecTags());

        if ($this->page['section_id']!=1) {
            $pageEdt = $this->page;
            $pageEdt['_selected']=true;
            unset($pageEdt['sec_content'], $pageEdt['sec_sort'], $pageEdt['sec_created'], $pageEdt['sec_system'], $pageEdt['sec_glr_id'], $pageEdt['sec_contshort'], $pageEdt['sec_lst_modify'], $pageEdt['sec_url_priority'], $pageEdt['sec_params']);
            $this->pageSections[$pageEdt['section_id']]=$pageEdt;
        }

        $sec_all_units = array();
        $sec_units_array = explode(',',$this->page['sec_units']);
        $sec_units = array();
        if (trim($this->page['sec_units']) !== '')
            foreach ($sec_units_array as $k)
                $sec_units[]=array('k'=>$k,'v'=>$cfg['pgunits'][$k]);
        foreach ($cfg['pgunits'] as $k=>$v)
            if (!in_array($k, $sec_units_array, true))
                if (!isset($cfg['pgunits_hidden'][$k]))
                    $sec_all_units[]=array('k'=>$k,'v'=>$v);

        return [
            'pageurl'=>$this->pageUri,
            'pagemainurl'=>$this->pageMainUri,
            'sec_contshort'=>$this->page['sec_contshort'],
            'id'=>$this->page['section_id'],
            'sec_tags'=>$secTags,
            'all_tags'=>$this->getAllTags(),
            'glr_id'=>$this->page['sec_glr_id'],
            'sec_all_units'=>$sec_all_units,
            'sec_units'=>$sec_units,
            'sec_page_child'=>$this->page['sec_page_child'],
        ];
    }

    public function ajxSecData()
    {
        global $cfg;
        $checkRule = array();
        $checkResult = checkForm($_GET,$checkRule,$this->hasRight());
        if (count($checkResult)===0) {
            return json_encode($this->variables());
        }
        return json_encode(array('error'=>$checkResult));
    }


    #Content
	public function getContent()
	{
		global $shape,$cfg;

		$adminPart = core::$inEdit;
        shp::$editMode = $this->editMode && !$adminPart;
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

		if ($adminPart) {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Expires: 0');
            #Теги
            #Меню
            $shape['pageMainUri'] = $this->pageMainUri;

            $treeViewData = $this->getAllMenu($this->inEditCan);
            $this->menuToTreeView($treeViewData);
            //$this->_buildPageSections($this->getMenu($this->inEditCan));
            $this->_buildPageSections($this->getAllMenu($this->inEditCan));

            $akcms = [
                'currpage'      => $this->variables(),
                'treeViewData'  => $treeViewData,
                'secs'          => $this->pageSections,
                'sec_pages'     => assocArray2KeyValue($cfg['pages']),
            ];

            //todo remove currpage, js function

            $GLOBALS['shape']['menuedit'] = self::buildAdminMenu($this->getMenu($this->inEditCan),'_/'); #todo remove old menu
            $currpage = $this->variables();
            $currpage['secs'] = $this->pageSections;
            $currpage['sec_pages'] = assocArray2KeyValue($cfg['pages']);

			$shape['jses']  .= '
			<script type="text/javascript">document.akcms='.json_encode($akcms).';currpage='.json_encode($currpage).';
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
			
		}
		if (!core::$inEdit) {
			VisualTheme::buildPageExtension($this); // Расширение страницы под конкретный сайт находится в теме
		}

        $shape['pageuri'] = $this->pageUri;
        $shape['pagemainuri'] = $this->pageMainUri;

		return $this->page['sec_content'];
	}
}