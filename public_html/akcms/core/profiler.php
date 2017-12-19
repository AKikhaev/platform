<?php

class profiler {
    static public $sql_logger;
    static private $timers = array();
    static public $sql_quries = 0;
    static public $sql_commands = 0;
    private static function convert($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/ (1024 ** $i = floor(log($size, 1024))),2).' '.$unit[$i];
    }
    public static function start($name = 'Overall') {
        self::$timers[$name] = microtime(true);
    }
    public static function  time($name = 'Overall') {
        return str_pad(number_format((microtime(true) - self::$timers[$name])*1000,14,'.',''),20,' ',STR_PAD_LEFT);
    }
    public static function toLog($name = 'Overall') {
        toLogInfo($name.': '.self::time($name).' ms '._ls(31).self::convert(memory_get_peak_usage(true))._ls());
    }
    public static function showOverallTime(){
        register_shutdown_function(function(){ self::toLog('Overall'); });
        self::start('Overall');
    }
    public static function toLogTerminal($message='',$name = 'Overall') {
        core::terminalWrite(
            "\r\e[2K"._ls(32).self::time($name)._ls(0).
            " === $_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI] "._ls(35)."$_SERVER[REMOTE_ADDR] "._ls(31).
            self::convert(memory_get_peak_usage(true)).
            _ls().': '.$message.PHP_EOL
        );
    }
    public static function showOverallTimeToTerminal($sqlDebug = false){
        /* @var  pgdb $sql */
        global $sql;
        $name = 'Overall';
        self::$sql_logger = function($sql) use ($name){
            core::terminalWrite(
                "\r\e[2K"._ls(32).self::time($name).' '._ls(0).
                $sql._ls().PHP_EOL
            );
        };
        register_shutdown_function(function() use ($name,$sqlDebug){
            core::terminalWrite(
                "\r\e[2K"._ls(32).self::time($name)._ls(0).
                " <<<<<<<<<<<<< $_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI] "._ls(35)."$_SERVER[REMOTE_ADDR] "._ls(31).
                self::convert(memory_get_peak_usage(true)).
                ($sqlDebug?' SQL: '.self::$sql_quries.' queries '.self::$sql_commands.' commands':'').
                _ls().PHP_EOL
            );
        });
        core::terminalWrite(
            "\r\e[2K"._ls(32).'    0.00000000000000'._ls(0).
            " >>>>>>>>>>>>> $_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI] "._ls(35)."$_SERVER[REMOTE_ADDR] "._ls(0).date('H:i:s').PHP_EOL);
        if ($sqlDebug) $sql->zzzSetDebug();
        self::start($name);
    }
}
