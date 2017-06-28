<?php
	$res = '<style>#mntxt,.main-content h1{/*display:none;font-size:20px;*/}</style><a style="font-size: 20px" href="/'.$pageLinkUri.'"></a> <h2>'.$glrItem['glr_name'].'</h2><div id="glry">
		<div class="glrind" id="glr'.$glrItem['id_glr'].'"></div>
		<div class="glridd">'.$glrItem['glr_desc'].'</div><div>';
	if ($dataset!==false) foreach ($glrPhotos as $photo) {
		$res .= 
		'<a rel="g|'.$photo['cgp_glr_id'].'|'.$photo['id_cgp'].'" title="'.$photo['cgp_name'].'" href="/img/gallery/'.$photo['cgp_file'].'" class="_imgview"><img class="glripp" width="160" height="137" border="0" alt="'.$photo['cgp_name'].'" src="/img/gallery/p/'.$photo['cgp_file'].'"></a>';
	};
	$res .= '</div><div class="clrbth"></div></div>';  
