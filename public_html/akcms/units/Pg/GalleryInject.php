<?php # Галерея раздела

class Pg_GalleryInject extends Pg_Gallery {
	public $Injected = true;
	
	public static function buildLevelSiteMap(&$putInto,$parentId,$parentUrlFull,$showHidden = false) {
		parent::buildLevelSiteMap(&$putInto,$parentId,$parentUrlFull,$showHidden,true);
	}	
}

