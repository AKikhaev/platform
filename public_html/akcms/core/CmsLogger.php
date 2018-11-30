<?php
class CmsLogger
{
    private static $terminals = [];
    public static $debug = false;
    public static function enableDebug() { self::$debug=true; }

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
        self::write(self::var_log_export(...func_get_args()).PHP_EOL);
    }
    /**
     * Вывод переменной в лог (print_r) и останов
     * @param $var
     */
    public static function var_log__($var) {
        self::var_log(...func_get_args());
        exit();
    }

    /**
     * Вывод переменной в лог (var_dump)
     * @param $vars
     */
    public static function var_dump($vars) {
        self::beep();
        self::write(self::var_dump_export(...func_get_args()).PHP_EOL);
    }
    /**
     * Вывод переменной в лог (var_dump) и останов
     * @param $var
     */
    public static function var_dump__($var) {
        self::var_dump(...func_get_args());
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
        if ($terminal==null) {
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
    public static function writeLn($data, $terminal=null){ return self::write($data.PHP_EOL, $terminal); }

    /**
     * Выдает в лог последовательность очистки окна терминала
     * @param null $terminal
     */
    public static function clearScreen($terminal=null) { self::write("\e[2J\e[H\e[3J",$terminal); }

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