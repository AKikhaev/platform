<?php
// https://www.favicon-generator.org/
// https://realfavicongenerator.net/
class RootFolder extends CmsPage {

	public function initAjx()
	{
		$ajaxes = array();
		return $ajaxes;
	}
  
	public function _rigthList()
	{
		return array();
	}

	public function __construct()
	{
		global $pathlen;

		if ($pathlen==1) {
		    $filename = $GLOBALS['path'][0];
		    if (in_array($filename,['apple-touch-icon-152x152-precomposed.png','apple-touch-icon-120x120-precomposed.png','apple-touch-icon-76x76-precomposed.png']))
                $filename = 'apple-touch-icon-precomposed.png';
		    else if (in_array($filename,['apple-touch-icon-152x152.png','apple-touch-icon-120x120.png','apple-touch-icon-76x76.png']))
                $filename = 'apple-touch-icon.png';

		    $fullpath = 'assets/root/'.$filename;

		    if (file_exists($fullpath)) {
                header('Content-type: '.mime_content_type($fullpath));
                readfile($fullpath);
                die;
            } else throw new CmsException('page_not_found');
		} else throw new CmsException('page_not_found');

	}

    public function getContent() {}
}