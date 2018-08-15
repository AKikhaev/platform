<?php
class CmsLogger
{
    private static $terminals = [];
    public static $debug = false;
    public static function enableDebug() {self::$debug=true;}

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
     * Вывод переменной в лог
     * @param $vars
     */
    public static function var_log($vars) {
        self::beep();
        self::write(self::var_log_export(...func_get_args()).PHP_EOL);
    }

    /**
     * Вывод переменной в лог и останов
     * @param $var
     */
    public static function var_log__($var) {
        self::var_log(...func_get_args());
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
    public static function writeLn($data, $terminal=null){self::write($data.PHP_EOL, $terminal);}

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
    public static function log($msg)          { self::write("\r\e[K"._ls(35).date('H:i:s ')._ls()                     .$msg._ls().PHP_EOL); }

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
    public static function logInfo($msg)      { self::write("\r\e[K"._ls(35).date('H:i:s ')._ls(32)             .$msg._ls().PHP_EOL); }

}