<?php
class functs {
    static function strHasValue($str) {
        return $str!==null && $str!=='';
    }

    static function strValue($str) {
        return $str===null?'':$str;
    }

    static function implodeIgnoreEmptyNull($glue , array $pieces){
        return implode($glue, array_filter($pieces,function ($element){
            return $element!==null && $element !='';
        }));
    }

    static function prettySize($size,array $unit=array('б.','Кб.','Мб.','Гб.','Тб.','Пб.'))
    {
        if ($size===0) return '0 '.$unit[0];
        return round($size/ (1024 ** $i = floor(log($size, 1024))),2).' '.$unit[$i];
    }
}

function mb_trim($string, $trim_chars = '\s'){
    return preg_replace('/^['.$trim_chars.']*(?U)(.*)['.$trim_chars.']*$/um', '\\1',$string);
}

function makePager($pager_Count, $pager_pgSize, $pager_pgNum, $urlstr, $NoFirstNum=true) {
	$html = '<table cellspacing="0" cellpadding="0" align="center"><tbody><tr>'; 
	if ($pager_Count<=$pager_pgSize) return '';
	$pagesCount = ceil($pager_Count/$pager_pgSize);
	$pageFromD = ($pager_pgNum-4<1)?1:$pager_pgNum-4;
	$pageToD = ($pageFromD+8>$pagesCount)?$pagesCount:$pageFromD+8;
	if ($pager_pgNum>1)
		$html .= ' <td valign="middle" align="center" class="pgrprev"><a title="Предыдущая" href="'.str_replace('{pg}',
		($NoFirstNum && $pager_pgNum-1==1?'':$pager_pgNum-1),$urlstr).'">Предыдущая</a></td>';
	
	$html .= '<td class="pgrl">&nbsp;</td><td valign="middle" align="center" class="pgrc">';

	$pagesArray = array();
	$pageFromDM10 = floor(($pageFromD-1)/10)*10;
	for ($i=$pageFromDM10;$i>=$pageFromDM10-20 && $i>=1;$i-=10) $pagesArray[] = $i; 
	$pageFromDM100 = floor(($pageFromDM10-21)/100)*100;
	for ($i=$pageFromDM100;$i>=$pageFromDM100-100 && $i>=1;$i-=100) $pagesArray[] = $i; 
	$pagesArray = array_reverse($pagesArray);
	
	for ($i=$pageFromD;$i<=$pageToD;$i++) $pagesArray[] = $i;
	$pageFromD10 = ceil(($pageToD+1)/10)*10;
	for ($i=$pageFromD10;$i<=$pageFromD10+20 && $i<=$pagesCount;$i+=10) $pagesArray[] = $i;
	$pageFromD100 = ceil(($pageFromD10+21)/100)*100;
	for ($i=$pageFromD100;$i<=$pageFromD100+100 && $i<=$pagesCount;$i+=100) $pagesArray[] = $i;
	foreach ($pagesArray as $i) {
		if ($i!=$pager_pgNum) {
			$html .= '<a title="Страница '.$i.'" href="'.str_replace('{pg}',($NoFirstNum && $i==1?'':$i),$urlstr).'" class="pgrdgt">'.$i.'</a>';
		} else 
			$html .= '<a title="Страница '.$i.'" class="pgrdgtcurr">'.$i .'</a>';
	}
	
	$html .= '<div class="clrbth"></div></td><td class="pgrr">&nbsp;</td>';	
	
	if ($pager_pgNum<$pagesCount)
		$html .= '<td valign="middle" align="center" class="pgrnext"><a title="Следующая" href="'.str_replace('{pg}',$pager_pgNum+1,$urlstr).'">Следующая</a></td>';
		
	$html .= '</tr></tbody></table>'; 
	return $html;
}

/** Build select options from array
 * @param $data
 * @param $colimnVal
 * value column
 * @param $columnName
 * title column
 * @param int $valSeected
 * selected value
 * @param null $columnStyle
 * css data column
 * @param array $ignoreVals
 * list of ignored values
 * @return string
 */
