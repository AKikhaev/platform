<?php
class CmsLogger
{
    private static $terminals = [];
    public static $debug = false;
    public static $ignoreCLI = false;

    /** formating array as horizontal table
     * @param $data
     * @param array $headers
     * array of headers translations. false to hide column ['name'=>Имя, 'sort'=>false]
     * @param array $alignment
     * array of column alignments. STR_PAD_LEFT, STR_PAD_RIGHT, STR_PAD_BOTH ['size'=>STR_PAD_LEFT]
     */
    public static function table($data, $headers = [], $alignment = []) {
        $lenghts = []; $keys = [];
        //reset($data);
        //self::var_dump__(__LINE__,current($data));
        foreach (array_keys(current($data)) as $key)
            $lenghts[$key] = isset($headers[$key])?mb_strlen($headers[$key]):mb_strlen($key);
        foreach ($data as $datum) foreach ($datum as $key=>$v) {
            $length = mb_strlen($v);
            if ($length>$lenghts[$key]) $lenghts[$key] = $length;
        }
        self::write(_ls(1));
        foreach (array_keys(current($data)) as $key) {
            if (isset($headers[$key]) && $headers[$key]===false) continue;
            self::write(mb_str_pad(isset($headers[$key]) ? $headers[$key] : $key, $lenghts[$key], ' ', STR_PAD_BOTH) . ' ');
        }
        self::write(_ls().PHP_EOL);
        foreach ($data as $datum) {
            foreach ($datum as $key=>$v) {
                if (isset($headers[$key]) && $headers[$key]===false) continue;
                if ($v==='f') $v = '-';
                self::write(mb_str_pad($v,$lenghts[$key],' ',isset($alignment[$key])?$alignment[$key]:STR_PAD_RIGHT).' '); //,
            }
            self::write(PHP_EOL);
        }
    }
    public static function enableDebug() { self::$debug=true; }

    /** formatting model data as vertical table
     * @param cmsModelAbstract $model
     * @param string $mode
     * skipEmpty|all
     */
    public static function models($models,$mode = 'skipEmpty') {
        $data = [];
        $models_count = count($models);
        if (is_object($models)) $models = [$models];
        foreach ($models as $n=>$model) {
            foreach ($model->asArray() as $k => $v) {
                if ($v !== null && $v != '' || mb_strtolower($mode) == 'all') {
                    $item = isset($data[$k])?$data[$k]:[
                        0 => $k,
                        1 => $model->__getFieldDescription($k),
                    ];
                    for ($i = 0; $i<$models_count;$i++) if (!isset($item[$i+2])) $item[$i+2] = '';
                    $item [$n + 2] = ': ' . $v;
                    $data[$k] = $item;
                }
            }
        }
        ksort($data);
        //self::var_dump__($data);
        self::table($data,['Название','Имя','Значение'],[1=>STR_PAD_LEFT]);
    }

    /**
     * Текстовое форматированное представление переданных переменных любого типа
     * @return false|null|string|string[]
     */
    public static function var_dump_export() {
        ob_start();
        var_dump(...func_get_args()); $printVar = ob_get_contents();
        ob_end_clean();
        $printVar = preg_replace('/\n\s*\}/','}',$printVar);
        $printVar = preg_replace('/=>\n\s+/',' => ',$printVar);
        $printVar = preg_replace('/\[\"(.+)\"\]/','[$1]',$printVar);
        return $printVar;
    }

    /**
     * Вывод переменной в лог (var_dump)
     * @param $vars
     */
    public static function var_dump($vars) {
        self::beep();
        $stacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $stacktrace_num = 0; if (strpos($stacktrace[0]['file'],__FILE__)!==false) $stacktrace_num = 1;
        self::write(
            basename($stacktrace[$stacktrace_num]['file']).':'.$stacktrace[$stacktrace_num]['line'].': '._ls(1).
            self::var_dump_export(...func_get_args())._ls().PHP_EOL
        );
    }
    /**
     * Вывод переменной в лог (var_dump) и останов
     * @param $var
     */
    public static function var_dump__($var) {
        self::var_dump(...func_get_args());
        exit();
    }

    /**
     * Текстовое форматированное представление переданных переменных любого типа
     * @param $vars
     * @return mixed|null|string|string[]
     */
    public static function var_log_export($vars) {
        $var = func_get_args();
        $var = count($var)===1?$var[0]:$var;
        $printVar = print_r($var,true);
        $printVar = preg_replace('/Array\n\s*/','Array',$printVar);
        $printVar = preg_replace('/\n\s+\(/','(',$printVar);
        $printVar = preg_replace('/\n\s+\)/',')',$printVar);
        $printVar = str_replace('[GLOBALS] => Array*RECURSION*','',$printVar);
        $printVar = preg_replace('/\n\s*\n/',"\n",$printVar);
        return $printVar;
    }
    /**
     * Вывод переменной в лог (print_r)
     * @param $vars
     */
    public static function var_log($vars) {
        self::beep();
        $stacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $stacktrace_num = 0; if (strpos($stacktrace[0]['file'],__FILE__)!==false) $stacktrace_num = 1;
        self::write(
            basename($stacktrace[$stacktrace_num]['file']).':'.$stacktrace[$stacktrace_num]['line'].': '._ls(1).
            self::var_log_export(...func_get_args()).PHP_EOL
        );
    }
    /**
     * Вывод переменной в лог (print_r) и останов
     * @param $var
     */
    public static function var_log__($var) {
        self::var_log(...func_get_args());
        exit();
    }

