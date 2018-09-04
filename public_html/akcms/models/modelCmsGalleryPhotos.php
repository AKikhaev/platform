<?php

/**
 * cms_gallery_photos
 * Фото галерей
 *
 * @property int     idCgp ИД
 * @property int     cgpGlrId Галерея
 * @property string  cgpName Название
 * @property string  cgpFile Фото
 * @property string  cgpCreated Дата
 * @property bool    cgpEnabled Отображать
 */
class modelCmsGalleryPhotos extends cmsModelAbstract
{
    use SQLpgModelAdapter;
    /* static fields list */
    public static $_idCgp       = 'id_cgp';
    public static $_cgpGlrId    = 'cgp_glr_id';
    public static $_cgpName     = 'cgp_name';
    public static $_cgpFile     = 'cgp_file';
    public static $_cgpCreated  = 'cgp_created';
    public static $_cgpEnabled  = 'cgp_enabled';

    public static $tableName = 'cms_gallery_photos';
    protected $schemaName = '{default}';

    /* струкура таблицы */
    protected $struct = array (
    'table' => 'cms_gallery_photos',
    'schema' => '{default}',
    'comment' => 'Фото галерей',
    'fields' => array(
      'idCgp' => array(
        'COLUMN_NAME' => 'id_cgp',
        'NULLABLE' => false,
        'COMMENT' => 'ИД',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'cgpGlrId' => array(
        'COLUMN_NAME' => 'cgp_glr_id',
        'NULLABLE' => false,
        'COMMENT' => 'Галерея',
        'RELATE_TO' => 'cms_galeries',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'cgpName' => array(
        'COLUMN_NAME' => 'cgp_name',
        'NULLABLE' => false,
        'COMMENT' => 'Название',
        'FIELD_CLASS' => 'FieldText',
      ),
      'cgpFile' => array(
        'COLUMN_NAME' => 'cgp_file',
        'NULLABLE' => false,
        'COMMENT' => 'Фото',
        'FIELD_CLASS' => 'FieldText',
      ),
      'cgpCreated' => array(
        'COLUMN_NAME' => 'cgp_created',
        'NULLABLE' => false,
        'COMMENT' => 'Дата',
        'FIELD_CLASS' => 'FieldDateTime',
      ),
      'cgpEnabled' => array(
        'COLUMN_NAME' => 'cgp_enabled',
        'NULLABLE' => false,
        'COMMENT' => 'Отображать',
        'FIELD_CLASS' => 'FieldBool',
      ),
    ),
    'fieldsDB' => array(
      'id_cgp' => 'idCgp',
      'cgp_glr_id' => 'cgpGlrId',
      'cgp_name' => 'cgpName',
      'cgp_file' => 'cgpFile',
      'cgp_created' => 'cgpCreated',
      'cgp_enabled' => 'cgpEnabled',
    ),
    'primary' => 'idCgp',
    'primaryDB' => 'id_cgp',
    'tables' => array(
      0 => array(
        'table' => 'cms_gallery_photos',
        'primary' => 'idCgp',
        'primaryDB' => 'id_cgp',
        'schema' => '{default}',
        'prefix' => '',
      ),
    ),
  );

    /*** customer extensions ***/

}
