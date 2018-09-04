<?php

/**
 * cms_users_groups
 * Группы пользователей
 *
 * @property int     idUsrgpr ИД
 * @property string  usrgrpName Группа
 */
class modelCmsUsersGroups extends cmsModelAbstract
{
    use SQLpgModelAdapter;
    /* static fields list */
    public static $_idUsrgpr    = 'id_usrgpr';
    public static $_usrgrpName  = 'usrgrp_name';

    public static $tableName = 'cms_users_groups';
    protected $schemaName = '{default}';

    /* струкура таблицы */
    protected $struct = array (
    'table' => 'cms_users_groups',
    'schema' => '{default}',
    'comment' => 'Группы пользователей',
    'fields' => array(
      'idUsrgpr' => array(
        'COLUMN_NAME' => 'id_usrgpr',
        'NULLABLE' => false,
        'COMMENT' => 'ИД',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'usrgrpName' => array(
        'COLUMN_NAME' => 'usrgrp_name',
        'NULLABLE' => false,
        'COMMENT' => 'Группа',
        'FIELD_CLASS' => 'FieldText',
      ),
    ),
    'fieldsDB' => array(
      'id_usrgpr' => 'idUsrgpr',
      'usrgrp_name' => 'usrgrpName',
    ),
    'primary' => 'idUsrgpr',
    'primaryDB' => 'id_usrgpr',
    'tables' => array(
      0 => array(
        'table' => 'cms_users_groups',
        'primary' => 'idUsrgpr',
        'primaryDB' => 'id_usrgpr',
        'schema' => '{default}',
        'prefix' => '',
      ),
    ),
  );

    /*** customer extensions ***/

}
