<?php
class profiler {
    static private $timers = array();
    static function start($name = 'Default') {
        self::$timers[$name] = microtime(true);
    }
    static function time($name = 'Default') {
        return (microtime(true) - self::$timers[$name])*1000;
    }
    static function toLog($name = 'Default') {
        toLogInfo($name.': '.self::time($name).' ms '._ls(31).self::convert(memory_get_peak_usage(true))._ls());
    }
    private static function convert($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }
    static function showOverallTime(){
        register_shutdown_function(function(){
            self::toLog('Overall');
        });
        self::start('Overall');
    }
}

class remainCalc {
    private $started;
    private $count = 0;
    private $skip = 0;
    private $nextPlot = 0;
    private $str;

    function init($count, $str='',$skip=0) {
        if ($count == 0)
            echo 'count=0, ' . $str . "\n";
        $this->count = $count;
        $this->started = microtime(true);
        $this->skip=$skip;
        $this->str = ' ' . $str . ' ';
        @ob_flush();
    }

    function intToTime($t) {
        $s = $t % 60;
        $h = floor($t/3600);
        $m = floor(($t-$h*3600)/60);
        return $h . ':' . ($m<10?'0'.$m:$m) . ':' . ($s<10?'0'.$s:$s);
    }

    function plot($num,$printlog = true,$msg='',$msgTitle='') {
        $elapsed = microtime(true) - $this->started;
        if (($elapsed < $this->nextPlot) && ($num != $this->count))
            return;
        if ($elapsed == 0) $elapsed = 1;
        $speed = ($num-$this->skip) / $elapsed;
        if ($speed!=0) {
            $finish = ($this->count - $this->skip) / $speed;
            $remain = $finish - $elapsed;
        } else $remain = 0;
        if ($remain < 0) $remain = 0;
		$elapsed_str = $this->intToTime($elapsed);
		$remain_str = $this->intToTime($remain);
        if ($printlog) echo "\r" .
            _ls(36). $elapsed_str .
            _ls(37) . $this->str .
            _ls(35) . $num . '/' . $this->count .
            _ls(32). ' '. floor($speed).'/s' .
            _ls(33). ' remain: ' . $remain_str .'  ' . _ls(1) . _ls(34) . $msg. '   '._ls(0);
		if ($num == $this->count) echo "\n";

        toTitle(floor($num/$this->count*100)."% $this->str $num/$this->count $remain_str $elapsed_str $msgTitle"); //Заголовок окна

        @ob_flush();
        $this->nextPlot = $elapsed+5;
    }

}