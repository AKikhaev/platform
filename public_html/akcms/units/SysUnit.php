<?php

class SysUnit extends CmsPage {						// Страницы из DB
	public $pageUri;
	public $units = array('ObjGallery','FileManager');
	private $pageUnits = [];

	public function initAjx()
	{
		$ajaxes = array();
        foreach ($this->pageUnits as $pageUnit)
        {
            foreach ($pageUnit->initAjx() as $k=>$v)
                $ajaxes[$k] = $v;
        }
		return $ajaxes;
	}
  
	public function _rigthList()
	{
		return array(
		);
	}

	public function __construct(&$pageTemplate)
	{
		global $pathlen,$path;
        if ($pathlen==2 && $path[0] === '_sys') {
            $this->pageUri = '_sys/';
            core::$renderPage = true;
            foreach ($this->units as $pgUnitClass)
            {
                $this->pageUnits[$pgUnitClass] = new $pgUnitClass(array());
            }
        } else throw new CmsException('page_not_found');
	
	}
	
	public static function getContent() { throw new CmsException('page_not_found'); }
}