    /** Вывод в js консоль
     * @param $var
     */
    public static function var_log_js($var) {
        $var = func_get_args();
        if (!Core::$IS_CLI && !Core::$isAjax)
            echo '<script>console.log('.json_encode(count($var)==1?$var[0]:$var).');</script>';
    }

    /**
     * @param $variable
     * @param int $strlen
     * @param int $width
     * @param int $depth
     * @param int $i
     * @param array $objects
     * @return string
     * @see https://www.leaseweb.com/labs/2013/10/smart-alternative-phps-var_dump-function/
     */
    public static function var_debug_export($variable,$strlen=100,$width=25,$depth=10,$i=0,&$objects = array())
    {
        $search = array("\0", "\a", "\b", "\f", "\n", "\r", "\t", "\v");
        $replace = array('\0', '\a', '\b', '\f', '\n', '\r', '\t', '\v');

        $string = '';

        switch(gettype($variable)) {
            case 'boolean':      $string.= $variable?'true':'false'; break;
            case 'integer':      $string.= $variable;                break;
            case 'double':       $string.= $variable;                break;
            case 'resource':     $string.= '[resource]';             break;
            case 'NULL':         $string.= "null";                   break;
            case 'unknown type': $string.= '???';                    break;
            case 'string':
                $len = strlen($variable);
                $variable = str_replace($search,$replace,substr($variable,0,$strlen),$count);
                $variable = substr($variable,0,$strlen);
                if ($len<$strlen) $string.= '"'.$variable.'"';
                else $string.= 'string('.$len.'): "'.$variable.'"...';
                break;
            case 'array':
                $len = count($variable);
                if ($i==$depth) $string.= 'array('.$len.') {...}';
                elseif(!$len) $string.= 'array(0) {}';
                else {
                    $keys = array_keys($variable);
                    $spaces = str_repeat(' ',$i*2);
                    $string.= "array($len)\n".$spaces.'{';
                    $count=0;
                    foreach($keys as $key) {
                        if ($count==$width) {
                            $string.= "\n".$spaces."  ...";
                            break;
                        }
                        $string.= "\n".$spaces."  [$key] => ";
                        $string.= self::var_debug_export($variable[$key],$strlen,$width,$depth,$i+1,$objects);
                        $count++;
                    }
                    $string.="\n".$spaces.'}';
                }
                break;
            case 'object':
                $id = array_search($variable,$objects,true);
                if ($id!==false)
                    $string.=get_class($variable).'#'.($id+1).' {...}';
                else if($i==$depth)
                    $string.=get_class($variable).' {...}';
                else {
                    $id = array_push($objects,$variable);
                    $array = (array)$variable;
                    $spaces = str_repeat(' ',$i*2);
                    $string.= get_class($variable)."#$id\n".$spaces.'{';
                    $properties = array_keys($array);
                    foreach($properties as $property) {
                        $name = str_replace("\0",':',trim($property));
                        $string.= "\n".$spaces."  [$name] => ";
                        $string.= self::var_debug_export($array[$property],$strlen,$width,$depth,$i+1,$objects);
                    }
                    $string.= "\n".$spaces.'}';
                }
                break;
        }

        if ($i>0) return $string;

//        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
//        do $caller = array_shift($backtrace); while ($caller && !isset($caller['file']));
//        if ($caller) $string = $caller['file'].':'.$caller['line']."\n".$string;

        $string = preg_replace('/\n\s*\{/',' {',$string);
        return $string;
    }

    /**
     * Вывод переменной в лог (var_debug)
     * @param $variable
     * @param int $strlen
     * @param int $width
     * @param int $depth
     * @param int $i
     * @param array $objects
     */
    public static function var_debug($variable,$strlen=100,$width=25,$depth=1,$i=0,&$objects = array()) {
        self::beep();
        $stacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $stacktrace_num = 0; if (strpos($stacktrace[0]['file'],__FILE__)!==false) $stacktrace_num = 1;
        self::write(
            basename($stacktrace[$stacktrace_num]['file']).':'.$stacktrace[$stacktrace_num]['line'].': '._ls(1).
            self::var_debug_export(...func_get_args()).PHP_EOL
        );
    }

