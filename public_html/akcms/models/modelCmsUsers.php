<?php

/**
 * cms_users
 * Пользователи
 *
 * @property int     idUsr ИД
 * @property string  usrLogin Логин
 * @property string  usrPasswordMd5 Хеш
 * @property string  usrLastLogin Последний вход
 * @property string  usrName Имя
 * @property bool    usrAdmin Админ
 * @property bool    usrEnabled Разрешен
 * @property array   usrGrp Группа
 * @property string  usrActcode Код активации
 * @property bool    usrActivated Активирован
 * @property string  usrLostcode Код восстановления
 * @property string  usrEmail Почта
 */
class modelCmsUsers extends cmsModelAbstract
{
    use SQLpgModelAdapter;
    /* static fields list */
    public static $_idUsr           = 'id_usr';
    public static $_usrLogin        = 'usr_login';
    public static $_usrPasswordMd5  = 'usr_password_md5';
    public static $_usrLastLogin    = 'usr_last_login';
    public static $_usrName         = 'usr_name';
    public static $_usrAdmin        = 'usr_admin';
    public static $_usrEnabled      = 'usr_enabled';
    public static $_usrGrp          = 'usr_grp';
    public static $_usrActcode      = 'usr_actcode';
    public static $_usrActivated    = 'usr_activated';
    public static $_usrLostcode     = 'usr_lostcode';
    public static $_usrEmail        = 'usr_email';

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
      'usrPasswordMd5' => array(
        'COLUMN_NAME' => 'usr_password_md5',
        'NULLABLE' => false,
        'COMMENT' => 'Хеш',
        'FIELD_CLASS' => 'FieldText',
      ),
      'usrLastLogin' => array(
        'COLUMN_NAME' => 'usr_last_login',
        'NULLABLE' => true,
        'COMMENT' => 'Последний вход',
        'FIELD_CLASS' => 'FieldDateTime',
      ),
      'usrName' => array(
        'COLUMN_NAME' => 'usr_name',
        'NULLABLE' => false,
        'COMMENT' => 'Имя',
        'FIELD_CLASS' => 'FieldText',
      ),
      'usrAdmin' => array(
        'COLUMN_NAME' => 'usr_admin',
        'NULLABLE' => false,
        'COMMENT' => 'Админ',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'usrEnabled' => array(
        'COLUMN_NAME' => 'usr_enabled',
        'NULLABLE' => false,
        'COMMENT' => 'Разрешен',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'usrGrp' => array(
        'COLUMN_NAME' => 'usr_grp',
        'NULLABLE' => false,
        'COMMENT' => 'Группа',
        'QUOTE_FUNCTION' => 'a_d',
        'FIELD_CLASS' => 'FieldManyInt',
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
      'usrEmail' => array(
        'COLUMN_NAME' => 'usr_email',
        'NULLABLE' => false,
        'COMMENT' => 'Почта',
        'FIELD_CLASS' => 'FieldText',
      ),
    ),
    'fieldsDB' => array(
      'id_usr' => 'idUsr',
      'usr_login' => 'usrLogin',
      'usr_password_md5' => 'usrPasswordMd5',
      'usr_last_login' => 'usrLastLogin',
      'usr_name' => 'usrName',
      'usr_admin' => 'usrAdmin',
      'usr_enabled' => 'usrEnabled',
      'usr_grp' => 'usrGrp',
      'usr_actcode' => 'usrActcode',
      'usr_activated' => 'usrActivated',
      'usr_lostcode' => 'usrLostcode',
      'usr_email' => 'usrEmail',
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
