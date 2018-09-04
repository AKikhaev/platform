<?php

/**
 * cms_users_groups_rgth
 * Права групп
 *
 * @property int     usrrghtGrpid ИД группы
 * @property string  usrrghtName Право
 * @property bool    usrrghtMode Разрешено
 */
class modelCmsUsersGroupsRgth extends cmsModelAbstract
{
    use SQLpgModelAdapter;
    /* static fields list */
    public static $_usrrghtGrpid  = 'usrrght_grpid';
    public static $_usrrghtName   = 'usrrght_name';
    public static $_usrrghtMode   = 'usrrght_mode';

    public static $tableName = 'cms_users_groups_rgth';
    protected $schemaName = '{default}';

    /* струкура таблицы */
    protected $struct = array (
    'table' => 'cms_users_groups_rgth',
    'schema' => '{default}',
    'comment' => 'Права групп',
    'fields' => array(
      'usrrghtGrpid' => array(
        'COLUMN_NAME' => 'usrrght_grpid',
        'NULLABLE' => false,
        'COMMENT' => 'ИД группы',
        'RELATE_TO' => 'cms_users_groups',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'usrrghtName' => array(
        'COLUMN_NAME' => 'usrrght_name',
        'NULLABLE' => false,
        'COMMENT' => 'Право',
        'FIELD_CLASS' => 'FieldText',
      ),
      'usrrghtMode' => array(
        'COLUMN_NAME' => 'usrrght_mode',
        'NULLABLE' => false,
        'COMMENT' => 'Разрешено',
        'FIELD_CLASS' => 'FieldBool',
      ),
    ),
    'fieldsDB' => array(
      'usrrght_grpid' => 'usrrghtGrpid',
      'usrrght_name' => 'usrrghtName',
      'usrrght_mode' => 'usrrghtMode',
    ),
    'primary' => '',
    'primaryDB' => '',
    'tables' => array(
      0 => array(
        'table' => 'cms_users_groups_rgth',
        'primary' => '',
        'primaryDB' => '',
        'schema' => '{default}',
        'prefix' => '',
      ),
    ),
  );

    /*** customer extensions ***/

}
