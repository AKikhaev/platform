<?php

class SysUnit extends CmsPage {						// Страницы из DB
	public $pageUri;
	public $units = array('Obj_Gallery','SecStrEdit');

	function initAjx()
	{
		$ajaxes = array();
		foreach ($this->pageUnits as $pageUnit)
		{
			$ajaxes = array_merge($ajaxes, $pageUnit->initAjx());
		}
		return $ajaxes;
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
  
	function __construct(&$pageTemplate)
	{
		global $sql,$cfg;

		$this->pageUri = '_sys/';
	
		foreach ($this->units as $pgUnitClass)
		{
			$this->pageUnits[$pgUnitClass] = new $pgUnitClass(array());
		}
	}
	
	static function getContent() { throw new CmsException('page_not_found');	}
}