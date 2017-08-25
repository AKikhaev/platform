<?php
class Pg_SectsHeads extends PgUnitAbstract {

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
		global $sql;

		$newsdata = array();

		$query = sprintf ('select sec_imgfile,sec_url_full,sec_namefull,sec_nameshort,sec_contshort,sec_content,sec_created from cms_sections where sec_enabled and sec_to_news order by sec_created desc limit 6;', 
		$sql->t($GLOBALS['pathstr']));
		$dataset = $sql->query_all($query);

		$i = 0;
		if ($dataset!==false) foreach ($dataset as $newsItem)
		{
			$i++;
			$text = GetTruncText(strip_tags(str_replace('// <![CDATA[','<![CDATA[',$newsItem['sec_contshort']!=''?$newsItem['sec_contshort']:$newsItem['sec_content'])),127);
			$styleimg = $newsItem['sec_imgfile']==''?'':'style="background-image: url(/img/pages/t/'.$newsItem['sec_imgfile'].')"';
			$res[$i] = '
			<div class="nwsb" '.$styleimg.'>
			<div class="nwsb_h">'.($newsItem['sec_url_full']!=''?' <a href="/'.$newsItem['sec_url_full'].'">'.$newsItem['sec_namefull'].'</a>':$newsItem['sec_namefull']).'</div>
			<div class="nwsb_s">'.$text.'</div></div>
			'; #<div class="nwsb_d">'.DtTmToDtStr($newsItem['sec_created']).'</div>
		}         
		$res = '
		<table width="99%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td width="49%">'.(isset($res[1])?$res[1]:' ').'</td>
				<td width="49%">'.(isset($res[2])?$res[2]:' ').'</td>
			</tr>
			<tr>
				<td>'.(isset($res[3])?$res[3]:' ').'</td>
				<td>'.(isset($res[4])?$res[4]:' ').'</td>
			</tr>
			<tr>
				<td>'.(isset($res[5])?$res[5]:' ').'</td>
				<td>'.(isset($res[6])?$res[6]:' ').'</td>
			</tr>
		</table>
		';
		#var_dump_(htmlspecialchars($res));
		return $res;
	}
}
