<?php

/**
 * cms_users
 * Пользователи
 *
 * @property int     idUsr ИД
 * @property string  usrLogin Логин
 * @property string  usrEmail Почта
 * @property array   usrGrp Группа
 * @property string  usrName Имя
 * @property string  usrSoname Фамилия
 * @property string  usrLastLogin Последний вход
 * @property bool    usrEnabled Разрешен
 * @property bool    usrAdmin Админ
 * @property string  usrActcode Код активации
 * @property bool    usrActivated Активирован
 * @property string  usrLostcode Код восстановления
 * @property string  usrPasswordMd5 Хеш
 * @property string  usrRegisteredStamp Дата регистрации
 * @property string  usrRegisteredId IP адрес регистрации
 */
class modelCmsUsers extends cmsModelAbstract
{
    use SQLpgModelAdapter;
    /* static fields list */
    public static $_idUsr               = 'id_usr';
    public static $_usrLogin            = 'usr_login';
    public static $_usrEmail            = 'usr_email';
    public static $_usrGrp              = 'usr_grp';
    public static $_usrName             = 'usr_name';
    public static $_usrSoname           = 'usr_soname';
    public static $_usrLastLogin        = 'usr_last_login';
    public static $_usrEnabled          = 'usr_enabled';
    public static $_usrAdmin            = 'usr_admin';
    public static $_usrActcode          = 'usr_actcode';
    public static $_usrActivated        = 'usr_activated';
    public static $_usrLostcode         = 'usr_lostcode';
    public static $_usrPasswordMd5      = 'usr_password_md5';
    public static $_usrRegisteredStamp  = 'usr_registered_stamp';
    public static $_usrRegisteredId     = 'usr_registered_id';

    public static $tableName = 'cms_users';
    protected $schemaName = 'knpzken';

    /* струкура таблицы */
    protected $struct = array (
    'table' => 'cms_users',
    'schema' => 'knpzken',
    'comment' => 'Пользователи',
    'fields' => array(
      'idUsr' => array(
        'COLUMN_NAME' => 'id_usr',
        'NULLABLE' => false,
        'COMMENT' => 'ИД',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'usrLogin' => array(
        'COLUMN_NAME' => 'usr_login',
        'NULLABLE' => false,
        'COMMENT' => 'Логин',
        'FIELD_CLASS' => 'FieldText',
      ),
      'usrEmail' => array(
        'COLUMN_NAME' => 'usr_email',
        'NULLABLE' => false,
        'COMMENT' => 'Почта',
        'FIELD_CLASS' => 'FieldText',
      ),
      'usrGrp' => array(
        'COLUMN_NAME' => 'usr_grp',
        'NULLABLE' => false,
        'COMMENT' => 'Группа',
        'QUOTE_FUNCTION' => 'a_d',
        'FIELD_CLASS' => 'FieldManyInt',
      ),
      'usrName' => array(
        'COLUMN_NAME' => 'usr_name',
        'NULLABLE' => false,
        'COMMENT' => 'Имя',
        'FIELD_CLASS' => 'FieldText',
      ),
      'usrSoname' => array(
        'COLUMN_NAME' => 'usr_soname',
        'NULLABLE' => false,
        'COMMENT' => 'Фамилия',
        'FIELD_CLASS' => 'FieldText',
      ),
      'usrLastLogin' => array(
        'COLUMN_NAME' => 'usr_last_login',
        'NULLABLE' => true,
        'COMMENT' => 'Последний вход',
        'FIELD_CLASS' => 'FieldDateTime',
      ),
      'usrEnabled' => array(
        'COLUMN_NAME' => 'usr_enabled',
        'NULLABLE' => false,
        'COMMENT' => 'Разрешен',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'usrAdmin' => array(
        'COLUMN_NAME' => 'usr_admin',
        'NULLABLE' => false,
        'COMMENT' => 'Админ',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'usrActcode' => array(
        'COLUMN_NAME' => 'usr_actcode',
        'NULLABLE' => false,
        'COMMENT' => 'Код активации',
        'FIELD_CLASS' => 'FieldText',
      ),
      'usrActivated' => array(
        'COLUMN_NAME' => 'usr_activated',
        'NULLABLE' => false,
        'COMMENT' => 'Активирован',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'usrLostcode' => array(
        'COLUMN_NAME' => 'usr_lostcode',
        'NULLABLE' => false,
        'COMMENT' => 'Код восстановления',
        'FIELD_CLASS' => 'FieldText',
      ),
      'usrPasswordMd5' => array(
        'COLUMN_NAME' => 'usr_password_md5',
        'NULLABLE' => false,
        'COMMENT' => 'Хеш',
        'FIELD_CLASS' => 'FieldText',
      ),
      'usrRegisteredStamp' => array(
        'COLUMN_NAME' => 'usr_registered_stamp',
        'NULLABLE' => false,
        'COMMENT' => 'Дата регистрации',
        'FIELD_CLASS' => 'FieldDateTime',
      ),
      'usrRegisteredId' => array(
        'COLUMN_NAME' => 'usr_registered_id',
        'NULLABLE' => true,
        'LENGTH' => '46',
        'COMMENT' => 'IP адрес регистрации',
        'FIELD_CLASS' => 'FieldString',
      ),
    ),
    'fieldsDB' => array(
      'id_usr' => 'idUsr',
      'usr_login' => 'usrLogin',
      'usr_email' => 'usrEmail',
      'usr_grp' => 'usrGrp',
      'usr_name' => 'usrName',
      'usr_soname' => 'usrSoname',
      'usr_last_login' => 'usrLastLogin',
      'usr_enabled' => 'usrEnabled',
      'usr_admin' => 'usrAdmin',
      'usr_actcode' => 'usrActcode',
      'usr_activated' => 'usrActivated',
      'usr_lostcode' => 'usrLostcode',
      'usr_password_md5' => 'usrPasswordMd5',
      'usr_registered_stamp' => 'usrRegisteredStamp',
      'usr_registered_id' => 'usrRegisteredId',
    ),
    'primary' => 'idUsr',
    'primaryDB' => 'id_usr',
    'tables' => array(
      0 => array(
        'table' => 'cms_users',
        'primary' => 'idUsr',
        'primaryDB' => 'id_usr',
        'schema' => 'knpzken',
        'prefix' => '',
      ),
    ),
  );

    /*** customer extensions ***/

}
