<?php # ������ ����������� �������� ������� ��� ������������

class Pg_SubSecLst extends PgUnitAbstract {

	public function initAjx()
	{
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
		$editMode = $this->hasRight() && core::$inEdit;
		$pageLinkUri = '/'.($editMode?'_/':'').$page->pageMainUri;

		if ((count($this->unitParam)==0) || (count($this->unitParam)==1?preg_match('/^\d{1,3}$/',$this->unitParam[0])==1:false))
		{
			$pgNum = 1;	if (count($this->unitParam)==1) $pgNum = $this->unitParam[0]>0?$this->unitParam[0]:1;
			$pgSize = 16; 
			
			$query_where = sprintf('from cms_sections where section_id<>%1$s and sec_parent_id=%1$s'.($editMode?'':' AND sec_from<now() and sec_enabled and sec_showinmenu'),$page->page['section_id']);			
			$query = 'select count(*) as totalrecords '.$query_where;			
			$totalset = $sql->query_first_assoc($query); $countRecords = $totalset['totalrecords'];
			
			$res = '
			<style>
			.subsecllst {
				/*background-color: #e3ccaf;*/
				padding: 4px 18px 18px;
			}
			.subsecllstimt {
				min-height: 100px;
				width: 178px;
				background-repeat: no-repeat;
				float: left;
				margin-right: 5px;
				margin-bottom: 3px;
			}
			.subsecllstimtlast {
				margin-right: 0 !important;
			}
			a.subsecllstimt_h {
				padding-top: 160px;
				padding-left: 9px;
				padding-bottom: 6px;
				background: url(/img/t/imtarrw.gif) left 167px no-repeat;
				display: block;
				/* min-height: 67px; */
			}
			.subsecllstimt_t {
				padding-left: 9px;
				font-size: 12px;
			}
			
			.clearfix:after {
				clear: both;
			}
			.clearfix:before, .clearfix:after {
				content: " ";
				display: table;
			}
			.clearfix:before, .clearfix:after {
				content: " ";
				display: table;
			}
			</style>
			<div class="subsecllst"><div class="subsecllstitms">';

			$query = 'select section_id,sec_parent_id,sec_url_full,sec_created,sec_imgfile,sec_namefull,sec_contshort,sec_content,sec_enabled,sec_showinmenu '.$query_where.' order by sec_sort';
			$query = sprintf ($query.' LIMIT %d OFFSET (%d-1)*%d;',
				$pgSize,
				$pgNum,
				$pgSize);
			$dataset = $sql->query_all($query);
			$pgNums = ceil($countRecords/$pgSize);
			if ($countRecords==0 && $pgNum==1) return '';
			if ($pgNum<1 || $pgNum>$pgNums)
				throw new CmsException('page_not_found');
			
			
			$i = 0;
			if ($dataset!==false) foreach ($dataset as $item)
			{
				$i++;
				if ($item['sec_imgfile']=='') $item['sec_imgfile'] = '0.jpg';#
				if ($item['sec_contshort']!='') {
					$text = strip_tags(str_replace('// <![CDATA[','<![CDATA[',$item['sec_contshort']));
				} else {
					$text = GetTruncText(strip_tags(str_replace('// <![CDATA[','<![CDATA[',$item['sec_content'])),95);
				}
				#$text = GetTruncText(strip_tags(str_replace('// <![CDATA[','<![CDATA[',$item['sec_contshort']!=''?$item['sec_contshort']:$item['sec_content'])),95);
				$styleimg = $item['sec_imgfile']==''?'':'style="background-image: url(/img/pages/l/'.$item['sec_imgfile'].')"';
				$res .= '
				<div class="subsecllstimt'.($i%4==0?' subsecllstimtlast':'').'" '.$styleimg.'>
				<div'.($item['sec_enabled']=='f'||$item['sec_showinmenu']=='f'?' class="imtdsbl"':'').'><a class="subsecllstimt_h" href="/'.$item['sec_url_full'].'">'.$item['sec_namefull'].'</a></div>
				<div class="subsecllstimt_t">'.$text.'</div></div>
				'; #<div class="nwsb_d">'.DtTmToDtStr($item['sec_created']).'</div>
				if ($i%4==0) $res .= '<div class="clearfix"></div>';
			}  

			$res .= '<div class="clearfix"></div></div></div>';
			
			if ($pgNums>1)
			$res .= '<div class="pager">'.makePager($countRecords, $pgSize, $pgNum, $pageLinkUri.'{pg}').'</div>';
			
		} else throw new CmsException('page_not_found');
		return $res;
	}
}
