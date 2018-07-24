<?php
function yn($bool,$yes,$no) {return $bool?$yes:$no;}
function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return str_pad(@round($size/ (1024 ** $i = floor(log($size, 1024))),2).' '.$unit[$i],10,' ',STR_PAD_LEFT);
}
function intToTime($t,$zero) {
    if ($t==0) return $zero;
    $s = $t % 60;
    $h = floor($t/3600);
    $m = floor(($t-$h*3600)/60);
    return $h . ':' . ($m<10?'0'.$m:$m) . ':' . ($s<10?'0'.$s:$s);
}
function number($num,$decimail = 0) {return number_format($num,$decimail,'.',' ');}

$status = opcache_get_status();

echo 'Opcache:'.
    yn($status['opcache_enabled']==1,' Активен',' Отключён').
    yn($status['cache_full']==1,' Переполнен','').
    yn($status['restart_pending']==1,' Ожидает перезагрузки','').
    yn($status['restart_in_progress']==1,' Перезагружается','').
    PHP_EOL;
echo 'Работает    : '.intToTime(time()-$status['opcache_statistics']['start_time'],'никогда').PHP_EOL;
echo 'Перезагрузка: '.intToTime($status['opcache_statistics']['last_restart_time'],'никогда').
    ', всего '.($status['opcache_statistics']['oom_restarts']+$status['opcache_statistics']['hash_restarts']+$status['opcache_statistics']['manual_restarts']).' раз'.PHP_EOL;
$memAll = $status['memory_usage']['used_memory'] + $status['memory_usage']['free_memory'] + $status['memory_usage']['wasted_memory'];
echo 'Используется: '.convert($status['memory_usage']['used_memory'])."\t\t".number_format($status['memory_usage']['used_memory']/$memAll*100,2,'.','').'%'.PHP_EOL;
echo 'Свободно    : '.convert($status['memory_usage']['free_memory'])."\t\t".number_format($status['memory_usage']['free_memory']/$memAll*100,2,'.','').'%'.PHP_EOL;
echo 'Потрачено   : '.convert($status['memory_usage']['wasted_memory'])."\t\t".number_format($status['memory_usage']['wasted_memory']/$memAll*100,2,'.','').'%'.PHP_EOL;
echo PHP_EOL;
echo 'Занято строк: '.number($status['interned_strings_usage']['number_of_strings']).PHP_EOL;
echo 'Используется: '.convert($status['interned_strings_usage']['used_memory'])."\t\t".number_format($status['interned_strings_usage']['used_memory']/$status['interned_strings_usage']['buffer_size']*100,2,'.','').'%'.PHP_EOL;
echo 'Свободно    : '.convert($status['interned_strings_usage']['free_memory'])."\t\t".number_format($status['interned_strings_usage']['free_memory']/$status['interned_strings_usage']['buffer_size']*100,2,'.','').'%'.PHP_EOL;
echo PHP_EOL;
echo 'Кешировано  : '.number($status['opcache_statistics']['num_cached_scripts']).PHP_EOL;
echo 'Ключей      : '.number($status['opcache_statistics']['num_cached_keys']).' из '.number($status['opcache_statistics']['max_cached_keys'])."\t\t".
    number_format($status['opcache_statistics']['num_cached_keys']/$status['opcache_statistics']['max_cached_keys']*100,2).'%'.PHP_EOL;
echo 'Попаданий/Промахов: '.$status['opcache_statistics']['hits'].'/'.$status['opcache_statistics']['misses']."\t\t".number_format($status['opcache_statistics']['opcache_hit_rate'],4).'%'.PHP_EOL;
usort($status['scripts'],function($a,$b){if ($a['hits'] == $b['hits']) {return 0;}return ($a['hits'] > $b['hits']) ? -1 : 1;});
$lengths = ['full_path'=>0,'hits'=>0,'memory_consumption'=>0];
foreach ($status['scripts'] as $script) {
    $len = strlen($script['full_path']); if ($len>$lengths['full_path']) $lengths['full_path'] = $len;
    $len = strlen(number($script['hits'])); if ($len>$lengths['hits']) $lengths['hits'] = $len;
}
foreach ($status['scripts'] as $script) {
    echo str_pad($script['full_path'],$lengths['full_path']) .' '.
        str_pad(number($script['hits']),$lengths['hits'],' ',STR_PAD_LEFT).', '.
        convert($script['memory_consumption']).',  '.
        //$script['last_used'].PHP_EOL;
        intToTime(time()-$script['last_used_timestamp'],'только что').PHP_EOL;
}
