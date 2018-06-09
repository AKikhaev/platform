<?php

/**
 * cms_menu_items
 * Меню, доработать
 *
 * @property int     mnuiId ИД
 * @property int     mnuiMnuId Родитель пункта меню
 * @property int     mnuiSecId Страница
 * @property int     mnuiSort Сортировка
 */
class modelCmsMenuItems extends cmsModelAbstact
{
    use SQLpgModelAdapter;
    /* static fields list */
    public static $_mnuiId     = 'mnui_id';
    public static $_mnuiMnuId  = 'mnui_mnu_id';
    public static $_mnuiSecId  = 'mnui_sec_id';
    public static $_mnuiSort   = 'mnui_sort';

    public static $tableName = 'cms_menu_items';
    protected $schemaName = 'knpzken';

    /* струкура таблицы */
    protected $struct = array (
    'table' => 'cms_menu_items',
    'schema' => 'knpzken',
    'comment' => 'Меню, доработать',
    'fields' => array(
      'mnuiId' => array(
        'COLUMN_NAME' => 'mnui_id',
        'NULLABLE' => false,
        'COMMENT' => 'ИД',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'mnuiMnuId' => array(
        'COLUMN_NAME' => 'mnui_mnu_id',
        'NULLABLE' => false,
        'COMMENT' => 'Родитель пункта меню',
        'RELATE_TO' => 'cms_menu_items',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'mnuiSecId' => array(
        'COLUMN_NAME' => 'mnui_sec_id',
        'NULLABLE' => false,
        'COMMENT' => 'Страница',
        'RELATE_TO' => 'cms_sections',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'mnuiSort' => array(
        'COLUMN_NAME' => 'mnui_sort',
        'NULLABLE' => false,
        'COMMENT' => 'Сортировка',
        'FIELD_CLASS' => 'FieldInt',
      ),
    ),
    'fieldsDB' => array(
      'mnui_id' => 'mnuiId',
      'mnui_mnu_id' => 'mnuiMnuId',
      'mnui_sec_id' => 'mnuiSecId',
      'mnui_sort' => 'mnuiSort',
    ),
    'primary' => 'mnuiId',
    'primaryDB' => 'mnui_id',
    'tables' => array(
      0 => array(
        'table' => 'cms_menu_items',
        'primary' => 'mnuiId',
        'primaryDB' => 'mnui_id',
        'schema' => 'knpzken',
        'prefix' => '',
      ),
    ),
  );

    /*** customer extensions ***/

}
