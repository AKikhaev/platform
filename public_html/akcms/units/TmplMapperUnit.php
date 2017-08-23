<?php

class TmplMapperUnit extends CmsPage {

	function initAjx()
	{
		$ajaxes = array(
			'_mng' => array(
				'func' => 'ajxGenQR'),
			'_auth' => array(
				'func' => 'ajxAuth'),
		);
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
  
	function MngUnit(&$pageTemplate)
	{
	}

	function __construct(&$pageTemplate)
	{
		global $shape;

		if ($GLOBALS['path'][0]=='_tmpl') {
            core::$renderPage = true;
            $this->title = 'Маппер';
            $pageTemplate = 'tmpl_mapper';
            echo file_get_contents('pages/index'.'.shtm',true);
            echo file_get_contents('pages/tmpl_mapper.shtm',true);
            die();
		} else throw new CmsException('page_not_found');

	}

    static function getContent() {}
}