<?php

class Pg_Sects extends PgUnitAbstract {
	public $imgnewspath = 'img/pages/';
	
	function initAjx()
	{
		global $page;
		return array(
		'_secnewssve' => array(
			'func' => 'ajxSecNewssve',
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
  
  
	function ajxSecNewssve()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('sec_created'      , '/^(19|20)\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');
		$checkRule[] = array('sec_to_news'    , '/^(t|f)$/');
		$checkRule[] = array('section_id'     , '/^\\d{1,}$/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query = sprintf ('UPDATE cms_sections SET sec_created=%s,sec_to_news=%s WHERE section_id=%d;', 
				$sql->t($_POST['sec_created']),
				$sql->t($_POST['sec_to_news']),
				$_POST['section_id']
				);
			$res = $sql->command($query)>0?'t':'f';
			return json_encode($res);
		} 
		return json_encode(array('error'=>$checkResult));
	}
	  
	function render()
	{
		global $sql,$page;
		$res = '';
		$editMode = $this->hasRight();
		
		if ((count($this->unitParam)==0) || (count($this->unitParam)==1?preg_match('/^p\d{1,3}$/',$this->unitParam[0])==1:false))
		{
			$pgNum = 1;	if (count($this->unitParam)==1) $pgNum = substr($this->unitParam[0],1)>0?substr($this->unitParam[0],1):1;
			$pgSize = 20;
			
			$query_whr = $editMode?'':'sec_enabled and sec_to_news and';
			$query = 'select count(*) as totalrecords from cms_sections where '.$query_whr.' sec_content<>\'\'';
			$totalset = $sql->query_first_assoc($query); $countRecords = $totalset['totalrecords'];
			$query = sprintf ('select section_id,sec_url_full,sec_enabled,sec_to_news,sec_created,sec_namefull,sec_contshort,sec_content from cms_sections where '.$query_whr.' sec_content<>\'\' order by sec_created desc LIMIT %d OFFSET (%d-1)*%d;',
				$pgSize,
				$pgNum,
				$pgSize);
			$dataset = $sql->query_all($query);
			$pgNums = ceil($countRecords/$pgSize);
			if ($pgNum<1 || $pgNum>$pgNums)
				throw new CmsException("page_not_found");
			
			if ($editMode) ;
			$res .= '<div id="news">';
			$news_items = array();
			if ($dataset!==false) foreach ($dataset as $secItem)
			{
				$news_items[$secItem['section_id']] = array(
																'section_id'  =>$secItem['section_id'],
																'sec_namefull'=>$secItem['sec_namefull'],
																'sec_to_news' =>$secItem['sec_to_news'],
																'sec_created' =>$secItem['sec_created'],
															);
				$text = GetTruncText(strip_tags(str_replace('// <![CDATA[','<![CDATA[',$secItem['sec_contshort']!=''?$secItem['sec_contshort']:$secItem['sec_content'])),327);
				$res .= '<div class="newsitem'.(($secItem['sec_enabled']=='f'||$secItem['sec_to_news']=='f')?' imtdsbl':'').'" id="newsi'.$secItem['section_id'].'"><div class="newidate">'.DtTmToDtStr($secItem['sec_created']).'</div><div class="newihead"><a href="/'.$secItem['sec_url_full'].'" title="'.$secItem['sec_namefull'].'">'.$secItem['sec_namefull'].'</a></div><div class="newicnt">'.$text.'</div></div>';
			}
			$res .= '</div>';
			if ($pgNums>1)
			$res .= '<div class="pager">'.makePager($countRecords, $pgSize, $pgNum, '/'.$page->pageMainUri.'p{pg}/').'</div>';
			if ($editMode) $res .= '<script type="text/javascript" src="/akcms/js/v1/pg_secnews_ed.js"></script><script type="text/javascript">var newsi='.json_encode($news_items).';</script>';
		} else throw new CmsException("page_not_found");
		return $res;
	}
  
}

?>