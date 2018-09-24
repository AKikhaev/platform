<?php

/**
 * cms_obj_files
 * Хранилище документов
 *
 * @property int     cofId ИД
 * @property string  cofObj Объект
 * @property int     cofObjId ИД объекта
 * @property string  cofObjField Поле объекта
 * @property string  cofTitle Назание файла
 * @property string  cofFile Файл
 * @property bool    cofEnabled Разрешен
 * @property int     cofOwnerId Владелец
 * @property string  cofFileExt Расширение исходного файла
 * @property bool    cofFilePacked Файл упакован
 * @property int     cofSrvId Сервер хранилища
 * @property bool    cofDraft Черновик (еще не загружен)
 * @property bool    cofSecured Доступ ограничен
 * @property string  cofUploadedStamp Дата загрузки
 */
class modelCmsObjFiles extends cmsModelAbstract
{
    use SQLpgModelAdapter;
    /* static fields list */
    public static $_cofId             = 'cof_id';
    public static $_cofObj            = 'cof_obj';
    public static $_cofObjId          = 'cof_obj_id';
    public static $_cofObjField       = 'cof_obj_field';
    public static $_cofTitle          = 'cof_title';
    public static $_cofFile           = 'cof_file';
    public static $_cofEnabled        = 'cof_enabled';
    public static $_cofOwnerId        = 'cof_owner_id';
    public static $_cofFileExt        = 'cof_file_ext';
    public static $_cofFilePacked     = 'cof_file_packed';
    public static $_cofSrvId          = 'cof_srv_id';
    public static $_cofDraft          = 'cof_draft';
    public static $_cofSecured        = 'cof_secured';
    public static $_cofUploadedStamp  = 'cof_uploaded_stamp';

    public static $tableName = 'cms_obj_files';
    protected $schemaName = '{default}';

    /* струкура таблицы */
    protected $struct = array (
    'table' => 'cms_obj_files',
    'schema' => '{default}',
    'comment' => 'Хранилище документов',
    'fields' => array(
      'cofId' => array(
        'COLUMN_NAME' => 'cof_id',
        'NULLABLE' => false,
        'COMMENT' => 'ИД',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'cofObj' => array(
        'COLUMN_NAME' => 'cof_obj',
        'NULLABLE' => true,
        'COMMENT' => 'Объект',
        'FIELD_CLASS' => 'FieldText',
      ),
      'cofObjId' => array(
        'COLUMN_NAME' => 'cof_obj_id',
        'NULLABLE' => false,
        'COMMENT' => 'ИД объекта',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'cofObjField' => array(
        'COLUMN_NAME' => 'cof_obj_field',
        'NULLABLE' => false,
        'COMMENT' => 'Поле объекта',
        'FIELD_CLASS' => 'FieldText',
      ),
      'cofTitle' => array(
        'COLUMN_NAME' => 'cof_title',
        'NULLABLE' => false,
        'COMMENT' => 'Назание файла',
        'FIELD_CLASS' => 'FieldText',
      ),
      'cofFile' => array(
        'COLUMN_NAME' => 'cof_file',
        'NULLABLE' => false,
        'COMMENT' => 'Файл',
        'FIELD_CLASS' => 'FieldText',
      ),
      'cofEnabled' => array(
        'COLUMN_NAME' => 'cof_enabled',
        'NULLABLE' => false,
        'COMMENT' => 'Разрешен',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'cofOwnerId' => array(
        'COLUMN_NAME' => 'cof_owner_id',
        'NULLABLE' => false,
        'COMMENT' => 'Владелец',
        'RELATE_TO' => 'cms_users',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'cofFileExt' => array(
        'COLUMN_NAME' => 'cof_file_ext',
        'NULLABLE' => false,
        'LENGTH' => '10',
        'COMMENT' => 'Расширение исходного файла',
        'FIELD_CLASS' => 'FieldString',
      ),
      'cofFilePacked' => array(
        'COLUMN_NAME' => 'cof_file_packed',
        'NULLABLE' => false,
        'COMMENT' => 'Файл упакован',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'cofSrvId' => array(
        'COLUMN_NAME' => 'cof_srv_id',
        'NULLABLE' => false,
        'COMMENT' => 'Сервер хранилища',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'cofDraft' => array(
        'COLUMN_NAME' => 'cof_draft',
        'NULLABLE' => false,
        'COMMENT' => 'Черновик (еще не загружен)',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'cofSecured' => array(
        'COLUMN_NAME' => 'cof_secured',
        'NULLABLE' => false,
        'COMMENT' => 'Доступ ограничен',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'cofUploadedStamp' => array(
        'COLUMN_NAME' => 'cof_uploaded_stamp',
        'NULLABLE' => false,
        'COMMENT' => 'Дата загрузки',
        'FIELD_CLASS' => 'FieldDateTime',
      ),
    ),
    'fieldsDB' => array(
      'cof_id' => 'cofId',
      'cof_obj' => 'cofObj',
      'cof_obj_id' => 'cofObjId',
      'cof_obj_field' => 'cofObjField',
      'cof_title' => 'cofTitle',
      'cof_file' => 'cofFile',
      'cof_enabled' => 'cofEnabled',
      'cof_owner_id' => 'cofOwnerId',
      'cof_file_ext' => 'cofFileExt',
      'cof_file_packed' => 'cofFilePacked',
      'cof_srv_id' => 'cofSrvId',
      'cof_draft' => 'cofDraft',
      'cof_secured' => 'cofSecured',
      'cof_uploaded_stamp' => 'cofUploadedStamp',
    ),
    'primary' => 'cofId',
    'primaryDB' => 'cof_id',
    'tables' => array(
      0 => array(
        'table' => 'cms_obj_files',
        'primary' => 'cofId',
        'primaryDB' => 'cof_id',
        'schema' => '{default}',
        'prefix' => '',
      ),
    ),
  );

    /*** customer extensions ***/

}
