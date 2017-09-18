<?php # Список статей(ой, новостей) некоторого раздела для главной

class Pg_Main_News extends PgUnitAbstract {

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
		global $sql;
		$section_id = 264;

		$res = '
		<style scoped="scoped">
		.nwslsthdr h2 {
			font-size: 30px;
			font-weight: normal;
			margin-bottom: 20px;
		}
		.nwslstlnk {
			float: right;
			margin-top: 13px;
		}
		.nwslstimt {
			min-height: 100px;
			width: 178px;
			background-repeat: no-repeat;
			float: left;
			margin-right: 17px;
			margin-bottom: 3px;
		}
		.nwslstimtlst {
			margin-right: 0 !important;
		}
		.nwslstimt_h {
			padding-top: 160px;
			padding-left: 9px;
			padding-bottom: 6px;
			background: url(/img/t/imtarrw.gif) left 167px no-repeat;
			display: block;
			/* min-height: 67px; */
		}
		.nwslstimt_t {
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
		<div class="nwslst"><div class="nwslsthdr"><div class="nwslstlnk"><img src="/img/t/allobjects.gif" alt="" width=12 height=12 /> <a href="/novosti/">Все новости</a></div><h2>Новости</h2></div><div class="nwslstitms">';

		$query = sprintf ('
			WITH RECURSIVE ierarh ( section_id,sec_parent_id,sec_url_full,sec_created,sec_imgfile,sec_namefull,sec_contshort,sec_content ) AS (
				select s1.section_id,s1.sec_parent_id,s1.sec_url_full,s1.sec_created,s1.sec_imgfile,s1.sec_namefull,s1.sec_contshort,s1.sec_content from cms_sections s1 where section_id = %1$s
				UNION
				select s2.section_id,s2.sec_parent_id,s2.sec_url_full,s2.sec_created,s2.sec_imgfile,s2.sec_namefull,s2.sec_contshort,s2.sec_content from cms_sections s2 inner join ierarh on (ierarh.section_id=s2.sec_parent_id)
			)
			select * from ierarh where section_id<>%1$s order by sec_created desc limit 4		
		', 
		$section_id);
		$dataset = $sql->query_all($query);
		
		$i = 0;
		if ($dataset!==false) foreach ($dataset as $item)
		{
			$i++;
			if ($item['sec_imgfile'] === '') $item['sec_imgfile'] = '0.jpg';#
			if ($item['sec_contshort'] !== '') {
				$text = strip_tags(str_replace('// <![CDATA[','<![CDATA[',$item['sec_contshort']));
			} else {
				$text = GetTruncText(strip_tags(str_replace('// <![CDATA[','<![CDATA[',$item['sec_content'])),95);
			}
			#$text = GetTruncText(strip_tags(str_replace('// <![CDATA[','<![CDATA[',$item['sec_contshort']!=''?$item['sec_contshort']:$item['sec_content'])),95);
			$styleimg = $item['sec_imgfile'] === ''?'':'style="background-image: url(/img/pages/l/'.$item['sec_imgfile'].')"';
			$res .= '
			<div class="nwslstimt'.($i%4==0?' nwslstimtlst':'').'" '.$styleimg.'>
			<a class="nwslstimt_h" href="/'.$item['sec_url_full'].'">'.$item['sec_namefull'].'</a>
			<div class="nwslstimt_t">'.$text.'</div></div>
			'; #<div class="nwsb_d">'.DtTmToDtStr($item['sec_created']).'</div>
		}  

		$res .= '<div class="clearfix"></div></div></div>';
		return $res;
	}
}
