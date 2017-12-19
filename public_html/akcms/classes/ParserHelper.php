<?php
class ParserHelper
{

    static function getCsvLine($handle) {
        $line = CSVWorker::fgetcsv($handle,65536);
        if (is_array($line)) foreach ($line as &$l) $l = mb_convert_encoding(trim($l),'utf8','cp1251');
        return $line;
    }

    static function showLine($cvshdr,$csvline) {
        echo "\n";
        foreach($csvline as $k=>$v) {
            echo mb_str_pad(mb_str_pad($k,2).' '.$cvshdr[$k],20).' '.$v."\n";
        }
    }

    static function preg_extract($pattern,$string) {
        if (preg_match($pattern,$string,$res))
            return $res[1];
        else return false;
    }

    static function trimspaces($text){
        $text = preg_replace('/\<\!\-\-.*?\-\-\>/',' ',$text);
        return mb_trim(preg_replace('/\s{2,}/',' ',$text));
    }


}