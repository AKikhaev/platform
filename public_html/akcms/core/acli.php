#! /usr/bin/php7.2
<?php
//chdir(dirname(__FILE__).'/../..');
if(PHP_SAPI!=='cli')die('<!-- not allowed -->');
ini_set('memory_limit', '228M');
require_once('akcms/core/core.php'); LOAD_CORE_CLI();

/**
 * Class cliUnit
 */
class cliUnit {
    private $runMethod = 'runAction';
    public function run(){
        $commands = func_get_args();
        if (count($commands)>0) {
            $this->runMethod = $commands[0].'Action';
            unset($commands[0]);
        }
        if (method_exists($this,$this->runMethod)) {
            $this->{$this->runMethod}(...$commands);
        } else echo "Command not found!\n";
    }

    /** Shows detailed help
     * @throws ReflectionException
     */
    private function helpAction(){
        $rc = new ReflectionClass($this);
        $comment = $rc->getDocComment();
        if ($rc->getDocComment()!==false) foreach (explode("\r",$comment) as $line) echo mb_trim($line,'\/\*\s');
        foreach ($rc->getMethods() as $method) {
            if (mb_substr($method->getName(),-6)==='Action') {
                $comment = $method->getDocComment();
                if ($comment!==false) {
                    echo '  '.mb_substr($method->getName(),0,-6).":\n";
                    foreach (explode("\n",$comment) as $line) {
                        $line = mb_trim($line,'\/\*\s');
                        if ($line==='') continue;
                        if (mb_strpos($line,'@')!==false) break;
                        echo '    '.$line.PHP_EOL;
                    }
                }
            }
        }
    }
}

class cli {
    private static $rootCmdList = [];
    private static function getRootCommandList(){
        if (count(self::$rootCmdList)==0)
            foreach (glob('{akcms/cli/*.php,akcms/u/cli/*.php}',GLOB_BRACE) as $item)
                self::$rootCmdList[basename($item,'.php')] = $item;
        return self::$rootCmdList;
    }
    public static function run(){
        global $sql;
        self::getRootCommandList();

        $command = $_SERVER['argv'];
        unset($command[0]);
        if (count($command)===0) {
            echo "Command list:\n";
            $maxLenght = 0;
            foreach (self::$rootCmdList as $cmd=>$path) {
                if (mb_strlen($cmd)>$maxLenght) $maxLenght = mb_strlen($cmd);
            }
            foreach (self::$rootCmdList as $cmd=>$path) {
                $handle = fopen($path, 'r');
                $description = mb_trim(str_replace('<?php','',fgets($handle, 4096)),'\#\/\s');
                fclose($handle);
                echo '  '.str_pad($cmd,$maxLenght).' - '.$description;
            }
        } else {
            $rootCommand = $command[1];
            unset($command[1]);
            if (isset(self::$rootCmdList[$rootCommand])) {
                require_once self::$rootCmdList[$rootCommand];
                $cliUnit = new $rootCommand;
                $cliUnit->run(...$command);
//                call_user_func([$rootCommand,'run']);
            } else echo "Command not found\n";
        }
    }
}

cli::run();