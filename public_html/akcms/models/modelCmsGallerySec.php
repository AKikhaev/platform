<?php

/**
 * cms_gallery_sec
 * Галареи на страницах
 *
 * @property int     secId Страница
 * @property int     glrId Галерея
 */
class modelCmsGallerySec extends cmsModelAbstract
{
    use SQLpgModelAdapter;
    /* static fields list */
    public static $_secId  = 'sec_id';
    public static $_glrId  = 'glr_id';

    public static $tableName = 'cms_gallery_sec';
    protected $schemaName = 'knpzken';

    /* струкура таблицы */
    protected $struct = array (
    'table' => 'cms_gallery_sec',
    'schema' => 'knpzken',
    'comment' => 'Галареи на страницах',
    'fields' => array(
      'secId' => array(
        'COLUMN_NAME' => 'sec_id',
        'NULLABLE' => false,
        'COMMENT' => 'Страница',
        'RELATE_TO' => 'cms_sections',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'glrId' => array(
        'COLUMN_NAME' => 'glr_id',
        'NULLABLE' => false,
        'COMMENT' => 'Галерея',
        'RELATE_TO' => 'cms_galeries',
        'FIELD_CLASS' => 'FieldInt',
      ),
    ),
    'fieldsDB' => array(
      'sec_id' => 'secId',
      'glr_id' => 'glrId',
    ),
    'primary' => '',
    'primaryDB' => '',
    'tables' => array(
      0 => array(
        'table' => 'cms_gallery_sec',
        'primary' => '',
        'primaryDB' => '',
        'schema' => 'knpzken',
        'prefix' => '',
      ),
    ),
  );

    /*** customer extensions ***/

}