function html_arrIdValPairs_toOptions($data,$colimnVal,$columnName,$valSeected=0,$columnStyle=null,$ignoreVals=[]) {
	$res = '';
	if ($data!==false) foreach ($data as $dataItem) {
	    if (!in_array($dataItem[$colimnVal],$ignoreVals)) {
            $res .= '<option value="' . $dataItem[$colimnVal] . '" ' . ($dataItem[$colimnVal] == $valSeected ? 'selected' : '') . (($columnStyle != null && isset($dataItem[$columnStyle])) ? ' style="' . $dataItem[$columnStyle] . '"' : '') . '>' . $dataItem[$columnName] . '</option>';
        }
	}
	return $res;
}

/**
 * @param $text
 * @param string $to
 * @param bool $notify
 * @param bool $web
 * @param int $parseMode
 * @return bool
 */
function sendTelegram($text,$to=null,$notify = true,$web = false,$parseMode = 0) {
    global $cfg;
    if ($to===null && isset($cfg['telegramId'])) $to = $cfg['telegramId'];
    if ($to===null) $to = '203405254';
    $auth = '276469341:AAE1A1kt1APsm8WsmxCvgFiOOc0BAnVaOZg';
    $params = array(
        'chat_id'=>$to,
        'text'=>$text,
        'disable_notification'=>$notify?'0':'1',
        'disable_web_page_preview',$web?'0':'1',
    );
    if ($parseMode===1) $params['parse_mode'] = 'Markdown';
    elseif ($parseMode===2) $params['parse_mode'] = 'HTML';
    $url = 'https://api.telegram.org/bot'.$auth.'/sendMessage?'.http_build_query($params);

    $headers = array(
        "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/5.0.1",
        "Accept: *"."/"."*",
        "Accept-Language: en-US,en;q=0.8,ru;q=0.6",
        "Accept-Encoding: gzip, deflate,sdch",
        "Connection: keep-alive",
    );
/*
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_HEADER => 0,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "gzip",
        //CURLOPT_COOKIESESSION => true,
        //CURLOPT_COOKIEJAR => 'cookie.txt',
        //CURLOPT_COOKIEFILE => 'cookie.txt',
        //CURLOPT_PROXY => '200.195.23.12:3128',
    ));
    $res = curl_exec($ch);
    curl_close($ch);
*/
    $res = file_get_contents($url);
    $res = json_decode($res,true);
    return $res['ok']==true;
}

function var_dump_($var) {
	if (php_sapi_name()!=='cli') echo '<pre>';  #var_dump($var);
	var_dump(...func_get_args());
	if (php_sapi_name()!=='cli') echo '</pre>';
}

function var_dump__($var) {
    var_dump_(...func_get_args());
    exit();
}

function var_export_($var) {
	if (php_sapi_name()!=='cli') echo '<pre>';  #var_dump($var);
    var_export(...func_get_args());
	if (php_sapi_name()!=='cli') echo '</pre>';
}

function var_export__($var) {
    var_export_(...func_get_args());
    exit();
}

function var_log_js($var) {
    $var = func_get_args();
    echo '<script>console.log('.json_encode(count($var)==1?$var[0]:$var).');</script>';
}

function print_r_($var) {
	$out = preg_replace('/\s*\n\(/'," (", print_r(...array($var, true)));
	if (php_sapi_name()!=='cli') echo '<pre>'.htmlentities($out,ENT_COMPAT,'UTF-8').'</pre>'; else echo $out;
}

function print_r__($var) {
    print_r_(...func_get_args());
    exit();
}

function checkForm(&$var, &$checkRule, $permissionOk = true) {
	$result = array();
	if (!$permissionOk) $result[] = array('f'=>'!','s'=>'!');
	foreach ($checkRule as $rule)
	{
		if (!isset($var[$rule[0]]) or ($rule[1]=='.' and isset($var[$rule[0]])?$var[$rule[0]]=='':false))
		{ 
			$result[] = array('f'=>$rule[0],'s'=>'empty');
		}
		elseif (!empty($rule[1]) && $rule[1]!='.')
		{
			##var_dump('>',$rule[0],$rule[1],$rule[2]);
			##(isset($rule[2])) var_dump(preg_match($rule[2],$var[$rule[0]]));
			if (preg_match($rule[1],$var[$rule[0]])!==1) $result[] = array('f'=>$rule[0],'s'=>'wrong');
			elseif (isset($rule[2])?$rule[2]!=''?preg_match($rule[2],$var[$rule[0]])===1:false:false)
			$result[] = array('f'=>$rule[0],'s'=>'Wrong');
		}
	}
	return $result;
}

