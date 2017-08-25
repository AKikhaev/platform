<?php

class Pg_Media extends PgUnitAbstract {

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
		global $shape;
		$editMode = $this->hasRight() && core::$inEdit;
		
		if (!$editMode) $shape['jses'] .= '<script type="text/javascript" src="/akcms/js/v1/player/jwplayer.js"></script><script type="text/javascript" src="/akcms/js/v1/pg_media.js"></script>';

		return '';
	}
}
