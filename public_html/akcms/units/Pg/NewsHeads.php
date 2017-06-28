<?php
class Pg_NewsHeads extends PgUnitAbstract {

	function initAjx()
	{
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
		global $sql;

		$newsdata = array();

		$query = sprintf ('select * from cms_news where news_enabled order by news_date desc limit 6;', 
		$sql->t($GLOBALS['pathstr']));
		$dataset = $sql->query_all($query);

		$i = 0;
		if ($dataset!==false) foreach ($dataset as $newsItem)
		{
			$i++;
			$styleimg = $newsItem['news_image']==''?'':'style="background-image: url(/img/news/t/'.$newsItem['news_image'].')"';
			$res[$i] = '
			<div class="nwsb" '.$styleimg.'>
			<div class="nwsb_d">'.DtTmToDtStr($newsItem['news_date']).'</div>
			<div class="nwsb_h">'.($newsItem['news_detaillink']=='t'?' <a href="/news/n'.$newsItem['news_id'].'/">'.$newsItem['news_head'].'</a>':$newsItem['news_head']).'</div>
			<div class="nwsb_s">'.GetTruncText(strip_tags(str_replace('// <![CDATA[','<![CDATA[',$newsItem['news_short'])),100).'</div></div>
			';
		}         
		return '
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td width="50%">'.(isset($res[1])?$res[1]:'').'</td>
				<td width="50%">'.(isset($res[2])?$res[2]:'').'</td>
			</tr>
			<tr>
				<td>'.(isset($res[3])?$res[3]:'').'</td>
				<td>'.(isset($res[4])?$res[4]:'').'</td>
			</tr>
			<tr>
				<td>'.(isset($res[5])?$res[5]:'').'</td>
				<td>'.(isset($res[6])?$res[6]:'').'</td>
			</tr>
		</table>
		';
	}
}

?>