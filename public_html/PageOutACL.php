<?php

class PageOutACL extends AclProcessor
{
    //public $editMode = false;
    //public $inEditCan = false;
    private static $_instances = [];

    public static function getInstance(&$pageData)
    {
        //$stacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        //if ($pageData===null)var_log_terminal__($stacktrace);
        return
            isset(self::$_instances[$pageData['section_id']])?
                $pageData['section_id']:
                $_instances[$pageData['section_id']] = new self($pageData);
    }

    public function __construct(array &$pageData)
    {
        global $sql;
        foreach ($sql->da_a($pageData['sec_ids_closest']) as $id) {
            $this->acl[] = 'pg'.$id;
        }

        //$this->inEditCan = $this->hasRight('inEdit',false,true);
        //$this->editMode = $this->hasRight();
    }
}