function checkFormAjax(&$var, &$checkRule, $permissionOk = true) {
	return checkForm($var, $checkRule, $permissionOk);
}

function checkFormAssoc(&$var, &$checkRule, $permissionOk = true) {
	$res = array();
	$data = checkForm($var, $checkRule, $permissionOk);
	foreach ($data as $val) { $res[$val['f']] = $val['s']; }
	return $res;
}

/* Преобразует ассоциативный массив в массив массивов для ajax */
function assocArray2KeyValue($arr) {
	$narr = array();
	foreach ($arr as $k=>$v) $narr[] = array('k'=>$k,'v'=>$v);
	return $narr;
}


function messagesToErrorArray($messages,$errors) {
	$narr = array();
	foreach ($errors as $k=>$v)
		$narr[] = array('f'=>'e_'.$k,'m'=>isset($messages[$k.'-'.$v])?$messages[$k.'-'.$v]:$v);
	return $narr;
}

Function dgtToChar($dgt) {
	return $dgt<10?$dgt:chr($dgt+87);
}

Function Str_($str,$cnt) { //Возвращает n-значное число
  if ($cnt>strlen($str))
   return str_repeat('0',$cnt-strlen($str)).$str;
  else return $str;
}

Function GetTruncText($str,$cnt,$p3after = true) // Возвращает часть строки. Обрезает строку
												//  до указанной длины или меньlе по последнему слову
{
	if (mb_strlen($str)<=$cnt) return $str;
	$str = mb_substr($str,0,$cnt);
	$str = mb_substr($str,0,mb_strrpos($str,' '));
	$lstchr = mb_substr($str,mb_strlen($str)-1,1);
	if (in_array($lstchr,array('!',',','.',';'))) $str = mb_substr($str,0,mb_strlen($str)-1);
	return $str.($p3after?'... ':'');
}

Function GetTruncString($str,$cnt,$p3after = true) // Возвращает часть строки. Обрезает строку
    //  до указанной длины и всё
{
    if (mb_strlen($str)<=$cnt) return $str;
    $str = mb_substr($str,0,$cnt);
    return $str.($p3after?'… ':'');
}

Function DtTmToDtStr($dttm,$y=true) // Конвертирует 2005-05-06 в 6 мая 2005г.
{
	$MonthStr = array("января","февраля","марта","апреля","мая","июня","июля","августа","сентября","октября","ноября","декабря");
	$Year  = (int)mb_substr($dttm,0,4);
	$Month = (int)mb_substr($dttm,5,2);
	$Day   = (int)mb_substr($dttm,8,2);
	return $Day.' '.$MonthStr[$Month-1].($y?' '.$Year.' г.':'');
}

Function DtTmToDtStrF($dttm) // Конвертирует 2005-05-06 15:01:25 в 6 мая 2005г. 15:01:25
{
	return DtTmToDtStr($dttm).' '.substr($dttm,11,8);
}

function DtDbFormatDate($dateFormat,$dbDate) {
	$timestamp = mktime(0, 0, 0,  substr($dbDate,5,2),substr($dbDate,8,2), substr($dbDate,0,4));
	return date($dateFormat,$timestamp);
}

function RightWordForm($dgt,$wordarr)	// Возвращает верную форму слова в 
{										// соответсвии с правилами русского языка
										// 1 час  2 часа  5 часов
	if ((int)($dgt%100/10)===1) return $wordarr[2];
	switch ($dgt%10) {
		case 1:
			return $wordarr[0];
		case 2: case 3: case 4:
			return $wordarr[1];
		case 5: case 6: case 7: case 8: case 9: case 0:
			return $wordarr[2];
	}
}

