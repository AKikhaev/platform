<?php
chdir(dirname(__FILE__).'/../..');
if(PHP_SAPI!=='cli')die('<!-- not allowed -->');
ini_set('memory_limit', '228M');
require_once('akcms/core/core.php'); LOAD_CORE_CLI();

/**
 * Class cliUnit
 */
class cliUnit {
    protected $runMethod = 'helpAction';
    protected $options_available = [];
    protected $options = [];

    private function extractOptions(&$commands)
    {
        $lastOption = '';
        foreach ($commands as $i=>$command) {
            if (mb_strpos($command,'--')===0) {
                $command = mb_substr($command,2);
                $this->options[$command] = false;
                $lastOption = $command;
                unset($commands[$i]);
            }
            elseif (mb_strpos($command,'-')===0) {
                $command = mb_substr($command,1);
                $this->options[$command] = false;
                $lastOption = $command;
                unset($commands[$i]);
            }
            elseif ($lastOption!==''){
                $this->options[$lastOption] = ($this->options[$lastOption] === false ? $command : $this->options[$lastOption].' '.$command);
                unset($commands[$i]);
            }
        }
    }

    public function run(){
        $commands = func_get_args();
        $this->extractOptions($commands);
        if (isset($this->options['bash_completion'])) $this->runMethod = 'bash_completion';
        elseif (count($commands)>0) {
            $this->runMethod = $commands[0] . 'Action';
            unset($commands[0]);
        }
        if (method_exists($this,$this->runMethod)) {
            $this->{$this->runMethod}(...$commands);
        } else echo "Cli sub command not found!\n";
    }

    protected function bash_completion()
    {
        $commands = func_get_args();
        $bash_completion_cword = $this->options['bash_completion_cword'];
        $commands_list = [];
        $rc = new ReflectionClass($this);
        foreach ($rc->getMethods() as $method) {
            if (mb_substr($method->getName(), -6) === 'Action' && $method->getDocComment() !== false)
                $commands_list[] = mb_substr($method->getName(), 0, -6);
        }
//        $contain = false;
//        if ($bash_completion_cword!==false) foreach ($commands_list as $commands_list_item) {
//            if (mb_strpos($commands_list_item,$bash_completion_cword)===0) {$contain = true; break;}
//        }

        if (count($commands)<($bash_completion_cword==false?1:2))
            echo implode(' ',array_merge($commands_list,$this->options_available));
        else
            echo implode(' ',$this->options_available);
    }

    /** Shows detailed help
     * @throws ReflectionException
     */
    protected function helpAction(){
        $rc = new ReflectionClass($this);
        $comment = $rc->getDocComment();
        if ($rc->getDocComment()!==false) foreach (explode("\r",$comment) as $line) echo mb_trim($line,'\/\*\s');
        echo PHP_EOL;
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
                $description = mb_trim(str_replace(['<?php',"\n","\r"],'',fgets($handle, 4096)),'\s\#\/');
                fclose($handle);
                echo '  '.str_pad($cmd,$maxLenght).' - '.$description."\n";
            }
        } else {
            $rootCommand = $command[1];
            unset($command[1]);
            if (isset(self::$rootCmdList[$rootCommand])) {
                require_once self::$rootCmdList[$rootCommand];
                $cliUnit = new $rootCommand();
                $cliUnit->run(...$command);
//                call_user_func([$rootCommand,'run']);
            } else echo "Cli command not found\n";
        }
    }
}

cli::run();