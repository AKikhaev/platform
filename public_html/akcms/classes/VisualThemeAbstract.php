<?php

class VisualThemeAbstract
{
    const weekdays = array('Воскресенье','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота');
    const months = array('','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
    /**
     * 7 июля в 14:17         - j F в H:i
     * Пятница, 7 июля 2017г. - l, j F Y г.
     * @param $format
     * @param $dt
     * @return false|string
     */
    public static function dateRus($format,$dt) {
        if (mb_strpos($format,'l')!==false) $format = str_replace('l',self::weekdays[date('w',$dt)],$format);
        if (mb_strpos($format,'F')!==false) $format = str_replace('F',self::months[date('n',$dt)],$format);
        return date($format,$dt);
    }

    public static function toTel($string) {
        $string = preg_replace('/[^0-9]/iu','',$string);
        if (mb_substr($string,0,1)=='8') $string = '7'.mb_substr($string,1);
        if (mb_strlen($string)>10 && mb_substr($string,0,1)!='+') $string = '+'.$string;
        return $string;
    }

    /** Обработчик плейсхолдера. Вывод даты
     * @param $pageData
     * Обязательный. массив с данными
     * @param $editMode
     * Обязательный. режим редактирования
     * @param $text
     * Обязательный. Заменяемый в шаблоне текст
     * @param $field
     * Обязательный. поле из шаблоны
     * @param $format
     * формат даты как в date, русский язык
     * @return false|string
     */
    public static function _ph_date(&$pageData,$editMode,$text,$field,$format){
        return self::dateRus($format,strtotime($pageData[$field]));
    }

    /** Обработчик плейсхолдера. Вывод текста
     * @param $pageData
     * Обязательный. массив с данными
     * @param $editMode
     * Обязательный. режим редактирования
     * @param $text
     * Обязательный. Заменяемый в шаблоне текст
     * @param $field
     * Обязательный. поле из шаблоны
     * @param $quote
     * Формат кавычек.
     * 0 - не экранировать
     * 1 - одинарные
     * 2 - двойные
     * @return false|string
     */
    public static function _ph_text(&$pageData,$editMode,$text,$field,$quote = 0){
        switch ($quote) {
            case 0: return $pageData[$field];
            case 1: return str_replace('\'','&apos;',$pageData[$field]); //&#039;
            case 2: return str_replace('"','&quot;',$pageData[$field]); //&#034;
        }
    }

    /** Обработчик плейсхолдера. Другой шаблон
     * @param $pageData
     * Обязательный. массив с данными
     * @param $editMode
     * Обязательный. режим редактирования
     * @param $text
     * Обязательный. Заменяемый в шаблоне текст
     * @param $template
     * Обязательный. Шаблон
     * @return false|string
     */
    public static function _ph_tmpl(&$pageData,$editMode,$text,$template){
        $html = file_get_contents($template.'.shtm',true);
        return '';
    }

    /** Обрабатывает редактируемые поля в шаблоне
     * @param $html
     * @param $pageData
     * должен содержать массив с полями: section_id, sec_content, sec_namefull, sec_imgfile, sec_from
     * @param $editMode
     * Режим редактирования, если не указан используется shp::$editMode
     */
    public static function replacementsEditable(&$html, &$pageData, $editMode = null){
        /* @var $sql pgdb */
        global $sql;
        $sectionIds = array(0);
        if (isset($pageData['section_id'])) $sectionIds[] = $pageData['section_id'];

        $repls = array();
        $ss_data = $sql->query_all('SELECT sec_id,secs_id,secs_code,secs_str FROM cms_sections_string WHERE sec_id=ANY(' . $sql->a_d($sectionIds).')');
        if ($ss_data!=false) foreach ($ss_data as $item) $repls[($item['sec_id']==0?'eg':'ep').'_'.$item['secs_code']] = $item;

        /* Вызывает функции из класса VisualTheme с префиксом _ph_
         * 1 - функция
         * 2 - поле
         * 3 - параметры
         * 5 - контент
         */
        $html=preg_replace_callback('~\{#_(\w+):(.+)(:.+)*#\}~usU',function($matches) use (&$repls,&$pageData,$editMode){
            $text = ''; if (isset($matches[4])) $text = $matches[4];
            $funct = $matches[1];
            $field = $matches[2];
            $params = isset($matches[3])?explode(':',trim($matches[3],':')):[];
            return call_user_func_array('VisualTheme::_ph_'.$funct,array_merge(array(&$pageData,$editMode,&$text,$field),$params));
        },$html);

        /* Редактор для полей ep_ и eg_ (локальные параметры и глобальные)
         * 1 - тип
         * 2 - ключ
         * 3 - параметры
         * 3_0 - mult
         * 3_1 - hint
         * 5 - контент
         */
        $html=preg_replace_callback('~\{#(ep|eg):(\w+)(:[^#]+)#(?|\/\}(.*)\{\/#\1:\2(?::[^#])?#\}|})~usU',function($matches) use (&$repls,&$pageData,$editMode){
            $text = ''; if (isset($matches[4])) $text = $matches[4];
            $code = $matches[1].'_'.$matches[2];
            $params = explode(':',trim($matches[3],':'));
            $hint = ''; if (isset($params[1])) $hint = $params[1];
            $mult = 's'; if (isset($params[0])) $mult = $params[0];
            $stay_original = mb_stripos($matches[3],'!')!==false;
            if (isset($repls[$code]) && !$stay_original) $text = $repls[$code]['secs_str'];

            if ($code=='ep_content' && !$stay_original) {
                $textDB = $pageData['sec_content']; if (mb_strlen($textDB)!=0) $text = $textDB;
                $hint = 'Основной текст';
            }
            if ($code=='ep_namefull' && !$stay_original) {
                $textDB = $pageData['sec_namefull']; if (mb_strlen($textDB)!=0) $text = $textDB;
                $hint = 'Основной заголовок';
            }

            $tag = $mult=='m'?'div':'span';
            if ($editMode != null ? $editMode : shp::$editMode)
                return "<$tag class=\"ss_edit\" data-code=\"$code\" data-mult=\"$mult\" ".($hint!=''?"data-hint=\"$hint\"":'').">$text</$tag>";
            else return $text;
        },$html);
    }

}