function intervalToWords($sec) {
	$min = (int)($sec/60);
	$min10 = (int)($sec/600);
	$hrs = (int)($min/60);
	$days = (int)($hrs/24);
	$weeks = (int)($days/7);
	$mnts = (int)($days/30);
	$years = (int)($days/365);
	if ($years!=0) return $years.' '.RightWordForm($years,array('год','год','лет'));
	if ($mnts!=0) return $mnts.' '.RightWordForm($mnts,array('месяц','месяца','месяцев'));
	if ($weeks!=0) return $weeks.' '.RightWordForm($weeks,array('неделю','недели','недель'));
	if ($days!=0) return $days.' '.RightWordForm($days,array('день','дня','дней'));
	if ($hrs!=0) return $hrs.' '.RightWordForm($hrs,array('час','часа','часов'));
	if ($min10!=0) return $min10.'0 '.RightWordForm($min10,array('минут','минут','минут'));
	if ($min!=0) return $min.' '.RightWordForm($min,array('минуту','минуты','минут'));
	return $sec.' '.RightWordForm($sec,array('секунду','секунды','секунд'));
}

function intervalToWordsExact($sec) {
    $years = intdiv($sec,365*86400); $sec = $sec % (365*86400);
    $mnts = intdiv($sec,31*86400); $sec = $sec % (31*86400);
    $weeks = intdiv($sec,7*86400); $sec = $sec % (7*86400);
    $days = intdiv($sec,86400); $sec = $sec % 86400;
    $hrs = intdiv($sec,3600); $sec = $sec % 3600;
    $min = intdiv($sec,60); $sec = $sec % 60;
    $text = [];
    if ($years>1) $text[] = $years.' '.RightWordForm($years,array('год','год','лет'));
    if ($mnts>1) $text[] =  $mnts.' '.RightWordForm($mnts,array('месяц','месяца','месяцев'));
    if ($weeks>1) $text[] =  $weeks.' '.RightWordForm($weeks,array('неделя','недели','недель'));
    if ($days>1) $text[] =  $days.' '.RightWordForm($days,array('день','дня','дней'));
    if ($hrs>1) $text[] =  $hrs.' '.RightWordForm($hrs,array('час','часа','часов'));
    if ($min>1) $text[] =  $min.' '.RightWordForm($min,array('минута','минуты','минут'));
    if ($sec>1) $text[] =  $sec.' '.RightWordForm($sec,array('секунда','секунды','секунд'));
    return implode(' ',$text);
}


function mb_strpos_all($haystack, $needle) {
    $s = 0;
    $i = 0;
    while(is_int($i)) {

        $i = mb_strpos($haystack, $needle, $s);

        if(is_int($i)) {
            $aStrPos[] = $i;
            $s = $i + mb_strlen($needle);
        }
    }

    if(isset($aStrPos)) {
        return $aStrPos;
    } else {
        return false;
    }
}

if (!function_exists('mb_ucfirst') && extension_loaded('mbstring'))
{
    /**
     * mb_ucfirst - преобразует первый символ в верхний регистр
     * @param string $str - строка
     * @param string $encoding - кодировка, по-умолчанию UTF-8
     * @return string
     */
    function mb_ucfirst($str, $encoding='UTF-8')
    {
        $str = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding).
            mb_substr($str, 1, mb_strlen($str), $encoding);
        return $str;
    }
}

/**
 * Возвращает сумму прописью
 * @author runcore
 * @uses morph(...)
 */
function num2str($num) {
    $nul='ноль';
    $ten=array(
        array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
        array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
    );
    $a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
    $tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
    $hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
    $unit=array( // Units
        array('копейка' ,'копейки' ,'копеек',	 1),
        array('рубль'   ,'рубля'   ,'рублей'    ,0),
        array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
        array('миллион' ,'миллиона','миллионов' ,0),
        array('миллиард','милиарда','миллиардов',0),
    );
    //
    list($rub,$kop) = explode('.',sprintf("%015.2f", (float)$num));
    $out = array();
    if ((int)$rub >0) {
        foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
            if (!(int)$v) continue;
            $uk = count($unit)-$uk-1; // unit key
            $gender = $unit[$uk][3];
            list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
            // mega-logic
            $out[] = $hundred[$i1]; # 1xx-9xx
            if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
            else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
            // units without rub & kop
            if ($uk>1) $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
        } //foreach
    }
    else $out[] = $nul;
    $out[] = morph((int)$rub, $unit[1][0],$unit[1][1],$unit[1][2]); // rub
    $out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
    return trim(preg_replace('/ {2,}/', ' ', implode(' ',$out)));
}

