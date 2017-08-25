<?php
	$res .= '<div id="glrs">
	<style>
	.glritm {
		width: 207px;
		min-height: 184px;
		float: left;
		margin-right: 0;
		margin-bottom: 10px;
		background: url(/img/t/phfrm.jpg) top left no-repeat;
	}
	.glritm_i {
		background-repeat: no-repeat;
		background-position: 22px 23px;
	}
	.glritmlast {
		margin-right: 0 !important;
	}
	a.glritm_h {
		padding-top: 170px;
		padding-bottom: 2px;
		display: block;
		font-size: 12px;
		text-align:center;
		/* min-height: 67px; */
	}
	.glritm_t {
		font-size: 12px;
	}
	</style>';
$glrs_items = array(); $glrnum = 0;
	if ($glrItms!==false) foreach ($glrItms as $glrItem)
	{
		$glrs_items[$glrItem['id_glr']] = $glrItem; $glrnum++;
		$res .= '
		<div class="glritm">
			<div class="glritm_i" style="background-image: url(/'.$this->imgglrpath.'t/'.$glrItem['glr_file'].')">
				<a href="/'.$pageLinkUri.'g'.$glrItem['id_glr'].'/" title="'.$glrItem['glr_name'].'" class="glritm_h">'.$glrItem['glr_name'].'</a>
			</div>
			<div class="glritm_t">'.$glrItem['glr_desc'].'</div>
		</div>
		<!--
		<div class="glritem'.($glrItem['glr_enabled']=='f'?' imtdsbl':'').'" id="glri'.$glrItem['id_glr'].'">
				<div class="glrip"><a href="/'.$pageLinkUri.'g'.$glrItem['id_glr'].'/" title="'.$glrItem['glr_name'].'"><img class="glripi" src="/'.$this->imgglrpath.'t/'.$glrItem['glr_file'].'" alt="'.$glrItem['glr_name'].'" width="228" height="150"></a></div>
				<div class="glrin"><a href="/'.$pageLinkUri.'g'.$glrItem['id_glr'].'/" title="'.$glrItem['glr_name'].'">'.$glrItem['glr_name'].'</a></div>
				<div class="glrid">'.$glrItem['glr_desc'].'</div>
			</div>
		-->';
		if ($glrnum%3==0) $res .= '<div class="clrbth"></div>';
	}
	$res .= '<div class="clrbth"></div></div>';