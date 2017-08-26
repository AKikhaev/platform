<?php

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

function html_arrIdValPairs_toOptions($data,$idVal,$idName,$valSeected,$idStyle=null) {
	$res = '';
	if ($data!==false) foreach ($data as $dataItem) { //
		$res .= '<option value="'.$dataItem[$idVal].'" '.($dataItem[$idVal]==$valSeected?'selected':'').(($idStyle!=null && isset($dataItem[$idStyle]))?' style="'.$dataItem[$idStyle].'"':'').'>'.$dataItem[$idName].'</option>';
	}
	return $res;
}

function gzipOutput(&$data) {
	$acceptEncodings = (isset($_SERVER['HTTP_ACCEPT_ENCODING'])?$_SERVER['HTTP_ACCEPT_ENCODING']:'').' '.(isset($_SERVER['HTTP_TE'])?$_SERVER['HTTP_TE']:'');
	if(strpos($acceptEncodings, 'x-gzip') !== false ){
	  $encoding = 'x-gzip';
	}elseif(strpos($acceptEncodings,'gzip') !== false ){
	  $encoding = 'gzip';
	}else{
	  $encoding = false;
	}
	/*
	$search = array(
		'/\>[^\S ]+/s', //strip whitespaces after tags, except space
		'/[^\S ]+\</s', //strip whitespaces before tags, except space
		'/(\s)+/s'  // shorten multiple whitespace sequences
	);
	$replace = array(
		'>',
		'<',
		'\\1'
	);
	$data = preg_replace($search, $replace, $data);	
	*/
	
	/*
	require_once 'Minify/HTML.php';
	require_once 'Minify/CSS.php';
	$data = Minify_HTML::minify($data, array(
		'cssMinifier' => array('Minify_CSS', 'minify'),
		//'cssMinifier' => array('cssMin', 'minify'),
		'jsMinifier' => array('jsMinifier', 'minify')
		//'jsMinifier' => array('jsMin', 'minify')
	));	
	*/
	if ($encoding) {
		header('Content-Encoding: '.$encoding);
		echo gzencode($data, 6);
	} else echo $data;	
}

function sendMailHTML($to, $subject, $message, $headersAdds = '', $from = 'noreply@beside.ru') {
  $headers  = 'MIME-Version: 1.0' . "\r\n";
  $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
  $headers .= 'From: '.$from. "\r\n";
  $headers .= $headersAdds;
  #$headers .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . "\r\n";
  #$headers .= 'Cc: birthdayarchive@example.com' . "\r\n";
  #$headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";
  return mail($to, $subject, $message, $headers);
}

/**
 * @param $text
 * @param string $to
 * @param bool $notify
 * @param bool $web
 * @return bool
 */