function morph($n, $f1, $f2, $f5) {
    $n = abs((int)$n) % 100;
    if ($n>10 && $n<20) return $f5;
    $n = $n % 10;
    if ($n>1 && $n<5) return $f2;
    if ($n==1) return $f1;
    return $f5;
}

function load_filecheck($filepath,$use_include=false) // Чтение файла с проверкой существования
{
	$str = @file_get_contents($filepath, $use_include);
	if ($str===false) return '['.$filepath.']';
	else return $str;
}

function TextToHTMLess($text)   // Выводит весь текст как есть, включая HTML
{
	$search=array(
	"[&]",
	"[\"]",
	"[©]",
	"[®]",
	"[™]",
	"[€]",
	"[”]",
	"[“]",
	"[«]",
	"[»]",
	"[>]",
	"[<]",
	);
	$replace=array(
	"&amp;",
	"&quot;",
	"&copy;",
	"&reg;",
	"&trade;",
	"&euro;",
	"&bdquo;",
	"&ldquo;",
	"&laquo;",
	"&raquo;",
	"&gt;",
	"&lt;",
	);

	return preg_replace($search,$replace,$text);
}

/*
	var ru2lt = {
		ru_str : "АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюя№ ",
		lt_str : [
			'A','B','V','G','D','E','E','ZH','Z','I','J','K','L','M','N','O','P','R','S','T','U','F',
			'H','C','CH','SH','SHH','','Y','','E','YU','YA',
			'a','b','v','g','d','e','e','zh','z','i','j','k','l','m','n','o','p','r','s','t','u','f',
			'h','c','ch','sh','shh','','y','','e','yu','ya','n','_'],
		t : function (inp) {
			var a = inp.split("");
			for (var i=0,aL=a.length;i<aL;i++) {var c = ru2lt.ru2en[a[i]]; a[i] = c==null?a[i]:c}
			var s =  a.join("")
			return s.replace(/[^0-9a-zA-Z_-]/g, '');
		},
		init:function() {
			ru2lt.ru2en = {};
			for(var i = 0,l = ru2lt.ru_str.length; i < l; i++)
			ru2lt.ru2en[ru2lt.ru_str.charAt(i)] = ru2lt.lt_str[i];
		}
	};ru2lt.init();
*/
Function strToURL($str) {
	$ru_str_ = 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюя№ ';
	$lt_str = array(
		'A','B','V','G','D','E','E','ZH','Z','I','J','K','L','M','N','O','P','R','S','T','U','F',
		'H','C','CH','SH','SHH','','Y','','E','YU','YA',
		'a','b','v','g','d','e','e','zh','z','i','j','k','l','m','n','o','p','r','s','t','u','f',
		'h','c','ch','sh','shh','','y','','e','yu','ya','n','_'
	);
	$ru_str = preg_split('//u', $ru_str_, -1, PREG_SPLIT_NO_EMPTY);
	$str = str_replace($ru_str,$lt_str,$str);
	return preg_replace('/[^0-9a-zA-Z_-]/','',mb_strtolower($str));
}
Function Translit($str) // Транслитирует русскую строку
{
	$srch = array(	'А' ,'Б' ,'В' ,'Г' ,'Д' ,'Е' ,'Ё' ,'Ж' ,'З' ,'И' ,'Й' ,'К' ,'Л' ,'М' ,'Н' ,'О' ,
					'П' ,'Р' ,'С' ,'Т' ,'У' ,'Ф' ,'Х' ,'Ц' ,'Ч' ,'Ш' ,'Щ' ,'Ъ' ,'Ы' ,'Ь' ,'Э' ,'Ю' ,
					'Я' ,'а' ,'б' ,'в' ,'г' ,'д' ,'е' ,'ё' ,'ж' ,'з' ,'и' ,'й' ,'к' ,'л' ,'м' ,'н' ,
					'о' ,'п' ,'р' ,'с' ,'т' ,'у' ,'ф' ,'х' ,'ц' ,'ч' ,'ш' ,'щ' ,'ъ' ,'ы' ,'ь' ,'э' ,
					'ю' ,'я' ,'“' ,'”' ,'«' ,'»');#',!.: \"+
	$rpls = array(	'A' ,'B' ,'V' ,'G' ,'D' ,'E' ,'YO','ZH' ,'Z' ,'I' ,'J' ,'K' ,'L' ,'M' ,'N' ,'O' ,
					'P' ,'R' ,'S' ,'T' ,'U' ,'F' ,'H' ,'TS','CH','SH','SH','\'','I' ,'\'','E' ,'JU',
					'YA','a' ,'b' ,'v' ,'g' ,'d' ,'e' ,'yo','zh' ,'z' ,'i' ,'j' ,'k' ,'l' ,'m' ,'n' ,
					'o' ,'p' ,'r' ,'s' ,'t' ,'u' ,'f' ,'h' ,'ts','ch','sh','shh','\'','i' ,'\'','e' ,
					'ju','ya','"' ,'"' ,'"' ,'"');
	return str_replace($srch,$rpls,$str);
}

