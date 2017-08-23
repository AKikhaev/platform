<?php

class SysUnit extends CmsPage {						// Страницы из DB
	public $pageUri;
	public $units = array('ObjGallery','SecStrEdit');

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
		global $pathlen,$path;
        if ($pathlen==2 && $path[0]=='_sys') {
            $this->pageUri = '_sys/';
            core::$renderPage = true;
            foreach ($this->units as $pgUnitClass)
            {
                $this->pageUnits[$pgUnitClass] = new $pgUnitClass(array());
            }
        } else throw new CmsException('page_not_found');
	
	}
	
	static function getContent() { throw new CmsException('page_not_found'); }
}