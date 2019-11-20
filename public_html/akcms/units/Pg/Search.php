<?php # Поиск на сайте встроенными методами

class Pg_Search extends PgUnitAbstract {

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

	public function render()
	{
		global $sql,$shape,$page;
		$html = '';
		
		$itemString = '
		<div class="related">
			<div class="relatedpost">
				<a href="/{#ha_u_0#}">{#ha_l_0#}<img width="150" height="125" alt="" src="/img/pages/spl/{#ha_i_0#}"></a>
				<h3><a title="{#ha_t_0#}" rel="bookmark" href="/{#ha_u_0#}">{#ha_t_0#}</a></h3>
			</div>
		</div>
		';

		$findstring = trim(isset($_GET['qsrch'])?mb_strtoupper($_GET['qsrch']):'');
		if ($findstring==='' && isset($_GET['qsrch'])) {
			$findstring = mb_strtoupper(mb_convert_encoding($_GET['qsrch'],mb_internal_encoding(),'CP-1251'));
			$shape['qsrch'] = mb_convert_encoding($_GET['qsrch'],mb_internal_encoding(),'CP-1251');
		} else $shape['qsrch'] = isset($_GET['qsrch'])?$_GET['qsrch']:'';
		#$shape['qsrch'] = strip_tags($shape['qsrch']);
		if ($shape['qsrch'] === ' Поиск...') {
			unset($_GET['qsrch']);
			$shape['qsrch'] = '';
		}
		if (isset($_GET['qsrch'])) {

			$findstring = ' '.$findstring.' ';
			$findstring = preg_replace('/&[a-zA-Zа-яА-Я0-9]+;/u', '', $findstring);
			preg_match_all('/\s-([^\s]+(?=\s))/u',$findstring,$excludeArray);
			#$excludeStr = implode(' ',$excludeArray[1]); preg_match_all('/[a-zA-Zа-яА-Я0-9]+/u',$excludeStr,$excludeArray);
			$findstring = preg_replace('/\s-([^\s]+(?=\s))/u', '', $findstring);
			preg_match_all('/[a-zA-Zа-яА-Я0-9]+/u',$findstring,$findArray);

            $morphy = phpMorphyAdapter::getInstance();
			$fndarr = $morphy->getBaseForm($findArray[0],false); $bestrank = count($fndarr);
			#$excldarr = $morphy->getBaseForm($excludeArray[0],false);
			
			$pageSize = 10;
			$pageIndex = @(int)$_GET['pg'] ? (int)$_GET['pg'] :1;
			
			$query = sprintf ('begin;select * from __cms__search_v2(%s,%s,%d,%d);FETCH ALL IN totalset;', #
				$sql->pgf_wordarrays_text($fndarr),
				$sql->pgf_wordarrays_text(array()),
				$pageSize,
				$pageIndex);
			$totalset = $sql->query_first($query); $countRecords = $totalset['totalrecords']; $maxRank = count($fndarr); # $maxRank = $totalset['maxrank'];
			$dataset = $sql->query_all('FETCH ALL IN dataset;'); $sql->command('commit;');
			if ($countRecords>0 && $dataset!==false) {
				$startnum = $pageSize*($pageIndex-1)+1;
				// Непосредственный вывод результата
				$html .= '<ol start='.$startnum.'">';
				foreach ($dataset as $dataitem) {
					$atitle = GetTruncText(strip_tags($dataitem['title']),250);
					$title = GetTruncText(strip_tags($dataitem['title']),150);
					$snippet = mb_strlen($dataitem['desc'])>5?$dataitem['desc']:GetTruncText(strip_tags($dataitem['content']),300);
					$html .= '<li>';
						$shp = array();
						$isEng = 0;
                    $shp['ha_t_0'] = trim(str_ireplace('[eNg]','',$dataitem['title'],$isEng));
						$shp['ha_l_0'] = ($isEng>0?'<span class="lang-block">Eng</span>':'');
						$shp['ha_u_0'] = $dataitem['url'];
						$shp['ha_d_0'] = $snippet;
						$shp['ha_i_0'] = $dataitem['news_id'].'.jpg';
						$shp['ha_dt_0'] = VisualTheme::date2str(strtotime($dataitem['stamp']));
						$html .= shp::tmpl('_PgUnits/Pg_SubSecLst/2_s',$shp);

						//$html .= '<div class="srchr_r"><a target="_blank" href="/'.$dataitem['url'].'" title="'.$atitle.'">'.$title.'</a><div class="srchr_rs">'.$snippet.' </div></div>';
					$html .= '</li>';
				}
				$html .= '</ol>';
				$pages_count=ceil($countRecords/$pageSize); if (!$pages_count) $pages_count=1;
				if ($pages_count>1) {
					$html .= '<div class="pager">'.makePager($countRecords, $pageSize, $pageIndex, '/'.$page->pageMainUri.'?qsrch='.urlencode($shape['qsrch']).'&pg={pg}').'</div>';
				}
			} else $html .= '<p>Результатов по вашему запросу не найдено</p>';
		} else $html .= '<br/><p">Введите ваш запрос чтобы начать поиск</p>';
		return shp::tmpl('search', array('html'=>$html,'qsrch'=>htmlentities($shape['qsrch'],ENT_QUOTES,'UTF-8')));
	}
}
