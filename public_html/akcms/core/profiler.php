<?php

class profiler {
    static private $timers = array();
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
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }
    public static function showOverallTime(){
        register_shutdown_function(function(){ self::toLog('Overall'); });
        self::start('Overall');
    }
    public static function showOverallTimeToTerminal(){
        register_shutdown_function(function(){
            $name = 'Overall';
            core::terminalWrite(
                $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].' '.$_SERVER['REMOTE_ADDR'].' '.$name.': '.self::time($name).' ms '._ls(31).self::convert(memory_get_peak_usage(true))._ls()
            );
        });
        self::start('Overall');
    }
}
