<?php
abstract class CmsPage extends AclProcessor { /* page */
    public $pageUri;
    public $inEditCan = false;
    public $page = [];
    protected $pagePath = array();		// Крошки
    protected $pagePath_ids = array();	// Крошки_ids
    protected $pageMenu = array();		// Меню страницы
    protected $pageAllMenu = array();		// Полное меню
    protected $title;
    protected $cacheWholePage = true;
    public function canCache() { return $this->cacheWholePage;}
    public function noCache() { $this->cacheWholePage=false; }
    public function __construct(&$pageTemplate) {}
    public function getTitle() {return $this->title;}
    public function initAjx() {return [];}

    /** Сортировка потомков
     * @param $howchild
     * 1 - по созданию
     * 2 - с новых
     * 3 - со старых
     * @return bool|string
     */
    public function _howchildToOrder($howchild){
        switch ($howchild) {
            case 1:	return 'sec_sort';
            case 2:	return 'sec_from DESC';
            case 3:	return 'sec_from';
        }
        return false;
    }

    protected function _getMenuItemByUrl($urlFull,$showHidden = false,$prefix=false)
    {
        global $sql;
        $query = sprintf ('SELECT '.MENU_FIELDS.' from cms_sections where sec_url_full=%s '.($showHidden?'':'and sec_enabled and sec_showinmenu and now()>sec_from').';',
            $sql->t($urlFull));
        $dataset = $sql->query_first_assoc($query);
        if ($prefix!==false && $dataset!==false) $dataset['sec_url_full'] = $prefix.$dataset['sec_url_full'];
        return $dataset;
    }
    protected function _getMenuItem($Id,$showHidden = false,$prefix=false)
    {
        global $sql;
        $query = sprintf ('SELECT '.MENU_FIELDS.' from cms_sections where section_id=%d '.($showHidden?'':'and sec_enabled and sec_showinmenu and now()>sec_from').';',
            $Id);
        $dataset = $sql->query_first_assoc($query);
        if ($prefix!==false && $dataset!==false) $dataset['sec_url_full'] = $prefix.$dataset['sec_url_full'];
        return $dataset;
    }
    protected function _getMenuItems($parentId,$howchild,$showHidden = false,$prefix=false)
    {
        global $sql;
        $order = $this->_howchildToOrder($howchild);
        if (core::$inEdit && $order==false) {
            $order = 'sec_enabled,sec_showinmenu,sec_from DESC';
            $wherespec = ($showHidden?' ':'and sec_enabled and sec_showinmenu and now()>sec_from').' and not sec_system';
            //'and (not sec_enabled or not sec_showinmenu or now()<=sec_from)'
        }
        else {
            if ($order==false) return false;
            $wherespec = ($showHidden?'':'and sec_enabled and sec_showinmenu and now()>sec_from').' and not sec_system';
        }
        $fields = MENU_FIELDS;
        if ($parentId<0) {
            $query = sprintf ('SELECT '.$fields.' from cms_sections inner join cms_menu_items ON (mnui_sec_id=section_id) where mnui_mnu_id=%d '.$wherespec.' order by mnui_sort,'.$order,
                -$parentId);
        } else
            $query = sprintf ('SELECT '.$fields.' from cms_sections where sec_parent_id=%d '.$wherespec.' order by '.$order,
                $parentId);
        $dataset = $sql->query_all($query);
        if ($prefix!==false && $dataset!==false) foreach ($dataset as $data) $data['sec_url_full'] = $prefix.$data['sec_url_full'];
        return $dataset;
    }

    protected function _getAllMenuItems(&$putInto,$parentId, $howchild=1,
                                        $showHidden = false, $prefix = false, $markSelected = false, $markCurrent = false, $expByPath = false, $deep = 999) {
        global $sql;
        if ($deep===0) return false;
        $order = $this->_howchildToOrder($howchild); if ($order===false && !$showHidden) return false;
        $mnulist = $this->_getMenuItems($parentId,$howchild,$showHidden,$prefix);
        if ($mnulist!==false) {
            $putInto = $mnulist;
            foreach ($putInto as &$menuAllItem) {
                if (isset($this->pagePath_ids[$menuAllItem['section_id']])) {
                    if ($markSelected && !isset($this->pagePath_ids[$menuAllItem['section_id']]['_current'])) $menuAllItem['_selected'] = true;
                    if ($markCurrent && isset($this->pagePath_ids[$menuAllItem['section_id']]['_current'])) $menuAllItem['_current'] = true;
                }
                $menuAllItem['_children'] = array();
                if (!$showHidden && $menuAllItem['sec_openfirst'] === 't') // Подменяем url openfirst раздела первым подразделом
                {
                    $query = sprintf ('select sec_url_full from cms_sections where sec_parent_id=%d and sec_enabled and now()>sec_from order by sec_sort limit 1;',
                        $menuAllItem['section_id']);
                    $dataset = $sql->query_first_assoc($query);
                    if ($dataset!==false) {
                        $menuAllItem['sec_url_full'] = ($prefix!==false?$prefix:'').$dataset['sec_url_full'];
                    }
                }
                if (!$expByPath || ($expByPath && isset($this->pagePath_ids[$menuAllItem['section_id']]))) // Раскрытие по крошкам
                    $this->_getAllMenuItems($menuAllItem['_children'],$menuAllItem['section_id'],$menuAllItem['sec_howchild'],
                        $showHidden,$prefix,$markSelected, $markCurrent,$expByPath, $deep-1);
                if ($menuAllItem['sec_units'] !== '')
                    foreach (explode(',',$menuAllItem['sec_units']) as $pgUnitClass) if (isset($cfg['pgunits'][$pgUnitClass]))
                        call_user_func_array(array($pgUnitClass,'buildLevelSiteMap'),array(&$menuAllItem['_children'],$menuAllItem['section_id'],$menuAllItem['sec_url_full']));
                if (count($menuAllItem['_children'])==0) unset($menuAllItem['_children']);
            }
        }
        return true;
    }

