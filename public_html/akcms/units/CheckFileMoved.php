<?php
// https://www.favicon-generator.org/
// https://realfavicongenerator.net/
class CheckFileMoved extends CmsPage {

	public function initAjx()
	{
		$ajaxes = array();
		return $ajaxes;
	}
  
	public function _rigthList()
	{
		return array();
	}

    /**
     * CheckFileMoved constructor.
     * @param $pageTemplate
     * @throws CmsException
     */
	public function __construct(&$pageTemplate)
	{
		global $pathlen,$path,$pathstr;

        $filepath = mb_trim($pathstr,'\/').'.7z';
		if ($pathlen>1 && $path[0]=='s' && file_exists($filepath)) {
            header('Location: /'.$filepath,true,301);
		    die;
        } else throw new CmsException('page_not_found');

	}

    public static function getContent() {}
}