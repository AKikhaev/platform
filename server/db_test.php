<?php

$startTime = mktime((int)date("H")+1,0,0);
$connebtionString = 'host=pg.sweb.ru port=5432 dbname=parablru_test user=parablru_test password=Gd5dv55G34g';

function secElapsed()
{
    global $startTime;
    return $startTime-time();
}

echo 'Тест подключения к postgres'.PHP_EOL;

if (secElapsed()>1)
    do {
        $sec = secElapsed();
        echo "\r До начала теста ".$sec.' секунд. Ожидание... Вы можете запустить тест за несколько минут до нового часа   ';
        sleep(1);
    } while ($sec>1);

$errors = 0;

echo PHP_EOL.'Тест будет заключатся к подключению и отключению от быза несколько раз на 4 секунды '.PHP_EOL;

for ($i=0; $i<20; $i++) {
    $msg = "\r".'Подключение...'; echo $msg;
    try {
       $db = pg_connect($connebtionString);
       $msg .= ' Успешно. Ожидание...';  echo $msg;
       sleep(2);
       $msg .= ' Отключение.'.PHP_EOL;  echo $msg;
       pg_close($db);
       sleep(1);
    } catch (Exception $exception)
    {
        ++$errors;
        echo PHP_EOL.$exception->getMessage();
    }
}

echo PHP_EOL.'Ошибок: '.$errors.PHP_EOL;

