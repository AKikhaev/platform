<?php

/**
 * cms_galeries
 * Галереи
 *
 * @property int     idGlr ИД
 * @property int     glrSecId Страница
 * @property string  glrName Название
 * @property string  glrCreated Дата
 * @property bool    glrEnabled Отображать
 * @property string  glrFile Путь к обложке
 * @property int     glrType 1 - photo, 2 - video, 3 - audio
 * @property bool    glrSys Системная
 * @property string  glrDesc Описание
 * @property int     glrSort Сортировка
 */
class modelCmsGaleries extends cmsModelAbstract
{
    use SQLpgModelAdapter;
    /* static fields list */
    public static $_idGlr       = 'id_glr';
    public static $_glrSecId    = 'glr_sec_id';
    public static $_glrName     = 'glr_name';
    public static $_glrCreated  = 'glr_created';
    public static $_glrEnabled  = 'glr_enabled';
    public static $_glrFile     = 'glr_file';
    public static $_glrType     = 'glr_type';
    public static $_glrSys      = 'glr_sys';
    public static $_glrDesc     = 'glr_desc';
    public static $_glrSort     = 'glr_sort';

    public static $tableName = 'cms_galeries';
    protected $schemaName = '{default}';

    /* струкура таблицы */
    protected $struct = array (
    'table' => 'cms_galeries',
    'schema' => '{default}',
    'comment' => 'Галереи',
    'fields' => array(
      'idGlr' => array(
        'COLUMN_NAME' => 'id_glr',
        'NULLABLE' => false,
        'COMMENT' => 'ИД',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'glrSecId' => array(
        'COLUMN_NAME' => 'glr_sec_id',
        'NULLABLE' => false,
        'COMMENT' => 'Страница',
        'RELATE_TO' => 'cms_sections',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'glrName' => array(
        'COLUMN_NAME' => 'glr_name',
        'NULLABLE' => false,
        'COMMENT' => 'Название',
        'FIELD_CLASS' => 'FieldText',
      ),
      'glrCreated' => array(
        'COLUMN_NAME' => 'glr_created',
        'NULLABLE' => false,
        'COMMENT' => 'Дата',
        'FIELD_CLASS' => 'FieldDateTime',
      ),
      'glrEnabled' => array(
        'COLUMN_NAME' => 'glr_enabled',
        'NULLABLE' => true,
        'COMMENT' => 'Отображать',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'glrFile' => array(
        'COLUMN_NAME' => 'glr_file',
        'NULLABLE' => false,
        'COMMENT' => 'Путь к обложке',
        'FIELD_CLASS' => 'FieldText',
      ),
      'glrType' => array(
        'COLUMN_NAME' => 'glr_type',
        'NULLABLE' => false,
        'COMMENT' => '1 - photo, 2 - video, 3 - audio',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'glrSys' => array(
        'COLUMN_NAME' => 'glr_sys',
        'NULLABLE' => false,
        'COMMENT' => 'Системная',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'glrDesc' => array(
        'COLUMN_NAME' => 'glr_desc',
        'NULLABLE' => false,
        'COMMENT' => 'Описание',
        'FIELD_CLASS' => 'FieldText',
      ),
      'glrSort' => array(
        'COLUMN_NAME' => 'glr_sort',
        'NULLABLE' => false,
        'COMMENT' => 'Сортировка',
        'FIELD_CLASS' => 'FieldInt',
      ),
    ),
    'fieldsDB' => array(
      'id_glr' => 'idGlr',
      'glr_sec_id' => 'glrSecId',
      'glr_name' => 'glrName',
      'glr_created' => 'glrCreated',
      'glr_enabled' => 'glrEnabled',
      'glr_file' => 'glrFile',
      'glr_type' => 'glrType',
      'glr_sys' => 'glrSys',
      'glr_desc' => 'glrDesc',
      'glr_sort' => 'glrSort',
    ),
    'primary' => 'idGlr',
    'primaryDB' => 'id_glr',
    'tables' => array(
      0 => array(
        'table' => 'cms_galeries',
        'primary' => 'idGlr',
        'primaryDB' => 'id_glr',
        'schema' => '{default}',
        'prefix' => '',
      ),
    ),
  );

    /*** customer extensions ***/

}