function sendTelegram($text,$to=null,$notify = true,$web = false) {
    if ($to===null) $to = '203405254';
    $auth = '276469341:AAE1A1kt1APsm8WsmxCvgFiOOc0BAnVaOZg';
    $url = 'https://api.telegram.org/bot'.$auth.'/sendMessage?'.http_build_query(array(
            'chat_id'=>$to,
            'text'=>$text,
            'disable_notification'=>$notify?'0':'1',
            'disable_web_page_preview',$web?'0':'1',
        ));

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

function var_log() {
    $var = func_get_args();
    ///$stacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS); $line = $stacktrace['line'];
        echo '<script>console.log('.json_encode(count($var)==1?$var[0]:$var).');</script>';
    //else die(json_encode($var));
}

function var_log_terminal() {
    $var = func_get_args();
    $var = count($var)===1?$var[0]:$var;
    $printVar = print_r($var,true);
    $printVar = preg_replace('/Array\n\s*/','Array',$printVar);
    $printVar = preg_replace('/\n\s+\(/','(',$printVar);
    $printVar = preg_replace('/\n\s+\)/',')',$printVar);
    $printVar = preg_replace('/\n\s*\n/',"\n",$printVar);
    core::terminalBeep();
    core::terminalWrite($printVar);
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
function assocArray2ajax($arr) {
	$narr = array();
	foreach ($arr as $k=>$v) $narr[] = array('k'=>$k,'v'=>$v);
	return $narr;
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

function messagesToErrorArray($messages,$errors) {
	$narr = array();
	foreach ($errors as $k=>$v)
		$narr[] = array('f'=>'e_'.$k,'m'=>isset($messages[$k.'-'.$v])?$messages[$k.'-'.$v]:$v);
	return $narr;
}

Function reindexArray($arr) {
  $newArr = array();
  foreach ($arr as $value) $newArr[] = &$value;
  return $newArr;
}

Function convertArrayEncoding__(&$item, &$key, $encodeFromTo) {
  $item = mb_convert_encoding($item,$encodeFromTo[1],$encodeFromTo[0]);
}

Function convertArrayEncoding(&$arr,$strFrom,$strTo) {
  array_walk_recursive($arr,'convertArrayEncoding__',array($strFrom,$strTo));
}

Function dgtToChar($dgt) {
	return $dgt<10?$dgt:chr($dgt+87);
}

Function Intwz($dg) { // Возращает двухзначное число
  if ($dg<10) return '0'. (int)$dg; else return (int)$dg;
}

Function Str_($str,$cnt) { //Возвращает n-значное число
  if ($cnt>strlen($str))
   return str_repeat('0',$cnt-strlen($str)).$str;
  else return $str;
}

Function DayOfwWeek($datastr,$dayfmt = 1) { // Возвращает день недели
											//, , , , , ,
	$DaysOfWeek[1] = array('Воскресенье','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота');
	$DaysOfWeek[2] = array('ВС','ПН','ВТ','СР','ЧТ','ПТ','СБ');
	$DaysOfWeek[3] = array('7','1','2','3','4','5','6');
	return $DaysOfWeek[$dayfmt][date("w", mktime(0, 0, 0, mb_substr($datastr,5,2), mb_substr($datastr,8,2), mb_substr($datastr,0,4)))];
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

/**
 * Склоняем словоформу
 * @ author runcore
 */
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
	if (file_exists($filepath) || $use_include)
		return file_get_contents($filepath,$use_include);
	else return '['.$filepath.']';
}

function AddFinalSlash($dirpath) // Добавляет слэши в конце строки
{
	if (substr($dirpath,-1)<>'/' and substr($dirpath,-1)<>'\\') return $dirpath.'/';
		else return $dirpath;
}

function GetShapeString($html, $vars, $replace_once = false) //Возвращает готовый HTML код
{
	foreach ($vars as $key => $val) if ($replace_once) {
		$html=preg_replace('/{#'.$key.'#}/',$val,$html,1);
		$html=str_replace('{#'.$key.'#}','',$html);
	} else {
		$html=str_replace('{#'.$key.'#}',$val,$html);
	}
	return $html;
}


function GetShape($shape, $vars=array(), $replace_once = false) //Возвращает готовый HTML код
{
	global $shapes;

	if (empty($shapes[$shape]))
		//$shapes[$shape]=load_filecheck('u/themes/'.$GLOBALS['cfg']['site_theme'].'/shapes/'.$shape.'.shtm');
        $shapes[$shape]=load_filecheck($shape.'.shtm',true);
	$html=$shapes[$shape];
	
	$html=GetShapeString($html, $vars, $replace_once);
	return $html;
}


Function SendMailKoi8r($emilto,$emailfrom,$emailsbj,$mailcontent) // Отправляет Email
{
	$headers  = 'Content-type: text/plain; charset=windows-koi8-r \r\n';
	$headers .= 'From: '.$emailfrom."\r\n";
	$headers .= 'Return-path: '.$emailfrom."\r\n";
	$headers .= 'Reply-To: '.$emailfrom."\r\n";
	return mail($emilto, '=?koi8-r?B?'.base64_encode(convert_cyr_string($emailsbj,'w','k')).'?=', convert_cyr_string($mailcontent,'w','k'), $headers);
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
	curl_setopt($ch, CURLOPT_URL, 'http://geocode-maps.yandex.ru/1.x/?geocode='.urlencode($addr).'&ll=38.985834,45.047493&spn=1,1&results=1&rspn=1&key=ANpUFEkBAAAAf7jmJwMAHGZHrcKNDsbEqEVjEUtCmufxQMwAAAAAAAAAAAAvVrubVT4btztbduoIgTLAeFILaQ==');
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

function strUpCorr($str)
{
  $findstring = isset($str)?mb_strtoupper($str):'';
  if ($findstring==='' && isset($str))
    $findstring = mb_strtoupper(mb_convert_encoding($str,mb_internal_encoding(),'CP-1251'));
  return $findstring;
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
	return "\x1b[".$code."m"; // \e = \x1b
}
function toTitle($msg){
	echo("\033]0;$msg\007");
}
function toLog($msg){ echo "\r\e[K"._ls(35).date('H:i:s ')._ls().$msg._ls().PHP_EOL; }
function toLogError($msg){ echo "\r\e[K"._ls(35).date('H:i:s ')._ls(31)._ls(1).$msg._ls().PHP_EOL; }
function toLogDie__($msg){ die("\r\e[K"._ls(35).date('H:i:s ')._ls(31)._ls(1).$msg._ls(36).' DIE'._ls().PHP_EOL); }
function toLogInfo($msg){ echo "\r\e[K"._ls(35).date('H:i:s ')._ls(32).$msg._ls().PHP_EOL; }

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


function TableHeader($tblStruct = false,$tblSort='') {
    $html = '<table class="ftable"><tbody>';
    if ($tblStruct!==false) {
        $html .= '<tr>';
        foreach ($tblStruct as $tblCol) $html .= '<th'.(isset($tblCol['width'])?' width="'.$tblCol['width'].'"':'').(isset($tblCol['nowrap'])?' nowrap':'').'>'.$tblCol['name'].'</th>';
        $html .= '</tr>';
    }
    return $html;
}
function TableRow($tblStruct,$tblData,$css='',$cssclass='') {
    $html = '<tr'.($css!=''?' style="'.$css.'"':'').($cssclass!=''?' class="'.$cssclass.'"':'').'>';
    foreach ($tblStruct as $k=>$tblCol) $html .= '<td'.(isset($tblCol['nowrap'])?' nowrap':'').(isset($tblCol['align'])?' align="'.$tblCol['align'].'"':'').'>'.@$tblData[$k].'</td>';
    $html .= '</tr>';
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