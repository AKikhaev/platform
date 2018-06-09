<?php

/**
 * cms_obj_photos
 * Фото элементов
 *
 * @property int     idCop ИД
 * @property string  copObj Объект
 * @property int     copObjId ИД объекта
 * @property bool    copHdr Заглавное
 * @property string  copName Название
 * @property string  copFile Изображение
 * @property string  copCreated Дата
 * @property bool    copEnabled Отображать
 */
class modelCmsObjPhotos extends cmsModelAbstact
{
    use SQLpgModelAdapter;
    /* static fields list */
    public static $_idCop       = 'id_cop';
    public static $_copObj      = 'cop_obj';
    public static $_copObjId    = 'cop_obj_id';
    public static $_copHdr      = 'cop_hdr';
    public static $_copName     = 'cop_name';
    public static $_copFile     = 'cop_file';
    public static $_copCreated  = 'cop_created';
    public static $_copEnabled  = 'cop_enabled';

    public static $tableName = 'cms_obj_photos';
    protected $schemaName = 'knpzken';

    /* струкура таблицы */
    protected $struct = array (
    'table' => 'cms_obj_photos',
    'schema' => 'knpzken',
    'comment' => 'Фото элементов',
    'fields' => array(
      'idCop' => array(
        'COLUMN_NAME' => 'id_cop',
        'NULLABLE' => false,
        'COMMENT' => 'ИД',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'copObj' => array(
        'COLUMN_NAME' => 'cop_obj',
        'NULLABLE' => false,
        'COMMENT' => 'Объект',
        'FIELD_CLASS' => 'FieldText',
      ),
      'copObjId' => array(
        'COLUMN_NAME' => 'cop_obj_id',
        'NULLABLE' => false,
        'COMMENT' => 'ИД объекта',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'copHdr' => array(
        'COLUMN_NAME' => 'cop_hdr',
        'NULLABLE' => false,
        'COMMENT' => 'Заглавное',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'copName' => array(
        'COLUMN_NAME' => 'cop_name',
        'NULLABLE' => false,
        'COMMENT' => 'Название',
        'FIELD_CLASS' => 'FieldText',
      ),
      'copFile' => array(
        'COLUMN_NAME' => 'cop_file',
        'NULLABLE' => false,
        'COMMENT' => 'Изображение',
        'FIELD_CLASS' => 'FieldText',
      ),
      'copCreated' => array(
        'COLUMN_NAME' => 'cop_created',
        'NULLABLE' => false,
        'COMMENT' => 'Дата',
        'FIELD_CLASS' => 'FieldDateTime',
      ),
      'copEnabled' => array(
        'COLUMN_NAME' => 'cop_enabled',
        'NULLABLE' => false,
        'COMMENT' => 'Отображать',
        'FIELD_CLASS' => 'FieldBool',
      ),
    ),
    'fieldsDB' => array(
      'id_cop' => 'idCop',
      'cop_obj' => 'copObj',
      'cop_obj_id' => 'copObjId',
      'cop_hdr' => 'copHdr',
      'cop_name' => 'copName',
      'cop_file' => 'copFile',
      'cop_created' => 'copCreated',
      'cop_enabled' => 'copEnabled',
    ),
    'primary' => 'idCop',
    'primaryDB' => 'id_cop',
    'tables' => array(
      0 => array(
        'table' => 'cms_obj_photos',
        'primary' => 'idCop',
        'primaryDB' => 'id_cop',
        'schema' => 'knpzken',
        'prefix' => '',
      ),
    ),
  );

    /*** customer extensions ***/

}
