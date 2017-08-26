<?php

abstract class VisualThemeAbstract
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
        if (mb_strpos($format,'l')!==false) {
            $format = str_replace('l',self::weekdays[date('w',$dt)],$format);
        }
        if (mb_strpos($format,'F')!==false) {
            $format = str_replace('F',self::months[date('n',$dt)],$format);
        }
        return date($format,$dt);
    }

    public static function toTel($string) {
        $string = preg_replace('/\D/u','',$string);
        if (mb_strpos($string, '8') === 0) {
            $string = '7'.mb_substr($string,1);
        }
        if (mb_strlen($string)>10 && mb_strpos($string, '+') !== 0) {
            $string = '+'.$string;
        }
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
            case 1: return str_replace('\'','&apos;',$pageData[$field]); //&#039;
            case 2: return str_replace('"','&quot;',$pageData[$field]); //&#034;
        }
        return $pageData[$field];
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
        self::replaceStaticHolders($html,$pageData,$editMode);
        return $html;
    }

    /** Обработчик плейсхолдера. Применение шаблона для прямых потомков
     * @param $pageData
     * Обязательный. массив с данными
     * @param $editMode
     * Обязательный. режим редактирования
     * @param $text
     * Обязательный. Заменяемый в шаблоне текст
     * @param $template
     * Обязательный. Шаблон
     * @param $howchild
     * Как сортировать потомков
     * 1 - по созданию
     * 2 - с новых
     * 3 - со старых
     * @return false|string
     */
    public static function _ph_tmpl_children(&$pageData,$editMode,$text,$template,$howchild=3){
        /* @var $sql pgdb */
        /* @var $page PageUnit */
        global $sql,$page;
        $html = '';
        $query = sprintf ('select * from cms_sections where sec_parent_id=%d '.($editMode?'':'and sec_enabled and now()>sec_from').' order by '.$page->_howchildToOrder($howchild),
            $pageData['section_id']);
        $sections = $sql->query_all($query);
        if ($sections!==false) foreach ($sections as $secData) {
            $childHtml = file_get_contents($template.'.shtm',true);
            self::replaceStaticHolders($childHtml,$secData,$editMode);
            $html .= $childHtml;
        }
        return $html;
    }

    /** Обработчик плейсхолдера. Применение php-шаблона для прямых потомков
     * @param $pageData
     * Обязательный. массив с данными
     * @param $editMode
     * Обязательный. режим редактирования
     * @param $text
     * Обязательный. Заменяемый в шаблоне текст
     * @param $template
     * Обязательный. Шаблон
     * @param int $howchild
     * Как сортировать потомков
     * 1 - по созданию
     * 2 - с новых
     * 3 - со старых
     * @param string $mode
     * Режим работы:
     * a - один за одним, запуск для каждой сущности
     * f - общий запуск, foreach необходимо выполнять вручную
     * @return false|string
     */
    public static function _ph_tmpl_children_e(&$pageData,$editMode,$text,$template,$howchild=3,$mode = 'a'){
        /* @var $sql pgdb */
        /* @var $page PageUnit */
        global $sql,$page;
        $html = '';
        $query = sprintf ('select * from cms_sections where sec_parent_id=%d '.($editMode?'':'and sec_enabled and now()>sec_from').' order by '.$page->_howchildToOrder($howchild),
            $pageData['section_id']);
        $sections = $sql->query_all($query);

        $execIntoScope = function($template,$data){
            extract($data,EXTR_PREFIX_SAME,'new_');
            ob_start();
            require('akcms/u/template/parts/' . $template . '.php');
            return ob_get_clean();
        };

        if ($sections===false) {
            $sections = [];
        }
        if ($mode==='f') {
            $childHtml = $execIntoScope($template, array(
                'pt' => &$pageData,
                'lst' => &$sections,
                'editMode' => $editMode,
                'text' => $text,
            ));
            self::replaceStaticHolders($childHtml, $pageData, $editMode);
            $html .= $childHtml;
        } elseif ($mode==='a') {
            $last = count($sections)-1;
            $k = 0;
            foreach ($sections as $secData) {
                $childHtml = $execIntoScope($template, array(
                    'pt' => &$pageData,
                    'ct' => &$secData,
                    'is_first' => $k === 0,
                    'is_last' => $k === $last,
                    'k' => $k++,
                ));
                self::replaceStaticHolders($childHtml, $secData, $editMode);
                $html .= $childHtml;
            }
        }
        if ($pageData['section_id']==37) var_log($pageData);
        return $html;
    }


    /** Обрабатывает редактируемые поля в шаблоне
     * @param $html
     * @param $pageData
     * должен содержать массив с полями: section_id, sec_url_full, sec_content, sec_namefull, sec_imgfile, sec_from
     * @param $editMode bool
     * Режим редактирования, если не указан используется shp::$editMode
     */
    public static function replaceStaticHolders(&$html, &$pageData, $editMode = null){
        $editMode = $editMode !== null ? $editMode : shp::$editMode;
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
        $html=preg_replace_callback('~\{#_(\w+):([^:#]+)(:[^#]*?)*#\}~usU',function($matches) use (&$pageData,$editMode){
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
        $html=preg_replace_callback('~\{#(ep|eg):([^:#]+)(:[^#]+?)#(?|\/\}(.*)\{\/#\1:\2(?::[^#])?#\}|})~usU',function($matches) use (&$repls,&$pageData,$editMode){
            $textFound = false;
            $text = ''; if (isset($matches[4])) $text = $matches[4];
            $code = $matches[1].'_'.$matches[2];
            $params = explode(':',trim($matches[3],':'));
            $hint = ''; if (isset($params[1])) $hint = $params[1];
            $mult = 's'; if (isset($params[0])) $mult = $params[0];
            $stay_original = mb_stripos($matches[3],'!')!==false;
            if (isset($repls[$code]) && !$stay_original) {
                $text = $repls[$code]['secs_str'];
                $textFound = true;
            }

            if ($code==='ep_content' && !$stay_original) {
                $textDB = $pageData['sec_content']; if ($textDB !== '') {
                    $text = $textDB;
                    $textFound = true;
                }
                $hint = 'Основной текст';
            }
            if ($code==='ep_namefull' && !$stay_original) {
                $textDB = $pageData['sec_namefull']; if ($textDB !== '') {
                    $text = $textDB;
                    $textFound = true;
                }
                $hint = 'Основной заголовок';
            }

            $tag = $mult=='m'?'div':'span';
            if ($editMode) return "<$tag class=\"ss_edit ".($textFound?'':'textNotFound')."\" data-edt-uri=\"$pageData[sec_url_full]\" data-code=\"$code\" data-mult=\"$mult\" ".($hint!==''?"data-hint=\"$hint\"":'').">$text</$tag>";
            else return $text;
        },$html);
    }

}