<?php

class profiler {
    static public $sql_logger;
    static private $timers = array();
    static public $sql_quries = 0;
    static public $sql_commands = 0;
    public static function start($name = 'Default') {
        self::$timers[$name] = microtime(true);
    }
    public static function time($name = 'Default') {
        return (microtime(true) - self::$timers[$name])*1000;
    }
    public static function toLog($name = 'Default') {
        toLogInfo($name.': '.self::time($name).' ms '._ls(31).self::convert(memory_get_peak_usage(true))._ls());
    }
    private static function convert($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/ (1024 ** $i = floor(log($size, 1024))),2).' '.$unit[$i];
    }
    public static function showOverallTime(){
        register_shutdown_function(function(){ self::toLog('Overall'); });
        self::start('Overall');
    }
    public static function showOverallTimeToTerminal($sqlDebug = false){
        /* @var  pgdb $sql */
        global $sql;
        $name = 'Overall';
        self::$sql_logger = function($sql) use ($name){
            core::terminalWrite(
                _ls(32).str_pad(self::time($name),17,' ',STR_PAD_RIGHT)._ls(0).
                $sql._ls()
            );
        };
        register_shutdown_function(function() use ($name,$sqlDebug){
            core::terminalWrite(
                _ls(32).str_pad(self::time($name),17,' ',STR_PAD_RIGHT)._ls(0).
                "<<<<<<<<<<<<< $_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI] "._ls(35)."$_SERVER[REMOTE_ADDR] "._ls(31).
                self::convert(memory_get_peak_usage(true)).
                ($sqlDebug?' SQL: '.self::$sql_quries.' queries '.self::$sql_commands.' commands':'').
                _ls()
            );
        });
        core::terminalWrite(
            _ls(32)."\n".str_pad('0.0000000000000',17,' ',STR_PAD_RIGHT)._ls(0).
            ">>>>>>>>>>>>> $_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI] "._ls(35)."$_SERVER[REMOTE_ADDR] "._ls(0).date('H:i:s'));
        if ($sqlDebug) $sql->zzzSetDebug();
        self::start('Overall');
    }
}