    /* Вся структура меню для карты и админки*/
    public function getAllMenu($showHidden = false)
    {
        if (count($this->pageAllMenu) != 0) return $this->pageAllMenu;
        $showHidden = $this->hasRight() && $showHidden;
        $this->_getAllMenuItems($this->pageAllMenu,0,1,$showHidden);
        return $this->pageAllMenu;
    }

    /* структура, начиная от url */
    public function getMenuSubByPath(&$putInto, $menuPath='', $markSelected = false, $markCurrent = false, $expByPath = false, $deep = 999, $howchild = 1) {
        if ($menuPath==='') $parentId = 0;
        else {
            $parentSec = $this->_getMenuItemByUrl($menuPath,true);
            $parentId = $parentSec['section_id'];
            $howchild = $parentSec['sec_howchild'];
        }
        $this->_getAllMenuItems($putInto,$parentId,$howchild,false,false,$markSelected,$markCurrent,$expByPath,$deep);
    }

    /* структура, начиная от SpecId */
    public function getMenuSubBySpecId(&$putInto, $menuSpecId, $markSelected = false, $markCurrent = false, $expByPath = false, $deep = 999, $howchild = 1) {
        $this->_getAllMenuItems($putInto,$menuSpecId,$howchild,false,false,$markSelected,$markCurrent,$expByPath,$deep);
    }

    /* полная структура, хорошо для админки*/
    public function getMenu($showHidden)
    {
        if (count($this->pageMenu) != 0) return $this->pageMenu;
        $this->getBreadcrumbs();
        $howchild = 1;
        $putInto = &$this->pageMenu;
        $lastPathItem = false;
        foreach ($this->pagePath as $pathItem)
        {
            $mnulist = $this->_getMenuItems($pathItem['sec_parent_id'],$howchild,$showHidden);
            if ($mnulist!==false) {
                $putInto = $mnulist;
                foreach ($putInto as &$mnuitem) {
                    $mnuitem['_p_hc'] = $howchild;
                    if ($mnuitem['section_id']==$pathItem['section_id'])
                    {
                        if ($mnuitem['section_id']==$this->page['section_id']) $mnuitem['_current'] = true;
                        $howchild = $mnuitem['sec_howchild'];
                        $mnuitem['_selected'] = true;
                        $mnuitem['_children'] = array();
                        $putInto = &$mnuitem['_children'];
                    }
                }
                unset($mnuitem);
            } else break;
            $lastPathItem = $pathItem;
        }
        if ($lastPathItem !== false)
        {
            $mnulist = $this->_getMenuItems($lastPathItem['section_id'],$howchild,$showHidden);
            if ($mnulist!==false) {
                $putInto = $mnulist; $this->page['_children'] = &$mnulist;
                foreach ($putInto as &$mnuitem1) $mnuitem1['_p_hc'] = $howchild;
                unset($mnuitem);
            }
        }
        if (core::$inEdit) {
            $root = $this->_getMenuItem(1);
            if ($root !== false) {
				$root['sec_url_full'] = '';
				array_unshift($this->pageMenu,$root);
			}
        }
        return $this->pageMenu;
    }

    protected function _buildPageSections($menuItems) /* Необходимо для режима редактирования - массив разделов для ajax */
    {
        foreach($menuItems as $v)
        {
            if (isset($v['_children'])?$v['_children']!=false:false) $this->_buildPageSections($v['_children']);
            if (isset($v['_children'])) unset($v['_children']);
            $this->pageSections[$v['section_id']] = $v;
        }
    }

    #Хлебные крошки
    public function &getBreadcrumbs($prefix=false)
    {
        global $sql;
        if (count($this->pagePath) !== 0) return $this->pagePath;

        $currNode = [
            'section_id'    =>$this->page['section_id'],
            'sec_parent_id' =>$this->page['sec_parent_id'],
            'sec_url_full'  =>$this->page['sec_url_full'],
            'sec_nameshort' =>$this->page['sec_nameshort'],
            'sec_namefull' =>$this->page['sec_namefull'],
            'sec_showinmenu'=>$this->page['sec_showinmenu'],
            'sec_enabled'   =>$this->page['sec_enabled'],
            'sec_hidden'	=>$this->page['sec_enabled'] === 'f' || $this->page['sec_showinmenu'] === 'f'?'t':'f',
            '_current' => true
        ];

        $this->pagePath[] = $currNode;
        $this->pagePath_ids[$currNode['section_id']] = $currNode;
        while ($currNode['sec_parent_id']!=0)
        {
            $query = sprintf ('select section_id,sec_parent_id,sec_url_full,sec_nameshort,sec_namefull,sec_showinmenu,sec_enabled, not sec_enabled or not sec_showinmenu as sec_hidden from cms_sections where section_id=%d limit 1;',
                $currNode['sec_parent_id']);
            $dataset = $sql->query_all($query);
            if (count($currNode)==0) break;
            $currNode = $dataset[0];
            $this->pagePath[] = $currNode;
            $this->pagePath_ids[$currNode['section_id']] = $currNode;
        }
        $this->pagePath = array_reverse($this->pagePath);
        return $this->pagePath;
    }
}