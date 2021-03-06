<?php

abstract class VisualThemeAbstract
{
    const weekdays = array('Воскресенье','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота');
    const weekdaysShort = array('вс','пн','вт','ср','чт','пт','сб');
    const months_rod = array('','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
    const months = array('','январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь','декабрь');
    const monthsShort = array('','янв','фев','мар','апр','мая','июн','июл','авг','сен','окт','ноя','дек');

    /**
     * 7 июля в 14:17         - j F в H:i
     *
     * 7 июл в 14:17         - j M в H:i
     *
     * Пятница, 7 янв 2017г. - l, j M Y г.
     *
     * l (строчная 'L')	Полное наименование дня недели	от Воскресенье до Суббота
     *
     * k (строчная 'L')	Полное наименование дня недели	от вс до сб
     *
     * F Полное наименование месяца в родительном подеже, например, января или марта	от января до декабря
     *
     * f Полное наименование месяца в именительном подеже, например, январь или март	от январь до декабрь
     *
     * M Сокращенное наименование месяца, 3 символа	от янв до дек
     *
     * E - год, как Y, если год отличный от текущего
     *
     * @param $format
     * @param $dt
     * @param null $formatToday
     * @return false|string
     */
    public static function dateRus($format,$dt,$formatToday = null) {
        if (!is_numeric($dt)) $dt = strtotime($dt);
        if (
            $formatToday !== null &&
            date('Y',$dt)==date('Y') &&
            date('n',$dt)==date('n') &&
            date('j',$dt)==date('j')
        ) {
            $format = $formatToday;
        }
        if (mb_strpos($format,'l')!==false) {
            $format = str_replace('l',self::weekdays[date('w',$dt)],$format);
        }
        if (mb_strpos($format,'k')!==false) {
            $format = str_replace('k',self::weekdaysShort[date('w',$dt)],$format);
        }
        if (mb_strpos($format,'F')!==false) {
            $format = str_replace('F',self::months_rod[date('n',$dt)],$format);
        }
        if (mb_strpos($format,'f')!==false) {
            $format = str_replace('f',self::months[date('n',$dt)],$format);
        }
        if (mb_strpos($format,'M')!==false) {
            $format = str_replace('M',self::monthsShort[date('n',$dt)],$format);
        }
        if (mb_strpos($format,'E')!==false) {
            $format = str_replace('E',date('Y',$dt)!=date('Y')?'Y':'',$format);
        }
        return date($format,$dt);
    }

    /** number_format
     * @param $value
     * @param int $decimals
     * @return string
     */
    public static function numberFormat($value,$decimals = 0) {
        return number_format($value,$decimals,'.',' ');
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

    /*** Строит хлебные крошки внутри ul
     * @param $pagePath
     * массив $page->getBreadcrumbs()
     * @param bool $showMain
     * добавить первым <li><a href="/" title="Главная">Главная</a></li>
     * @param int $showLast
     * Отображать последний элемент
     *
     *  0 - не отображать
     *
     *  1 - активный li, внутри ссылка
     *
     *  2 - активный li, внутри текст
     *
     *  3 - активный li, внутри h1
     *
     * -1 - активный предпоследний, ссылкой. последний не отображается
     *
     * @return string
     */
    public static function buildBreadcrumbs_links($pagePath, $showMain=true, $showLast=1, $skipIds = [])
    {
        $path = array();
        $i=0; $count = count($pagePath);
        if ($showMain) $path[] = '<li><a href="/" title="Главная">Главная</a></li>';
        foreach ($pagePath as $pageItem) {
            if (in_array((int)$pageItem['section_id'], $skipIds,true)) {
                --$count;
                continue;
            }
            //var_dump__($count,$i,$pageItem);

            if ($i===$count-1) {
                if ($showLast>0) {
                    $link = $pageItem['sec_nameshort'];
                    if ($showLast === 1) $link = sprintf('<li class="active"><a href="/%1$s" title="%2$s">%2$s</a></li>',
                        $pageItem['sec_url_full'],
                        $pageItem['sec_nameshort']
                    );
                    if ($showLast === 2) $link = '<li class="active">' . $pageItem['sec_nameshort'] . '</li>';
                    if ($showLast === 3) $link = '<li class="active"><h1>' . $pageItem['sec_nameshort'] . '</h1></li>';
                    $path[] = $link;
                }
            } elseif ($showLast===-1 && $i===$count-2) {
                $path[] = sprintf('<li class="active"><a href="/%1$s">%2$s</a></li>',
                    $pageItem['sec_url_full'],
                    $pageItem['sec_nameshort']
                );
            } else {
                $path[] = sprintf('<li><a href="/%1$s">%2$s</a></li>',
                    $pageItem['sec_url_full'],
                    $pageItem['sec_nameshort']
                );
            }
            ++$i;
        }
        return implode($path);

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

    /** Обработчик плейсхолдера. Вывод текста
     * @param $pageData
     * Обязательный. массив с данными
     * @param $editMode
     * Обязательный. режим редактирования
     * @param $text
     * Обязательный. Заменяемый в шаблоне текст
     * @param $field
     * Обязательный. поле из шаблоны
     * @param int $quote
     * Формат кавычек.
     * 0 - не экранировать
     * 1 - одинарные
     * 2 - двойные
     * @param int $cnt
     * Длина текста
     * @return false|string
     */
    public static function _ph_text_trunc(&$pageData,$editMode,$text,$field,$quote = 0,$cnt = 200){
        $txt = $pageData[$field];
        if ($text==='' && $field==='sec_contshort') $txt = $pageData['sec_content'];
        //&nbsp;
        $txt = str_replace('&nbsp;',' ',$txt);
        $txt = mb_trim(strip_tags($txt),'\s\n');
        $txt = GetTruncText(html_entity_decode($txt),$cnt);
        switch ($quote) {
            case 1: return str_replace('\'','&apos;',$txt); //&#039;
            case 2: return str_replace('"','&quot;',$txt); //&#034;
        }
        return $txt;
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
     * @throws DBException
     */
    public static function _ph_tmpl_children(&$pageData,$editMode,$text,$template,$howchild=3,$limit=0,$sec_id=-1,$skipthis='no'){
        /* @var $sql pgdb */
        /* @var $page PageUnit */
        global $sql,$page;

        $html = '';
        $query = sprintf ('select * from cms_sections where sec_parent_id=%d '.($editMode?'':'and sec_enabled and now()>sec_from').
            ($skipthis==='t'||$skipthis===true?' AND section_id<>'.$sql->d($pageData['section_id']):'').
            ' order by '.$page->_howChildrenOrder($howchild),
            $sql->d((int)$sec_id===-1?$pageData['section_id']:$sec_id));
        if ($limit>0) $query.=' LIMIT '.$sql->d($limit);

        $sections = $sql->query_all($query);
        if ($sections!==false) foreach ($sections as $secData) {
            $childHtml = file_get_contents($template.'.shtm',true);
            $isEdit = $page->inEditCan && PageOutACL::getInstance($secData)->hasRight(); //Персональные права этой страницы
            self::replaceStaticHolders($childHtml,$secData,$isEdit);
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
     * Как сортировать потомков:
     * 1 - по созданию
     * 2 - с новых
     * 3 - со старых
     * @param int $limit
     * @param int $sec_id
     * @param string $mode
     * Режим работы:
     * a - один за одним, запуск для каждой сущности
     * f - общий запуск, foreach необходимо выполнять вручную
     * @return false|string
     * @throws DBException
     */
    public static function _ph_tmpl_children_e(&$pageData,$editMode,$text,$template,$howchild=3,$limit=0,$sec_id=-1,$mode = 'a'){
        /* @var $sql pgdb */
        /* @var $page PageUnit */
        global $sql,$page;
        $html = '';
        $query = sprintf ('select * from cms_sections where sec_parent_id=%d '.($editMode?'':'and sec_enabled and now()>sec_from').' order by '.$page->_howChildrenOrder($howchild),
            $sql->d((int)$sec_id===-1?$pageData['section_id']:$sec_id));
        if ($limit>0) $query.=' LIMIT '.$sql->d($limit);
        $sections = $sql->query_all($query);

        $execIntoScope = function($template,$data){
            if (is_array($data)) extract($data,EXTR_PREFIX_SAME,'new_');
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
                $isEdit = $page->inEditCan && PageOutACL::getInstance($secData)->hasRight(); //Персональные права этой страницы
                $childHtml = $execIntoScope($template, array(
                    'pt' => &$pageData,
                    'ct' => &$secData,
                    'is_first' => $k === 0,
                    'is_last' => $k === $last,
                    'k' => $k++,
                    //'editMode' => $editMode,
                    'secEditMode' => $isEdit,
                ));
                self::replaceStaticHolders($childHtml, $secData, $isEdit);
                $html .= $childHtml;
            }
        }
        return $html;
    }

    private static $sectionStrings = [];

    /** Возвращает ассоциированные сохраненные данные строк раздела
     * @param int $secId
     * @return mixed
     * @throws DBException
     */
    protected static function &getSectionStrings($secId = 0) {
        /* @var $sql pgdb */
        global $sql;
        if (isset(self::$sectionStrings[$secId])) return self::$sectionStrings[$secId];

        $sectionIds = [0,$secId];

        $repls = array();
        $ss_data = $sql->query_all('SELECT sec_id,secs_id,secs_code,secs_str FROM cms_sections_string WHERE sec_id=ANY(' . $sql->a_d($sectionIds).')');
        if ($ss_data!=false)
            foreach ($ss_data as $item)
                $repls[($item['sec_id']==0?'eg':'ep').'_'.$item['secs_code']] = $item;

        self::$sectionStrings[$secId] = $repls;
        return self::$sectionStrings[$secId];
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
     * @param string $mult
     * Тип редактора
     * s - однострочный полный html
     * m - многострочный полный html
     * l - простая строка без переносов
     * @param string $hint
     * Подсказка в режиме редактирования
     * @param string $debug
     * символ ! отлючает подстановнку данных из базы
     * @return false|string
     * @throws DBException
     */
    public static function _ph_editable(&$pageData,$editMode,$text,$field,$mult = 's',$hint = '',$debug = ''){
        $stay_original = mb_stripos($debug,'!')!==false;
        $classes = [];
        $textFound = false;

        if ($field==='ep_content' && !$stay_original) {
            $textDB = $pageData['sec_content']; if ($textDB !== '') {
                $text = $textDB;
                $textFound = true;
            }
            $hint = 'Основной текст';
        }
        elseif ($field==='ep_namefull' && !$stay_original) {
            $textDB = $pageData['sec_namefull']; if ($textDB !== '') {
                $text = $textDB;
                $textFound = true;
                if ($pageData['sec_enabled']==='f') $classes[] = 'ss_edit_secDisabled';
                if (strtotime($pageData['sec_from'])>time()) $classes[] = 'ss_edit_secInFuture';
            }
            $hint = 'Основной заголовок';
        } else {
            $repls = &self::getSectionStrings($pageData['section_id']);
            if (!$stay_original && isset($repls[$field])) {
                $text = $repls[$field]['secs_str'];
                $textFound = true;
            }
            if (mb_strpos($field,'eg_')===0) {
                // Должны быть права на первую страницу
                $pg1 = array(
                    'section_id' => 1,
                    'sec_ids_closest' => '{1}'
                );
                $editMode = $editMode && PageOutACL::getInstance($pg1)->hasRight();
            };
        }

        $tag = $mult==='m'?'div':'span';
        if (!$textFound) $classes[] = 'textNotFound';
        if ($editMode) return
            "<$tag class=\"ss_edit ss_edit_$mult ".implode(' ',$classes)."\" data-edt-uri=\"$pageData[sec_url_full]\" data-code=\"$field\" data-mult=\"$mult\" ".
            ($hint!==''?"data-hint=\"$hint\"":'').">$text</$tag>";
        else return $text;
    }

    /** Обрабатывает редактируемые поля в шаблоне
     * @param $html
     * @param $pageData
     * должен содержать массив с полями: section_id, sec_url_full, sec_content, sec_namefull, sec_imgfile, sec_from, sec_enabled
     * @param $editMode bool
     * Режим редактирования, если не указан используется shp::$editMode
     */
    public static function replaceStaticHolders(&$html, &$pageData, $editMode = null){
        $editMode = $editMode ?: shp::$editMode;
        /* @var $sql pgdb */
        global $sql;

        /* Вызывает функции из класса VisualTheme с префиксом _ph_,
           а для полей ep_ и eg_ (локальные параметры и глобальные) - редактор _ph_editable
         * 1 - тип
         * 2 - ключ
         * 3 - параметры
         * 3_0 - mult
         * 3_1 - hint
         * 4 - контент
         */
        $html=preg_replace_callback('~\{#(ep|eg|_\w+):([^:#]+)(:[^#]+?)?#(?|\/\}(.*)\{\/#\1:\2(?::[^#])?#\}|})~usU',function($matches) use (&$pageData,$editMode){
            $funct = $matches[1];
            $field = $matches[2];
            if ($funct==='ep' || $funct==='eg') {
                $field = $funct.'_'.$field;
                $funct = '_editable';
            }
            $params = isset($matches[3]) && $matches[3]!==''?explode(':',trim($matches[3],':')):[];
            $text = ''; if (isset($matches[4])) $text = $matches[4];
            return call_user_func_array('VisualTheme::_ph'.$funct,array_merge(array(&$pageData,$editMode,&$text,$field),$params));
        },$html);
    }

}