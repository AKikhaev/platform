<?php

/**
 * cms_sections
 * Страницы
 *
 * @property int     sectionId ИД
 * @property int     secParentId Род. страница
 * @property string  secUrl имя пути
 * @property string  secUrlFull полный путь
 * @property string  secNameshort Краткое название
 * @property string  secNamefull Полное название
 * @property string  secPage Шаблон
 * @property string  secPageChild Шаблон "детей"
 * @property string  secContent Основной контент
 * @property string  secKeywords SEO Ключевые слова
 * @property string  secDescription SEO Описание
 * @property string  secImgfile Изображение страницы
 * @property int     secSort Сортировка
 * @property bool    secEnabled Раздел разрешен
 * @property string  secUnits Подключеные модули
 * @property bool    secShowinmenu Отображать в меню
 * @property bool    secOpenfirst Отображать дочерний вместо этого
 * @property string  secCreated Дата создания
 * @property bool    secSystem Системный раздел
 * @property int     secGlrId ИД связанной галереи
 * @property string  secContshort Краткий текст для превьюшек
 * @property bool    secToNews Помещать в ленту новостей/артифакт
 * @property string  secLstModify Последнее изменение
 * @property string  secParams Параметры подключенный модулей
 * @property string  secFrom Отображать с даты
 * @property string  secTitle SEO Заголовок
 * @property int     secHowchild 0-не отображать, 1-по порядку, 2-по дате (со старых), 3-по дате (с новых)
 * @property array   secIdsClosest ИД путь
 */
class modelCmsSections extends cmsModelAbstract
{
    use SQLpgModelAdapter;
    /* static fields list */
    public static $_sectionId       = 'section_id';
    public static $_secParentId     = 'sec_parent_id';
    public static $_secUrl          = 'sec_url';
    public static $_secUrlFull      = 'sec_url_full';
    public static $_secNameshort    = 'sec_nameshort';
    public static $_secNamefull     = 'sec_namefull';
    public static $_secPage         = 'sec_page';
    public static $_secPageChild    = 'sec_page_child';
    public static $_secContent      = 'sec_content';
    public static $_secKeywords     = 'sec_keywords';
    public static $_secDescription  = 'sec_description';
    public static $_secImgfile      = 'sec_imgfile';
    public static $_secSort         = 'sec_sort';
    public static $_secEnabled      = 'sec_enabled';
    public static $_secUnits        = 'sec_units';
    public static $_secShowinmenu   = 'sec_showinmenu';
    public static $_secOpenfirst    = 'sec_openfirst';
    public static $_secCreated      = 'sec_created';
    public static $_secSystem       = 'sec_system';
    public static $_secGlrId        = 'sec_glr_id';
    public static $_secContshort    = 'sec_contshort';
    public static $_secToNews       = 'sec_to_news';
    public static $_secLstModify    = 'sec_lst_modify';
    public static $_secParams       = 'sec_params';
    public static $_secFrom         = 'sec_from';
    public static $_secTitle        = 'sec_title';
    public static $_secHowchild     = 'sec_howchild';
    public static $_secIdsClosest   = 'sec_ids_closest';

    public static $tableName = 'cms_sections';
    protected $schemaName = '{default}';