Function TranslitFrmFld($str)
{
	$srchcl = array('\'','+','*','#',',','!','?','.',':','"','+');
	$rplscl = '';
	$str = str_replace($srchcl,$rplscl,Translit($str));
	return $str ;
}

Function Title2Uri($str) {
	$str = preg_replace('/[^0-9a-z_-]/u','_',str_replace(' ','_',TranslitFrmFld(mb_strtolower($str))));
	$str = preg_replace('/[\_]{2,}/u','_',str_replace(' ','_',TranslitFrmFld(mb_strtolower($str))));
	return $str;
}

function getCoordsByAddress($addr)
{
	$ch = curl_init();
	$result = array(1,1);
	curl_setopt($ch, CURLOPT_URL, 'https://geocode-maps.yandex.ru/1.x/?geocode='.urlencode($addr).'&ll=38.985834,45.047493&spn=1,1&results=1&rspn=1&key=ANpUFEkBAAAAf7jmJwMAHGZHrcKNDsbEqEVjEUtCmufxQMwAAAAAAAAAAAAvVrubVT4btztbduoIgTLAeFILaQ==');
	curl_setopt($ch, CURLOPT_HEADER, 0);
	#curl_setopt($ch, CURLOPT_PROXY , '200.195.23.12:3128');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$xml = curl_exec($ch);
	curl_close($ch);
	if(preg_match("#<pos>([0-9\\.]*) ([0-9\\.]*)</pos>#i", $xml, $out)) 
	{
		$lng= (float)trim($out[1]);
		$lat= (float)trim($out[2]);
		if($lng>0 && $lat>0) 
		$result = array($lng,$lat);
		else 
		$result = array(-1,-1);
	}  else $result = array(-2,-2);
	return $result;  
}

function mb_str_pad($input, $length, $pad_str=' ', $type = STR_PAD_RIGHT)
{
    $input_len = mb_strlen($input);
    if ($length <= $input_len)
        return $input;
    $pad_str_len = mb_strlen($pad_str);
    $pad_len = $length - $input_len;
    if ($type == STR_PAD_RIGHT)
    {
        $repeat_times = ceil($pad_len / $pad_str_len);
        return mb_substr($input.str_repeat($pad_str, $repeat_times), 0, $length);
    }
    if ($type == STR_PAD_LEFT)
    {
        $repeat_times = ceil($pad_len / $pad_str_len);
        return mb_substr(str_repeat($pad_str, $repeat_times), 0, floor($pad_len)).$input;
    }
    if ($type == STR_PAD_BOTH)
    {
        $pad_len /= 2;
        $pad_amount_left = floor($pad_len);
        $pad_amount_right = ceil($pad_len);
        $repeat_times_left = ceil($pad_amount_left / $pad_str_len);
        $repeat_times_right = ceil($pad_amount_right / $pad_str_len);
        $padding_left = mb_substr(str_repeat($pad_str, $repeat_times_left), 0, $pad_amount_left);
        $padding_right = mb_substr(str_repeat($pad_str, $repeat_times_right), 0, $pad_amount_right);
        return $padding_left.$input.$padding_right;
    }
    trigger_error('utf8_str_pad: Unknown padding type ('.$type.')', E_USER_ERROR);
}

