<?php

class Pg_Ctlg extends PgUnitAbstract {
	public $imgcatipath = 'img/cat/';

	function initAjx()
	{
		global $page;
		return array(
		$page->pageUri.'_catisve' => array(
			'func' => 'ajxCatiSave',
			'object' => $this),
		$page->pageUri.'_catiiupl' => array(
			'func' => 'ajxCatiIUpload',
			'object' => $this),	
		$page->pageUri.'_catiidrp' => array(
			'func' => 'ajxCatiIDrp',
			'object' => $this),			
		$page->pageUri.'_catidrp' => array(
			'func' => 'ajxCatidrp',
			'object' => $this),
        $page->pageUri.'_catlist' => array(
			'func' => 'ajxCatiList',
			'object' => $this),
        $page->pageUri.'_catsetordr' => array(
			'func' => 'ajxCatiSetOrder',
			'object' => $this),
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
  
	function reindex($indxnews)
	{
		// if (!$this->hasRight()) return false;
		// global $sql,$page;
		// $indstr = strip_tags($indxnews['news_head'].' '.$indxnews['news_short'].' '.$indxnews['news_content']);
		// $base_forms = $page->makeSrchWords($indstr);

		// $query = sprintf ('select __cms_srchwords_news__assign(%d,%s);', 
			// $indxnews['news_id'],
			// $sql->pgf_array_text($base_forms));
		// $res_count = $sql->command($query);
		// return $res_count>0?'t':'f';
		return 't';
	}

	function ajxCatiSave()
	{
		global $sql,$page;
		$fild_key = 'cati_id';
		$checkRule = array();
		$checkRule[] = array('cati_id'       , '/^\\d{1,}$/');
		$checkRule[] = array('cati_nameshort'   , '.');
		$checkRule[] = array('cati_namefull'   , '.');
		$checkRule[] = array('cati_desc'   , '');
		$checkRule[] = array('cati_artcl'   , '');
		$checkRule[] = array('cati_bcost'   , '');
		// $checkRule[] = array('cati_cost'   , '^\\d{1,}\\.*d{0,2}$');
		// $checkRule[] = array('cati_costold'   , '^\\d{1,}\\.*d{0,2}$');
		$checkRule[] = array('cati_show'   , '/^(t|f)$/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			if ($_POST[$fild_key]==0) {
				$query = sprintf ('INSERT INTO cms_cat_gds(cati_nameshort,cati_namefull,cati_desc,cati_artcl,cati_bcost,cati_cost,cati_costold,cati_show,cati_sec_id) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%d) RETURNING cati_id;', 
					$sql->t($_POST['cati_nameshort']),
					$sql->t($_POST['cati_namefull']),
					$sql->t($_POST['cati_desc']),
					$sql->t($_POST['cati_artcl']),
					$sql->t($_POST['cati_bcost']),
					$_POST['cati_cost'],
					$_POST['cati_costold'],
					$sql->t($_POST['cati_show']),
					$page->page['section_id']
				);
				$res = $sql->query_first_row($query);
				if ($res!==false) {
					$_POST['cati_id'] = $res[0];
					$this->reindex($_POST);
				}
				return json_encode($res!==false?'t':'f');
			} else {
				$query = sprintf ('UPDATE cms_cat_gds SET cati_nameshort=%s,cati_namefull=%s,cati_desc=%s,cati_artcl=%s,cati_bcost=%s,cati_cost=%s,cati_costold=%s,cati_show=%s WHERE cati_id=%d;', 
					$sql->t($_POST['cati_nameshort']),
					$sql->t($_POST['cati_namefull']),
					$sql->t($_POST['cati_desc']),
					$sql->t($_POST['cati_artcl']),
					$sql->t($_POST['cati_bcost']),
					$_POST['cati_cost'],
					$_POST['cati_costold'],
					$sql->t($_POST['cati_show']),
					$_POST['cati_id']
					);
				$res = $sql->command($query)>0?'t':'f';
				if ($res=='t') $this->reindex($_POST);
				return json_encode($res);
			}
		} 
		return json_encode(array('error'=>$checkResult));
	}    
  
	function ajxCatiIUpload() 
	{
		global $sql,$page;
		$res_msg = ''; $res_stat = 0; $res_i_file = '';
		$JsHttpRequest = new JsHttpRequest("UTF-8");
		$checkRule = array();
		$checkRule[] = array('cati_id', '/^\d+/');
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
							if ($file_ext=='jpg')
							{
								$res_stat = 1;
								$max_width = 800;
								$max_height = 600;
								$imginfo = false;
								
								try {
									$imgRszr = new ImgResizer();
									$dst = $imgRszr->simpleResize($upl['tmp_name'],$max_width,$max_height,0);
									$imginfo = $imgRszr->imginfo;						
								} catch(Exception $e) {}									

								if ($imginfo!==false)
								{
									$res_stat = 2;

									if ($_POST['cati_id']>0)
									{
										$res_stat = 3; 
										$res_stat = 4;
										$i_file = $_POST['cati_id'].'.jpg';
										$pathstr = $this->imgcatipath.$i_file;
										$dirpath = dirname($pathstr);
										if (!file_exists($dirpath)) mkdir($dirpath,0755,true); 
										foreach (array('','s/','pl/','p/','pm/','h/') as $part) @unlink($this->imgcatipath.$part.$i_file);
										ImageJpeg($dst,$pathstr,90); 

										$query = sprintf ('UPDATE cms_cat_gds SET cati_photofile = %s WHERE cati_id = %d;', 
											$sql->t($i_file),
											$_POST['cati_id']);
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

	function ajxCatiIDrp()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('cati_id', '/^\d+/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query =  sprintf('UPDATE cms_cat_gds SET cati_photofile=\'\' WHERE cati_id = %d RETURNING cati_photofile;',
				$_POST['cati_id']);
			$result = $sql->query_first_row($query);
			if ($result!=false) {
				$filename = $result[0];//$_POST['cati_id'].'.jpg'; 
				foreach (array('','s/','pl/','p/','pm/','h/') as $part) @unlink($this->imgcatipath.$part.$filename);
				$res = 't';
				return json_encode($res);
			} else $checkResult['db'] = 'mstk';
		}
		return json_encode(array('error'=>$checkResult));   
	} 	
  
	function ajxCatidrp()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('cati_id'       , '/^\\d{1,}$/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0) 
		{
			$query = sprintf ('DELETE FROM cms_cat_gds WHERE cati_id=%d; --DELETE FROM cms_srchwords_news WHERE cati_id=%d;',
				$_POST['cati_id'],$_POST['cati_id']
			);
			$res = $sql->command($query)>0?'t':'f';
			$filename = $_POST['cati_id'].'.jpg';
			@unlink($this->imgcatipath.$filename);
			@unlink($this->imgcatipath.'s/'.$filename);
			@unlink($this->imgcatipath.'t/'.$filename);
			return json_encode($res);
		} 
		return json_encode(array('error'=>$checkResult));
	}
  
	function ajxCatiList()
	{
		global $sql,$page;
		$checkRule = array();
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query = sprintf('SELECT cati_id as k, cati_nameshort as v FROM cms_cat_gds where cati_sec_id=%d ORDER BY cati_sort',$page->page['section_id']);
			$dataset = $sql->query_all($query);
			return json_encode($dataset!==false?array('r'=>'t','d'=>$dataset):'f');
		}
		return json_encode(array('error'=>$checkResult));   
	}
	
	function ajxCatiSetOrder()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('itm_order', '.');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$i = 0; $f = true;
			foreach($_POST['itm_order'] as $id) {
				$query = sprintf ('UPDATE cms_cat_gds SET cati_sort=%d WHERE cati_id = %d;', 
					++$i,
					@intval($id));
				$f = $sql->command($query) && $f;
			}
			return json_encode($f>0?'t':'f');
		}
		return json_encode(array('error'=>$checkResult));   
	}
	