    /**
     * Вывод переменной в лог (var_debug) и останов
     * @param $variable
     * @param int $strlen
     * @param int $width
     * @param int $depth
     * @param int $i
     * @param array $objects
     */
    public static function var_debug__($variable,$strlen=100,$width=25,$depth=0,$i=0,&$objects = array()) {
        self::var_debug(...func_get_args());
        exit();
    }

    /**
     * Перечень доступных консолей для вывода
     * @return array
     */
    public static function getTerminalsList(){
        global $cfg;
        if (count(self::$terminals)>0) return self::$terminals;
        $out = [];
        if (is_writable('/logs/terminal'))
            $out[] = [
                'user' => 'nouser',
                'terminal' => '/logs/terminal',
                'date' => '',
                'time' => '',
                'ip' => '127.0.0.1',
            ];
        else {
            foreach (array_reverse(glob('/dev/pts/*')) as $pts) {
                if (is_writable($pts) && fileowner($pts)==$cfg['user_terminal_uid'])
                    $out[] = [
                        'user' => 'nouser',
                        'terminal' => $pts,
                        'date' => '',
                        'time' => '',
                        'ip' => '127.0.0.1',
                    ];
            }
        }
        return self::$terminals = $out;
    }

    /**
     * Вывод данных в активный лог/терминал
     * @param $data
     * @param null $terminal
     * @return bool
     */
    public static function write($data, $terminal=null){
        if ($terminal==null && !self::$ignoreCLI) {
            if (core::$IS_CLI) {
                echo $data;
                return true;
            }
            if (!isset(self::$terminals[0])) self::getTerminalsList();
            if (isset(self::$terminals[0])) $terminal = self::$terminals[0]['terminal'];
            else return false;
        }
        //exec('who > /dev/pts/1');
        $tty = fopen($terminal, 'ab');
        $r = fwrite($tty, "$data");
        fclose($tty);
        return $r !== false;
    }

    /**
     * Вывод данных в активный лог/терминал и перевод строкм
     * @param $data
     * @param null $terminal
     * @return bool
     */
    public static function writeLn($data='', $terminal=null){ return self::write($data.PHP_EOL, $terminal); }

    /**
     * Выдает в лог последовательность очистки окна терминала и прокрутки
     * @param null $terminal
     */
    public static function clearScreenScroll($terminal=null) { self::write("\e[2J\e[H\e[3J",$terminal); }

    /**
     * Выдает в лог последовательность очистки окна терминала
     * @param null $terminal
     */
    public static function clearScreen($terminal=null) { self::write("\e[2J\e[H",$terminal); }

    /**
     * Выдает в лог последовательность очистки прокрутки
     * @param null $terminal
     */
    public static function clearScroll($terminal=null) { self::write("\e[3J",$terminal); }

    /**
     * Выдает в лог последовательность beep
     * @param null $terminal
     */
    public static function beep($terminal=null) { self::write("\x07",$terminal); }

    /**
     * Выдаёт в лог последовательность установки заголовка
     * @param $title
     * @param null $terminal
     */
    public static function title($title,$terminal=null) { self::write("\033]0;$title\007",$terminal); }

    /**
     * Выдаёт в лог последовательность очистки строки
     * @param null $terminal
     */
    public static function clearLine($terminal=null) { self::write("\e[2K",$terminal); }

    /**
     * Время фиолетовое, сообщение обычное
     * @param $msg
     */
    public static function log($msg)          { self::write("\r\e[K"._ls(35).date('H:i:s ')._ls().$msg._ls().PHP_EOL); }

    /**
     * Время фиолетовое, сообщение серое без новой строки
     * @param $msg
     */
    public static function logProcess($msg)   { self::write("\r\e[K"._ls(35).date('H:i:s ')._ls(37)._ls(1).$msg._ls()); }

    /**
     * Время фиолетовое, сообщение красное
     * @param $msg
     */
    public static function logError($msg)     { self::write("\r\e[K"._ls(35).date('H:i:s ')._ls(31)._ls(1).$msg._ls().PHP_EOL); }

    /**
     * Время фиолетовое, сообщение красное. Останов
     * @param $msg
     */
    public static function logDie__($msg)     { self::write("\r\e[K"._ls(35).date('H:i:s ')._ls(31)._ls(1).$msg._ls(36).' DIE'._ls().PHP_EOL); die; }

    /**
     * Время фиолетовое, сообщение зеленое
     * @param $msg
     */
    public static function logInfo($msg)      { self::write("\r\e[K"._ls(35).date('H:i:s ')._ls(32) .$msg._ls().PHP_EOL); }

    public static function logWho() {
        self::log($_SERVER['HTTP_USER_AGENT'].' '.$_SERVER['REMOTE_ADDR']. ' '.$_SERVER['REQUEST_URI']);
    }

}