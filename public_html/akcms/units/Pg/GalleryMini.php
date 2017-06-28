<?php

class Pg_GalleryMini extends Pg_Gallery {
	public $Injected = true;
	
	function initAjx()
	{
		global $page;
		$ajxs = parent::initAjx();
		$ajxs[$page->pageUri.'_glrsec'] = array(
			'func' => 'ajxGlrSec',
			'object' => $this);
		return $ajxs;
	}

	/*
	public static function buildLevelSiteMap(&$putInto,$parentId,$parentUrlFull,$showHidden = false) {
		parent::buildLevelSiteMap(&$putInto,$parentId,$parentUrlFull,$showHidden,true);
	}
	*/
	
	function ajxGlrSec()
	{
		global $sql,$page;
		$checkRule = array();
		//$checkRule[] = array('glrs', '');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query = sprintf ('SELECT __cms_gallery_sec__assign(%d,%s);', 
				$page->page['section_id'],
				$sql->pgf_array_int(isset($_POST['glrs'])?$_POST['glrs']:array())
			);
			$db_res = $sql->query_first_row($query);
			return json_encode($db_res[0]=='t'?'t':'f');
		}
		return json_encode(array('error'=>$checkResult));   
	}

	function render()
	{
		global $page,$shape;
		$editMode = $this->hasRight() && core::$inEdit;

		$galleries = $this->getSecGalleries();
		$res = '
		<style scoped="scoped">
		.glrmini_nm {
			font-size: 16px;
			font-weight: bold;
			padding-bottom: 18px;
			width: 695px;
		}
		.glrmini_nm a,.glrmini_nm a:link,.glrmini_nm a:visited {
			text-decoration: none;
		}
		.glrmini_nm a:hover,.glrmini_nm a:active {
			text-decoration: underline;
		}
		.glrmini_dt {
			float: right;
			font-size: 12px;
			padding-left: 15px;
			text-align: left;
		}
		.glrmini_p {
			padding-bottom: 24px;
		}
		.glrmini_pi {
			width: 173px;
			height: 173px;
			float: left;
			margin: 1px 1px 0 0;
		}
		.glrmini_pi_othshow{
			font-size: 14px;
			float: right;
			margin-right: 69px;
			display: block;
		}
		</style>
		<div id="glrmini" class="glrmini">';
		$glrs = array();
		foreach($galleries['g'] as $gallery) {
			$res .= '<div class="glrmini_nm">';
			$glrs[] = $gallery['id_glr'];
			$dt = strtotime($gallery['glr_created']);
			$res .= '<div class="glrmini_dt"><img src="/img/t/artcl_dt.gif" width=12 height=12 alt="" /> '.date('y.m.d',$dt).'</div><a href="/mediateka/fotogalereya/g'.$gallery['id_glr'].'/">';
			$res .= $gallery['glr_name'].'</a></div><div class="glrmini_p">';
			$i=0;
			if ($galleries['p'][$gallery['id_glr']]!=false) {
				$res .= '<div class="glrmini_p'.(count($galleries['p'][$gallery['id_glr']])>8?' glrmini_pi_alot':'').'">';
				foreach ($galleries['p'][$gallery['id_glr']] as $photo) {
					$res .= '<div class="glrmini_pi'.(++$i>8?' hidden glrmini_pi_oth':'').'"><a  class="_imgview" rel="g|'.$gallery['id_glr'].'|'.$photo['id_cgp'].'" title="'.($photo['cgp_name']!=''?$photo['cgp_name']:$gallery['glr_name']).'" href="/img/gallery/o/'.$photo['cgp_file'].'"><img width="173" height="173" border="0" alt="'.$photo['cgp_name'].'" src="/img/gallery/a/'.$photo['cgp_file'].'"></a></div>';
				}
				$res .= '<div class="clearfix"></div></div>';
			}
		}
		$res .= '</div>';
		if ($editMode) {
			$shape['jses'] .= '
			<link rel="stylesheet" href="/js/multiselect/selectfilter.css">
			<script type="text/javascript">var glrs_mini='.json_encode($glrs).';</script><script type="text/javascript" src="/js/pg_glr_mini_sel.js"></script>
			<script type="text/javascript" src="/js/multiselect/multipleSelectFilter.js"></script>
			';
		}
		return $res;
	}	
}