/** Консоль. Цвет
 *
 * 0 все атрибуты по умолчанию
 *
 * 1 жирный шрифт (интенсивный цвет)
 *
 * 2 полу яркий цвет (тёмно-серый, независимо от цвета)
 *
 * 4 выделение (ярко-белый, независимо от цвета) или подчеркивание
 *
 * 5 мигающий
 *
 * 7 реверсия (знаки приобретают цвет фона, а фон -- цвет знаков)
 *
 * 22 установить нормальную интенсивность
 *
 * 24 отменить подчеркивание
 *
 * 25 отменить мигание
 *
 * 27 отменить реверсию
 *
 * 30 чёрный цвет знаков
 *
 * 31 красный цвет знаков
 *
 * 32 зелёный цвет знаков
 *
 * 33 коричневый цвет знаков
 *
 * 34 синий цвет знаков
 *
 * 35 фиолетовый цвет знаков
 *
 * 36 цвет морской волны знаков
 *
 * 37 серый цвет знаков
 *
 * Фон:
 *
 * 40 чёрный цвет фона
 *
 * 41 красный цвет фона
 *
 * 42 зелёный цвет фона
 *
 * 43 коричневый цвет фона
 *
 * 44 синий цвет фона
 *
 * 45 фиолетовый цвет фона
 *
 * 46 цвет морской волны фона
 *
 * 47 серый цвет фона
 */
function _ls($code = '0'){
    //http://manpages.ubuntu.com/manpages/trusty/man4/console_codes.4.html
    //http://wiki.bash-hackers.org/scripting/terminalcodes
    //http://ascii-table.com/ansi-escape-sequences.php
    //http://ascii-table.com/ansi-escape-sequences-vt-100.php
	return "\e[".$code."m"; // \e = \x1b = \033
}

/**
 * @param $url Адрес
 * @param array $headers_add Доп. Заголовки
 * @param array $GET
 * @param array $POST
 * @param bool $followlocation разрешить редиректы
 * @param bool $cookies
 * @param string $cookiefile путь
 * @param bool|string $proxy false|строка прокси
 * @return array
 */
function _getUrlContent(
    $url,
    $headers_add = array(),
    $GET=array(),
    $POST=array(),
    $followlocation=true,
    &$cookies = false,
    $cookiefile='../cookie_file.txt',
    $proxy = false)
{
    /* @var $Cacher CacheController */
    global $Cacher;

    //$cacheKey = md5(serialize(func_get_args()));

    $res = '';
    $headers = array(
        'User-Agent'=>'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:50.0) Gecko/20100101 Firefox/50.0',
        'Accept'=>'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language'=>'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
        'Accept-Encoding'=>'gzip, deflate',
        'X-Compress'=>1,
        'Connection'=>'keep-alive',
    );
    $headers = array_merge($headers_add,$headers);
    $headers = array_merge($headers,$headers_add);
    $headers_raw = array();
    foreach($headers as $k=>$v) $headers_raw[] = $k.': '.$v;

    $url = str_replace(' ','%20',$url);
    if (count($GET)>0) $url .= '?'.http_build_query($GET);
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_HEADER => true,
        CURLOPT_HTTPHEADER => $headers_raw,
        CURLOPT_RETURNTRANSFER => true,
        #CURLOPT_VERBOSE => false,
        CURLOPT_ENCODING => "gzip",
        CURLOPT_COOKIEJAR => $cookiefile,
        CURLOPT_COOKIEFILE => $cookiefile,
        //CURLOPT_PROXY => '212.192.64.125:3128',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => $followlocation,
        //CURLOPT_SSL_VERIFYHOST => 2,
        //CURLOPT_CAINFO => '_scripts/thawtePrimaryRootCA.crt',
        CURLINFO_HEADER_OUT => true,
    ));
    if ($proxy!==false) {
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
    }
    if ($POST===true) { curl_setopt($ch, CURLOPT_POST, true); $POST = array();}
    if (count($POST)>0) curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($POST));
    $time_start = microtime(true);
    $output = curl_exec($ch);
    $headers_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($output, 0, $headers_len);
    if ($cookies!==false) {
        foreach (explode("\r\n",$headers) as $header)
            if (mb_stripos($header,'SET-COOKIE: ')===0) {
                $header = explode(';',mb_substr($header,12));
                $header = explode('=',$header[0]);
                if (count($header)==2) {
                    $cookies[$header[0]] = $header[1];
                    #toLog('cookie '.$header[0].'='.$header[1]);
                }
            }
    }
    $res = array(
        'redirects'     => curl_getinfo($ch,CURLINFO_REDIRECT_COUNT),
        'code'          => curl_getinfo($ch,CURLINFO_HTTP_CODE),
        'url'           => curl_getinfo($ch,CURLINFO_EFFECTIVE_URL),
        'url_orig'      => $url,
        'headers'       => array_filter(explode("\r\n\r\n", $headers),function($v){return trim($v)!='';}),
        'headers_out'   => curl_getinfo($ch, CURLINFO_HEADER_OUT),
        'headers_len'   => $headers_len,
        'time_start'    => $time_start,
        'time_duration' => microtime(true)-$time_start,
        'data'          => substr($output, $headers_len)
    );
    curl_close($ch);
    if (count($GET)>0) $res['get'] = $GET;
    if (count($POST)>0) {
        #$res['post'] = $POST;
        $res['post_raw'] = http_build_query($POST);
    }
    return $res;
}