    /* струкура таблицы */
    protected $struct = array (
    'table' => 'cms_sections',
    'schema' => '{default}',
    'comment' => 'Страницы',
    'fields' => array(
      'sectionId' => array(
        'COLUMN_NAME' => 'section_id',
        'NULLABLE' => false,
        'COMMENT' => 'ИД',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'secParentId' => array(
        'COLUMN_NAME' => 'sec_parent_id',
        'NULLABLE' => false,
        'COMMENT' => 'Род. страница',
        'RELATE_TO' => 'cms_sections',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'secUrl' => array(
        'COLUMN_NAME' => 'sec_url',
        'NULLABLE' => false,
        'COMMENT' => 'имя пути',
        'FIELD_CLASS' => 'FieldText',
      ),
      'secUrlFull' => array(
        'COLUMN_NAME' => 'sec_url_full',
        'NULLABLE' => false,
        'COMMENT' => 'полный путь',
        'FIELD_CLASS' => 'FieldText',
      ),
      'secNameshort' => array(
        'COLUMN_NAME' => 'sec_nameshort',
        'NULLABLE' => false,
        'COMMENT' => 'Краткое название',
        'FIELD_CLASS' => 'FieldText',
      ),
      'secNamefull' => array(
        'COLUMN_NAME' => 'sec_namefull',
        'NULLABLE' => false,
        'COMMENT' => 'Полное название',
        'FIELD_CLASS' => 'FieldText',
      ),
      'secPage' => array(
        'COLUMN_NAME' => 'sec_page',
        'NULLABLE' => false,
        'COMMENT' => 'Шаблон',
        'FIELD_CLASS' => 'FieldText',
      ),
      'secPageChild' => array(
        'COLUMN_NAME' => 'sec_page_child',
        'NULLABLE' => false,
        'COMMENT' => 'Шаблон "детей"',
        'FIELD_CLASS' => 'FieldText',
      ),
      'secContent' => array(
        'COLUMN_NAME' => 'sec_content',
        'NULLABLE' => false,
        'COMMENT' => 'Основной контент',
        'FIELD_CLASS' => 'FieldText',
      ),
      'secKeywords' => array(
        'COLUMN_NAME' => 'sec_keywords',
        'NULLABLE' => false,
        'COMMENT' => 'SEO Ключевые слова',
        'FIELD_CLASS' => 'FieldText',
      ),
      'secDescription' => array(
        'COLUMN_NAME' => 'sec_description',
        'NULLABLE' => false,
        'COMMENT' => 'SEO Описание',
        'FIELD_CLASS' => 'FieldText',
      ),
      'secImgfile' => array(
        'COLUMN_NAME' => 'sec_imgfile',
        'NULLABLE' => false,
        'COMMENT' => 'Изображение страницы',
        'FIELD_CLASS' => 'FieldText',
      ),
      'secSort' => array(
        'COLUMN_NAME' => 'sec_sort',
        'NULLABLE' => false,
        'COMMENT' => 'Сортировка',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'secEnabled' => array(
        'COLUMN_NAME' => 'sec_enabled',
        'NULLABLE' => false,
        'COMMENT' => 'Раздел разрешен',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'secUnits' => array(
        'COLUMN_NAME' => 'sec_units',
        'NULLABLE' => false,
        'COMMENT' => 'Подключеные модули',
        'FIELD_CLASS' => 'FieldText',
      ),
      'secShowinmenu' => array(
        'COLUMN_NAME' => 'sec_showinmenu',
        'NULLABLE' => false,
        'COMMENT' => 'Отображать в меню',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'secOpenfirst' => array(
        'COLUMN_NAME' => 'sec_openfirst',
        'NULLABLE' => false,
        'COMMENT' => 'Отображать дочерний вместо этого',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'secCreated' => array(
        'COLUMN_NAME' => 'sec_created',
        'NULLABLE' => false,
        'COMMENT' => 'Дата создания',
        'FIELD_CLASS' => 'FieldDateTime',
      ),
      'secSystem' => array(
        'COLUMN_NAME' => 'sec_system',
        'NULLABLE' => false,
        'COMMENT' => 'Системный раздел',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'secGlrId' => array(
        'COLUMN_NAME' => 'sec_glr_id',
        'NULLABLE' => false,
        'COMMENT' => 'ИД связанной галереи',
        'RELATE_TO' => 'cms_galeries',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'secContshort' => array(
        'COLUMN_NAME' => 'sec_contshort',
        'NULLABLE' => false,
        'COMMENT' => 'Краткий текст для превьюшек',
        'FIELD_CLASS' => 'FieldText',
      ),
      'secToNews' => array(
        'COLUMN_NAME' => 'sec_to_news',
        'NULLABLE' => false,
        'COMMENT' => 'Помещать в ленту новостей/артифакт',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'secLstModify' => array(
        'COLUMN_NAME' => 'sec_lst_modify',
        'NULLABLE' => false,
        'COMMENT' => 'Последнее изменение',
        'FIELD_CLASS' => 'FieldDateTime',
      ),
      'secParams' => array(
        'COLUMN_NAME' => 'sec_params',
        'NULLABLE' => false,
        'COMMENT' => 'Параметры подключенный модулей',
        'FIELD_CLASS' => 'FieldText',
      ),
      'secFrom' => array(
        'COLUMN_NAME' => 'sec_from',
        'NULLABLE' => false,
        'COMMENT' => 'Отображать с даты',
        'FIELD_CLASS' => 'FieldDateTime',
      ),
      'secTitle' => array(
        'COLUMN_NAME' => 'sec_title',
        'NULLABLE' => false,
        'COMMENT' => 'SEO Заголовок',
        'FIELD_CLASS' => 'FieldText',
      ),
      'secHowchild' => array(
        'COLUMN_NAME' => 'sec_howchild',
        'NULLABLE' => false,
        'COMMENT' => '0-не отображать, 1-по порядку, 2-по дате (со старых), 3-по дате (с новых)',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'secIdsClosest' => array(
        'COLUMN_NAME' => 'sec_ids_closest',
        'NULLABLE' => true,
        'COMMENT' => 'ИД путь',
        'QUOTE_FUNCTION' => 'a_d',
        'FIELD_CLASS' => 'FieldManyInt',
      ),
    ),
    'fieldsDB' => array(
      'section_id' => 'sectionId',
      'sec_parent_id' => 'secParentId',
      'sec_url' => 'secUrl',
      'sec_url_full' => 'secUrlFull',
      'sec_nameshort' => 'secNameshort',
      'sec_namefull' => 'secNamefull',
      'sec_page' => 'secPage',
      'sec_page_child' => 'secPageChild',
      'sec_content' => 'secContent',
      'sec_keywords' => 'secKeywords',
      'sec_description' => 'secDescription',
      'sec_imgfile' => 'secImgfile',
      'sec_sort' => 'secSort',
      'sec_enabled' => 'secEnabled',
      'sec_units' => 'secUnits',
      'sec_showinmenu' => 'secShowinmenu',
      'sec_openfirst' => 'secOpenfirst',
      'sec_created' => 'secCreated',
      'sec_system' => 'secSystem',
      'sec_glr_id' => 'secGlrId',
      'sec_contshort' => 'secContshort',
      'sec_to_news' => 'secToNews',
      'sec_lst_modify' => 'secLstModify',
      'sec_params' => 'secParams',
      'sec_from' => 'secFrom',
      'sec_title' => 'secTitle',
      'sec_howchild' => 'secHowchild',
      'sec_ids_closest' => 'secIdsClosest',
    ),
    'primary' => 'sectionId',
    'primaryDB' => 'section_id',
    'tables' => array(
      0 => array(
        'table' => 'cms_sections',
        'primary' => 'sectionId',
        'primaryDB' => 'section_id',
        'schema' => '{default}',
        'prefix' => '',
      ),
    ),
  );

    /*** customer extensions ***/

}
