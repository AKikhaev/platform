<?php

class remainCalc {
    private $started;
    private $count = 0;
    private $skip = 0;
    private $nextPlot = 0;
    private $str;
    private $value = 0;

    /** Инициализация калькулятора
     * @param $count
     * Количетво
     * @param string $str
     * Заголовок
     * @param int $skip
     * Расчет, при пропуске указанного количества
     */
    function init($count, $str='',$skip=0) {
        if ($count == 0)
            echo 'count=0, ' . $str . "\n";
        $this->count = $count;
        $this->skip=$skip;
        $this->value = $skip;
        $this->str = ' ' . $str . ' ';
        @ob_flush();
        $this->started = microtime(true);
    }

    /*** Преобразует число в текстовый интервал
     * @param $t
     * @return string
     */
    function intToTime($t) {
        $s = $t % 60;
        $h = floor($t/3600);
        $m = floor(($t-$h*3600)/60);
        return $h . ':' . ($m<10?'0'.$m:$m) . ':' . ($s<10?'0'.$s:$s);
    }

    /** Подсчет и вывод времени не чаще чем раз в несколько секунд
     * @param int $num
     * Текщее значение. Если -1, подсчитает как следущее
     * @param bool $printLog
     * Вывод лога на экран. Если 1 - вывод принудительно, игнорируя частоту
     * @param string $msg
     * Сообщение на экран
     * @param string $msgTitle
     * Сообщение в заголовок
     */
    function plot($num=-1, $printLog = true, $msg='', $msgTitle='') {
        if ($num === -1) $num = ++$this->value;
        $elapsed = microtime(true) - $this->started;
        if (($elapsed < $this->nextPlot) && ($num != $this->count) && $printLog!==1)
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
        if ($printLog) echo "\r\e[2K" .
            _ls(36). $elapsed_str .
            _ls(37) . $this->str .
            _ls(35) . $num . '/' . $this->count .
            _ls(32). ' '. floor($speed).'/s' .
            _ls(33). ' remain: ' . $remain_str .'  ' . _ls(1) . _ls(34) . $msg. _ls(0);
		if ($num == $this->count || $printLog===1) echo "\n";

        toTitle(floor($num/$this->count*100)."% $this->str $num/$this->count $remain_str $elapsed_str $msgTitle"); //Заголовок окна

        @ob_flush();
        $this->nextPlot = $elapsed+5;
    }

}