function getUrlContent($url) {
    $headers = array(
        "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/5.0.1",
        "Accept: *"."/"."*",
        "Accept-Language: en-US,en;q=0.8,ru;q=0.6",
        "Accept-Encoding: gzip, deflate,sdch",
        "Connection: keep-alive",
    );

    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_HEADER => 0,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        //CURLOPT_VERBOSE => true,
        CURLOPT_ENCODING => "gzip",
        CURLOPT_COOKIESESSION => true,
        CURLOPT_COOKIEJAR => 'cookie.txt',
        CURLOPT_COOKIEFILE => 'cookie.txt',
        //CURLOPT_PROXY => '200.195.23.12:3128',
    ));
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function removeBOM($text="") {
    if(substr($text, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf)) {
        $text= substr($text, 3);
    }
    return $text;
}

function TableHeader($tblStruct = false,$tblSort='') {
    $html = '<table class="ftable"><tbody>';
    if ($tblStruct!==false) {
        $html .= '<tr>';
        foreach ($tblStruct as $tblCol) {
            if (!(isset($tblCol['hideTable']) && $tblCol['hideTable']))
            $html .=
                '<th' .
                (isset($tblCol['width']) ? ' width="' . $tblCol['width'] . '"' : '') .
                (isset($tblCol['nowrap']) ? ' nowrap' : '') . '>' .
                $tblCol['name'] .
                '</th>';
        }
        $html .= '</tr>';
    }
    return $html;
}
function TableRow($tblStruct,$tblData,$css='',$cssclass='') {
    $html =
        '<tr'.
        ($css!=''?' style="'.$css.'"':'').
        ($cssclass!=''?' class="'.$cssclass.'"':'').
        '>';
    foreach ($tblStruct as $k=>$tblCol) {
        if (!(isset($tblCol['hideTable']) && $tblCol['hideTable']))
        {
            $value = isset($tblData[$k]) ? $tblData[$k] : '';
            if (isset($tblCol['toText']))
                $value = call_user_func($tblCol['toText'],$value,$k,$tblData);
            $html .=
                '<td' . (isset($tblCol['nowrap']) ? ' nowrap' : '') .
                (isset($tblCol['align']) ? ' align="' . $tblCol['align'] . '"' : '') .
                '>' .
                $value .
                '</td>';
        }
    }
    $html .= '</tr>';
    return $html;
}
function TableEditRows($tblStruct,$tblData){
    $html = '';
    foreach ($tblStruct as $k=>$tblCol)
        if (!(isset($tblCol['hideEdit']) && $tblCol['hideEdit']))
        {
            $value = sprintf('<input type="text" name="%s" value="%s">',
                $k,
                htmlspecialchars(isset($tblData[$k]) ? $tblData[$k] : '')
            );
            if (isset($tblCol['toEdit']))
                $value = call_user_func($tblCol['toEdit'],isset($tblData[$k]) ? $tblData[$k] : '',$k,$tblData);
            $html .= TableRowD($tblCol['name'],$value);
        }
    return $html;
}

function TableRowD($header,$value) {
    $html = '<tr>';
    $html .= '<th>'.$header.'</th>';
    $html .= '<td>'.$value.'</td>';
    $html .= '</tr>';
    return $html;
}
function TableBottom() {
    $html = '</tbody></table>';
    return $html;
}