	function render()
	{
		global $sql,$page,$shape;
		$res = '';
		$editMode = core::$inEdit && $this->hasRight();
		$pageLinkUri = ($editMode?'_/':'').$page->pageMainUri;

		$this->page->page['sec_content'] = '&nbsp;';
		if (count($this->unitParam)==1?preg_match('/^g\d{1,3}$/',$this->unitParam[0])==1:false)
		{
            $catiId = substr($this->unitParam[0],1);
			$query = sprintf ('select * from cms_cat_gds where '.($editMode?'':'cati_show and').' cati_id=%d;', 
			$catiId);
			$item = $sql->query_first_assoc($query);
			if ($item==false) throw new CmsException("page_not_found");
			
			if ($item['cati_photofile']=='') $item['cati_photofile'] = '0.jpg';#
			$imgsrc = $this->imgcatipath.'p/'.$item['cati_photofile'];

			$costStr = $item['cati_bcost'].($item['cati_cost']==='0'?'':(' <b>'.number_format($item['cati_cost'], 0, '.', ' ').'</b>р.'));
			$styleimg = 'style="background-image: url(/img/cat/p/'.$item['cati_photofile'].')"';
		
			$costold = number_format($item['cati_costold'], 2, '.', ' ');
			$href_ask = 'ask?'.http_build_query(array('url'=>'/'.$pageLinkUri.'g'.$item['cati_id'],'item'=>$item['cati_nameshort']));	
			
			$res .= '
			<style>
			.cati {
				margin-right: 4px;
				possition: relative;
			}
			.cati_h h1 {
				font-size: 24px;
				text-transform: uppercase;
				font-family: "Cuprum",Helvetica,Arial,sans-serif;
			}			
			.cati_p {
				width: 305px;
				float:left;
			}
			.cati_pm {
			}
			.cati_d {
				margin-left: 300px;
				font-size: 15px;
			}
			.cati_di {
				width:340px;
				height:232px;
				margin: 0 10px 3px 0;
				float:left;
				font-size: 15px;
				background: #ececec;
			}
			.cati_dt {
			    padding-top: 242px;
				margin-left: 10px;
			}
			.cati_bg {
			    background: #ececec;
			}
			.cati_d ul{
				padding-left: 17px;
			}

			.cati_d table {
				float: inherit;
				border-collapse: collapse;
			}
			.cati_d h1,.cati_d h2 {
			    margin: 0;
			    padding: 9px 9px;
			    color:#fff;
				background: #d1596f;
				font-size: 24px;
				font-family: "Cuprum", Helvetica, Arial, sans-serif;
			}
			.cati_d p{

			    padding: 9px;
			}
			.cati_d table td p, .ftable th p{
				margin: 0;
				padding: 0;
			}
			.cati_d th {
				background-color:#dddddd;
				color:#585858;
				font-size:12px;
				padding-bottom:4px;
				padding-top:5px;
				text-align:center;
			}
			.cati_d td, .cati_d th {
				border:1px solid #c6c6c6;
				font-size:12px;
				padding:3px 7px 2px;
			}			
			
			.cati_c {
				padding-top: 11px;
				font-size: 15px;
			}
			.cati_c b{
				font-size: 24px;
			}
			.cati_c span{
				font-weight: bold;
			}

			.cati_oth_h {
				font-size: 17px;
				margin-top: 20px;
			}
			.cati_ps0 {
				width: 145px;
				height: 130px;
				float:left;
				margin: 0 10px 10px 0;
			}
			.cati_ps1 {
				width: 145px;
				height: 130px;
				float:left;
				margin: 0 0 10px 0;
				border-left: none;
			}
			.cati_pss {
				margin-top: 10px;
				width: 300px;
			}
			</style>			
			<div id="catis"><div class="cati" id="ctlgi' .$item['cati_id'].'">
				<div class="cati_h"><h1>'.$item['cati_namefull'].'</h1></div>';
            $shape['cati_namefull'] = $item['cati_namefull'];
            $shape['cati_urlfull'] = $page->pageUri;
			$res .= '<div class="cati_p"><a class="_imgview" href="/'.$this->imgcatipath.$item['cati_photofile'].'" title="'.$item['cati_namefull'].'"><img class="cati_pm" src="/'.$imgsrc.'" border="0" width="300" height="232" title="'.$item['cati_namefull'].'" alt="'.$item['cati_namefull'].'"></a><div class="cati_pss">';

			$i = 0;
			foreach (Obj_Gallery::getCopList('cati',$item['cati_id']) as $photo) {
				$phClass='cati_ps'.($i%2);
				$res .= '<div class="'.$phClass.'"><a class="_imgview" href="/'.'img/objph/'.$photo['cop_file'].'" title="'.$photo['cop_name'].'"><img class="cati_ps" src="/'.'img/objph/ps/'.$photo['cop_file'].'" border="0" width="145" height="130" title="'.$photo['cop_name'].'" alt="'.$photo['cop_name'].'"></a></div>';
				$i++;
			}
			$res .= '<div class="clearfix"></div></div></div>';

			if (!core::$inEdit) $GLOBALS['pageTemplate'] = 'second_cat';
			
			$res .= '<div class="cati_d"><div class="cati_di"></div><div class="cati_dt"><div class="cati_bg">';
			$res .= $item['cati_desc'];
			
			if ($item['cati_artcl']!='') $res .= '<div class="catiart"> Артикул: '.$item['cati_artcl'].'</div>';   
			
			$res .= '<div class="cati_ask"><a href="/'.$href_ask.'"></a></div>';
			
			if ($costStr!='') $res .= '<div class="cati_c"><span>Цена</span>: '.$costStr.'</div>'; 
			
			$res .= '</div></div></div><div class="clearfix"></div>';
			$res .= '</div></div>'; #<div class="ctlg_under"><a href="/'.$pageLinkUri.'" title="К списку">К списку</a></div>

			if ($item['cati_photofile']=='0.jpg') $item['cati_photofile'] = '';
			if ($editMode) $res .= '<script type="text/javascript" src="/akcms/js/v1/pg_ctlg_ed.js"></script><script type="text/javascript">var cati='.json_encode(array($item['cati_id']=>$item,'noadd'=>true)).';</script>';
            /*
            $query_where = sprintf('from cms_cat_gds where cati_sec_id=%d and cati_id<>%d',$page->page['section_id'],$item['cati_id']);
            $query = 'select * '.$query_where.'';

            $query_where = sprintf('from cms_cat_gds where cati_sec_id=%d and cati_id<>%d',$page->page['section_id'],$item['cati_id']);
            $query = 'select * '.$query_where.' ORDER BY random() LIMIT 5';
            $dataset = $sql->query_all($query);
            if ($dataset!==false && count($dataset)>0)
            {
                $res .= '<div class="cati_oth_h">ПОДОБНЫЕ ТОВАРЫ</div>';
                $res .= '
                <style>
                .subsecllst {
                    padding: 4px 0 0;
                }
                .subsecllstimt {
                    min-height: 100px;
                    width: 145px;
                    background-repeat: no-repeat;
                    float: left;
                    margin-right: 6px;
                    margin-bottom: 14px;
                }
                .subsecllstimtlast {
                    margin-right: 0px !important;
                }
                a.subsecllstimt_h {
                    padding-top: 135px;
                    padding-bottom: 2px;
                    display: block;
                    font-size: 12px;
                    text-align:cener;
                }
                .subsecllstimt_t {
                    font-size: 12px;
                }
                </style>
                <div class="subsecllst"><div class="subsecllstitms">';
                $i=0;
                foreach ($dataset as $item)
                {
                    $i++;
                    $href = $pageLinkUri.'g'.$item['cati_id'];
                    if ($item['cati_photofile']=='') $item['cati_photofile'] = '0.jpg';#
                    $styleimg = 'style="background-image: url(/img/cat/mpl/'.$item['cati_photofile'].')"';
                    $res .= '
                    <div class="subsecllstimt'.($i%5==0?' subsecllstimtlast':'').'" '.$styleimg.'><div'.($item['cati_show']=='f'?' class="imtdsbl"':'').'><a class="subsecllstimt_h" href="/'.$href.'">'.$item['cati_nameshort'].'</a></div></div>
                    ';
                }

                $res .= '<div class="clearfix"></div></div></div>';
            }
            */
			
		} elseif ((count($this->unitParam)==0) || (count($this->unitParam)==1?preg_match('/^\d{1,3}$/',$this->unitParam[0])==1:false))
		{
			$pgNum = 1;	if (count($this->unitParam)==1) $pgNum = $this->unitParam[0]>0?$this->unitParam[0]:1;
			$pgSize = 12;
			
			$query_where = sprintf('from cms_cat_gds where cati_sec_id=%d'.($editMode?'':' and cati_show'),$page->page['section_id']);			
			$query = 'select count(*) as totalrecords '.$query_where;
			$totalset = $sql->query_first_assoc($query); $countRecords = $totalset['totalrecords'];
			
			$query = 'select * '.$query_where.' ORDER BY cati_sort';		
			$query = sprintf ($query.' LIMIT %d OFFSET (%d-1)*%d;',
				$pgSize,
				$pgNum,
				$pgSize);
			$dataset = $sql->query_all($query);
			$pgNums = ceil($countRecords/$pgSize);

			if ($editMode) $res .= '<script type="text/javascript" src="/akcms/js/v1/pg_ctlg_ed.js"></script>'; //<script type="text/javascript">var cati='.json_encode($ctlg_items).'</script>;
		
			if ($countRecords==0 && $pgNum==1) return $res.'<div id="ctlg"><div class="ctlgitms"></div></div>';
			if ($pgNum<1 || $pgNum>$pgNums)
				throw new CmsException("page_not_found");
			
			$res .= '
			<style>
			#ctlg {
				/*background-color: #e3ccaf;*/
				padding: 4px 18px 18px;
			}
			.ctlgi {
				width: 181px;
				height: 282px;
				float: left;
				margin-right: 4px;
				possition: relative;
			}
			.ctlgi_last {
				margin-right: 0px !important;
			}
			.ctlgi_h {
				background-color: #f3f3f3/* #212121 */;
				border: 2px solid #dbd2d2;
			}
			.ctlgi_h a {
				background-repeat: no-repeat;
				background-position: left top;
				height: 34px;
				owerflow: hidden;
				padding: 186px 6px 6px;
				display: block;
				font-size: 13px;
				vertical-align: middle;
			}
			.ctlgi_h a:link,.ctlgi_h a:visited {
				color:  /* #ccc */;
				text-decoration: underline;
			}
			.ctlgi_h a:hover,.ctlgi_h a:active {
				color:  /* #fff */;
				text-decoration: none;
			}
			.ctlgi_ask_right {
				width: 58px;
				float: right;
			}
			.ctlgi_ask {
				color: #000;
				font-size: 13px;
				padding: 5px 2px 0 9px;
				line-height: 118%;
				background-repeat: no-repeat;
				background-position: left 9px;
				background-image: url(/img/t/1024/ask.png);
			}
			.ctlgi_ask a:link,.ctlgi_ask a:visited {
				color: #000;
				text-decoration: none;
			}
			.ctlgi_ask a:hover,.ctlgi_ask a:active {
				color: #000;
				text-decoration: underline;
			}
			.ctlgi_cost {
				color: #000;
				font-size: 17px;
				padding: 4px 2px;
			}
			.ctlgi_cost b{
				font-size: 18px;
				font-weight: bold;
			}
			</style>
			<div id="ctlg"><div class="ctlgitms">';

			$i = 0;
			if ($dataset!==false) foreach ($dataset as $item)
			{
				$i++;
				$href = $pageLinkUri.'g'.$item['cati_id'];				
				$href_ask = 'ask?'.http_build_query(array('url'=>'/'.$pageLinkUri.'g'.$item['cati_id'],'item'=>$item['cati_nameshort']));	
				if ($item['cati_photofile']=='') $item['cati_photofile'] = '0.jpg';#
				$costStr = $item['cati_bcost'].($item['cati_cost']==='0'?'':(' <b>'.$item['cati_cost'].'</b>р.'));
				$styleimg = 'style="background-image: url(/img/cat/pl/'.$item['cati_photofile'].')"';
				$res .= '
				<div class="ctlgi'.($i%4==0?' ctlgi_last':'').($item['cati_show']=='f'?' class="imtdsbl"':'').'">
					<div class="ctlgi_brdr"></div>
					<div class="ctlgi_h"><a href="/'.$href.'" title="'.$item['cati_namefull'].'" '.$styleimg.'>'.$item['cati_nameshort'].'</a></div>
					<div class="ctlgi_cost">'.$costStr.'</div>
				</div>
				'; #<div class="nwsb_d">'.DtTmToDtStr($item['sec_created']).'</div>
				//if ($i%4==0) $res .= '<div class="clearfix"></div>';
			}

			$res .= '<div class="clearfix"></div></div></div>';
			
			if ($pgNums>1)
			$res .= '<div class="pager">'.makePager($countRecords, $pgSize, $pgNum, '/'.$pageLinkUri.'{pg}/').'</div>';
		} else throw new CmsException("page_not_found");
		return $res;
	}
  
}

?>