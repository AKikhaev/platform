<?php

class Pg_CtlgPrce extends PgUnitAbstract {
	public function initAjx()
	{
		global $page;
		return array(
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
  
	public function render()
	{
		global $sql,$page;
		$html = '';
		$editMode = $this->hasRight();
		if ((count($this->params)==0) || (count($this->params)==1?preg_match('/^\d{1,3}$/',$this->params[0])==1:false))
		{
			$pgNum = 1;	if (count($this->params)==1) $pgNum = $this->params[0]>0?$this->params[0]:1;
			$pgSize = 40; 
			
			$sqlb = '
			FROM 
			  cms_cat_gds g INNER JOIN cms_sections s ON (g.cati_sec_id=s.section_id) LEFT JOIN cms_sections s1 ON (s.sec_parent_id=s1.section_id and s.sec_parent_id<>7) 
			WHERE g.cati_show		
			';
			
			$query = 'SELECT count(*) as totalrecords '.$sqlb;
			$totalset = $sql->query_first_assoc($query); $countRecords = $totalset['totalrecords'];
			$query = sprintf ('SELECT
			  s.sec_url_full,
			  s.sec_namefull,
			  g.cati_id,
			  g.cati_namefull,
			  g.cati_photofile,
			  g.cati_desc,
			  g.cati_cost,
			  g.cati_costold,
			  g.cati_offerprcnt,
			  g.cati_artcl,
			  g.cati_descshort
			  '.$sqlb.' ORDER by s1.sec_sort*1000000+s.sec_parent_id,sec_url_full,g.cati_namefull, g.cati_sort LIMIT %d OFFSET (%d-1)*%d;',
				$pgSize,
				$pgNum,
				$pgSize);
			$dataset = $sql->query_all($query);
			$pgNums = ceil($countRecords/$pgSize);

			if ($pgNum<1 || $pgNum>$pgNums)
				throw new CmsException('page_not_found');
			
			$html .= '<div id="gstbk">';
			$gb_items = array();
			if ($dataset!==false) {
				$html .= '<table class="prce"><tr><th><div class="prcenameh"><div class="prcenameh_">Наименование товара</div></div></th><th><div class="prcecosth"><div class="prcecosth_">Стоимость</div></div></th></tr>';
				$sec_namefull = '';
				foreach ($dataset as $di)
				{//sec_url_full
					if ($sec_namefull!=$di['sec_namefull']) {
						$sec_namefull = $di['sec_namefull'];
						$html .= '<tr><td colspan=2><div class="prcesec">'.$di['sec_namefull'].'</div></td>';						
					}
					$html .= '<tr><td><div class="prcenameb"><div class="prcenameb_"><div class="prcenamebg_">'.$di['cati_namefull'].'</div></div></div></td><td><div class="prcecostb"><div class="prcecostb_"><div class="prcecostbg_">'.($di['cati_cost']==0?'':$di['cati_cost']).'</div></div></div></td>';
					// $gb_items[$gbItem['gb_id']] = $gbItem;
					// $html .= '<div class="gbitem'.($gbItem['gb_enabled']=='f'?' imtdsbl':'').'" id="gbi'.$gbItem['gb_id'].'"><div class="gbiname">'.$gbItem['gb_name'].'</div><div class="gbidate">'.DtTmToDtStr($gbItem['gb_date']).'</div><div class="gbimsg">'.str_replace("\n",'<br/>',$gbItem['gb_message']).'</div></div>';
				}
				$html .= '</table>';
			}
			$html .= '</div>';
			if ($pgNums>1)
			$html .= '<div class="pager">'.makePager($countRecords, $pgSize, $pgNum, '/'.$page->pageMainUri.'{pg}/').'</div>';

		} else throw new CmsException('page_not_found');
		return $html;
	}
  
}

