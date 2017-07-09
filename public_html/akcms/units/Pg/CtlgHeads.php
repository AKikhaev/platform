<?php

class Pg_CtlgHeads extends PgUnitAbstract {
	public $imgnewspath = 'img/news/';

	function initAjx()
	{
		global $page;
		return array(
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

	function render()
	{
		global $sql,$page;
		$res = '';
		$editMode = $this->hasRight();
			$query = sprintf ('select a.cati_id,a.cati_photofile,a.cati_show,a.cati_namefull,a.cati_nameshort,a.cati_cost,a.cati_costold,b.sec_url_full from cms_cat_gds a inner join cms_sections b on (a.cati_sec_id=b.section_id) inner join cms_cat_rcmnd c on (a.cati_id=c.rcmnd_cati_id) where a.cati_show and c.rcmnd_enabled order by rcmnd_order limit 8');
			$dataset = $sql->query_all($query);
			$res .= '<div id="ctlg">';
			$ctlg_items = array();
			if ($dataset!==false) foreach ($dataset as $ctlgItem)
			{
				$href = $ctlgItem['sec_url_full'].'g'.$ctlgItem['cati_id'];
				$imgsrc = $ctlgItem['cati_photofile']!=''?'/img/catimgs/pl/'.$ctlgItem['cati_photofile']:'/img/e.gif';
				$res .= '<div class="ctlgiteml'.($ctlgItem['cati_show']=='f'?' imtdsbl':'').'" id="ctlgi'.$ctlgItem['cati_id'].'">';
				$res .= '<div class="ctlgiteml_p"><a href="/'.$href.'" title="'.$ctlgItem['cati_namefull'].'"><img src="'.$imgsrc.'" border="0" width="161" height="141" title="'.$ctlgItem['cati_namefull'].'" alt="'.$ctlgItem['cati_namefull'].'"></a></div>';
				$res .= '<div class="ctlgiteml_t"><a href="/'.$href.'" title="'.$ctlgItem['cati_namefull'].'">'.$ctlgItem['cati_nameshort'].'</a></div>';
				//$ctlgItem['cati_cost'] = 49999; $ctlgItem['cati_costold'] = $ctlgItem['cati_cost']*1.09;
				$cost = number_format($ctlgItem['cati_cost'], 2, '.', ' ');
				$costold = number_format($ctlgItem['cati_costold'], 2, '.', ' ');
				$res .= '<div class="ctlgiteml_c">';
				if ($cost>0) $res .= '<div class="ctlgiteml_cc">'.$cost.' руб.</div>';
				if ($costold>0) $res .= '<div class="ctlgiteml_co">'.$costold.' руб.</div>';
				$res .= '</div>';

				$res .= '</div>';
                            //$res .= '<pre>'.print_r($ctlgItem,true).'</pre>';
                                $ctlg_items[$ctlgItem['cati_id']] = $ctlgItem;
				//$res .= '<div class="newsitem'.($ctlgItem['news_enabled']=='f'?' imtdsbl':'').'" id="newsi'.$ctlgItem['news_id'].'"><div class="newidate">'.DtTmToDtStr($newsItem['news_date']).'</div><div class="newihead">'.$newsItem['news_head'].'</div><div class="newicnt">'.$newsItem['news_short'].($newsItem['news_detaillink']=='t'?' <a href="/'.$page->pageMainUri.'n'.$newsItem['news_id'].'/">Подробнее...</a>':'').'</div></div>';
			}
			$res .= '<div class="clrbth"></div></div>';
			//if ($editMode) $res .= '<script type="text/javascript" src="/akcms/js/v1/pg_ctlg_ed.js"></script><script type="text/javascript">var ctlgis='.json_encode($ctlg_items).';</script>';
		return $res;
	}
  
}

?>