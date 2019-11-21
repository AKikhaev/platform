<?php
chdir(__DIR__.'/../..');
if(PHP_SAPI!=='cli')die('<!-- not allowed -->');
ini_set('memory_limit', '228M');
require_once('akcms/core/core.php');

/**
 * Class cliUnit
 */
class cliUnit {
    /**
     * @var int Не позволяет запускать комманды, количество активных копий которых превышает лимит. 0 - без ограничений
     */
    protected $concurrentLimit = 0;
    protected $runMethod = 'helpAction';
    protected $options_available = [];

    public function __construct()
    {
        if(PHP_SAPI!=='cli')die('<!-- not allowed -->');
        if ($this->concurrentLimit>0 && !isset(cli::$options['bash_completion_cword'])) {
            $find = exec('ps -o command --no-headers -p '.getmypid());
            exec('ps -A -o command --no-headers',$psOut);
            $concurrent = 0; foreach ($psOut as $cmd) if ($cmd==$find) $concurrent++;
            if ($concurrent>$this->concurrentLimit) { CmsLogger::logError('Concurrent limit exceeded! ('.$concurrent.')'); die();}
            else CmsLogger::log("Started $concurrent concurrent process.");
        }
    }

    public function run(){
        $commands = func_get_args();
        if (isset(cli::$options['bash_completion_cword'])) $this->runMethod = 'bash_completion';
        elseif (count($commands)>0) {
            $this->runMethod = $commands[0] . 'Action';
            unset($commands[0]);
        }
        if (method_exists($this,$this->runMethod)) {
            $rc = new ReflectionClass($this);
            $method = $rc->getMethod($this->runMethod);
            if (count($commands)>=$method->getNumberOfRequiredParameters()) {
                try {
                    $this->{$this->runMethod}(...$commands);
                } catch (Throwable $e) {
                    core::GlobalExceptionHandler($e);
                }
                flush();
            } else {
                CmsLogger::logError('Parameters required. See help');
                $parameters = [];
                foreach ($method->getParameters() as $parameter){
                    $parameters[] = $parameter->name;
                }
                $parameters = count($parameters)>0 ? ' {'.implode(',',$parameters).'}' : '';
                $comment = $method->getDocComment();
                if ($comment!==false) {
                    echo '  '.mb_substr($method->getName(),0,-6).$parameters.":\n";
                    foreach (explode("\n",$comment) as $line) {
                        $line = mb_trim($line,'\/\*\s');
                        if ($line==='') continue;
                        if (mb_strpos($line,'@')!==false) break;
                        echo '    '.$line.PHP_EOL;
                    }
                } else {
                    echo '  '.mb_substr($method->getName(),0,-6).$parameters."\n";
                }
            }

        } else echo "Cli sub command not found!\n";
    }

    protected function bash_completion()
    {
        $commands = func_get_args();
        $bash_completion_cword = cli::$options['bash_completion_cword'];
        $commands_list = [];
        $rc = new ReflectionClass($this);
        foreach ($rc->getMethods() as $method) {
            if (mb_substr($method->getName(), -6) === 'Action')
                if ($method->getDocComment() !== false)
                    $commands_list[] = mb_substr($method->getName(), 0, -6);
                else {
                    if ($bash_completion_cword !== false && mb_strpos($method->getName(),$bash_completion_cword)===0)
                        $commands_list[] = mb_substr($method->getName(), 0, -6);
                }
        }
        if (count($commands)<($bash_completion_cword==false?1:2))
            echo implode(' ',array_merge($commands_list,$this->options_available));
        else
            echo implode(' ',$this->options_available);
    }

    /** Shows this help
     * @throws ReflectionException
     */
    protected function helpAction(){
        $rc = new ReflectionClass($this);
        $comment = $rc->getDocComment();
        if ($rc->getDocComment()!==false) foreach (explode("\r",$comment) as $line) echo mb_trim($line,'\/\*\s');
        foreach ($rc->getMethods() as $method) {
            if (mb_substr($method->getName(),-6)==='Action') {
                $parameters = [];
                foreach ($method->getParameters() as $parameter){
                    $parameters[] = $parameter->name;
                }
                $parameters = count($parameters)>0 ? ' {'.implode(',',$parameters).'}' : '';
                $comment = $method->getDocComment();
                if ($comment!==false) {
                    echo '  '.mb_substr($method->getName(),0,-6).$parameters.":\n";
                    foreach (explode("\n",$comment) as $line) {
                        $line = mb_trim($line,'\/\*\s');
                        if ($line==='') continue;
                        if (mb_strpos($line,'@')!==false) break;
                        echo '    '.$line.PHP_EOL;
                    }
                } else {
                    echo '  '.mb_substr($method->getName(),0,-6).$parameters."\n";
                }

            }
        }
    }
}

class cli {
    private static $rootCmdList = [];
    public static $options = [];

    private static function init()
    {
        GLOBAL $cfg,$CliUser;

        #function CORE_CLI_TERMINATE(){die();}
        #pcntl_signal(SIGINT, 'CORE_CLI_TERMINATE'); // Ctrl+C
        #pcntl_signal(SIGTERM, 'CORE_CLI_TERMINATE'); // killall myscript / kill <PID>
        #pcntl_signal(SIGHUP, 'CORE_CLI_TERMINATE'); // обрыв связи
        $CliUser = function_exists('posix_getpwuid') ? posix_getpwuid(posix_getuid()) : array('name'=>get_current_user());
        core::$OS_WIN = DIRECTORY_SEPARATOR==='\\';
        core::$IS_CLI = true;
        $_SERVER['DOCUMENT_ROOT'] = getcwd();
        $_SERVER['HTTP_HOST'] = 'CLI:'.$cfg['site_domain'];
        $_SERVER['SERVER_NAME'] = 'CLI:'.$cfg['site_domain'];
        $_SERVER['REQUEST_URI'] = core::hidePathForShow('/'.trim($GLOBALS['argv'][0],'/'));
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        #set_error_handler("GlobalErrorHandler");
        if (core::$OS_WIN) {
            //system('chcp 65001>null');
            //mb_http_output('cp866'); ob_start('mb_output_handler');
            set_include_path(str_replace(':',';', get_include_path()));
        }

        if (file_exists('akcms/u/VisualTheme.php')) { require_once 'akcms/u/VisualTheme.php'; }

        if (!in_array('--silence_greetings',$_SERVER['argv']))
            echo (_ls(35)._ls(1).'ITteka CMS'._ls(36).'v'.CMS_VERSION._ls().' ('._ls(33)._ls(1).$cfg['site_domain']._ls().') '._ls(34)._ls(1).'CLI MODE'._ls()."\n");
    }

    /** Windows Subsystem Linux
     * @return bool
     */
    public static function isWSL(){
        $osVersion = file_get_contents('/proc/version');
        return mb_stripos($osVersion,'Microsoft')!==false ||
            mb_stripos($osVersion,'WSL')!==false;
    }
    private static function extractOptions(&$commands)
    {

        $lastOption = '';
        foreach ($commands as $i=>$command) {
            if (mb_strpos($command,'--')===0) {
                $command = mb_substr($command,2);
                self::$options[$command] = false;
                $lastOption = $command;
                unset($commands[$i]);
            }
            elseif (mb_strpos($command,'-')===0) {
                $command = mb_substr($command,1);
                self::$options[$command] = false;
                $lastOption = $command;
                unset($commands[$i]);
            }
            elseif ($lastOption!==''){
                self::$options[$lastOption] = (self::$options[$lastOption] === false ? $command : self::$options[$lastOption].' '.$command);
                unset($commands[$i]);
            }
        }
    }
    private static function getRootCommandList(){
        if (count(self::$rootCmdList)==0)
            foreach (glob('{akcms/cli/*.php,akcms/u/cli/*.php}',GLOB_BRACE) as $item)
                self::$rootCmdList[basename($item,'.php')] = $item;
        return self::$rootCmdList;
    }
    public static function run(){
        self::init();
        self::getRootCommandList();
        $commands = $_SERVER['argv']; unset($commands[0]);
        self::extractOptions($commands);

        if (isset(cli::$options['bash_completion_cword']) && count($commands)<(cli::$options['bash_completion_cword']==false?1:2)) {
            die(implode(' ',array_keys(self::$rootCmdList)));
        }

        if (count($commands)===0) {
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
            $rootCommand = $commands[1];
            unset($commands[1]);
            if (isset(self::$rootCmdList[$rootCommand])) {
                require_once self::$rootCmdList[$rootCommand];
                $cliUnit = new $rootCommand();
                $cliUnit->run(...$commands);
//                call_user_func([$rootCommand,'run']);
            } else echo "Cli command not found\n";
        }
    }
}

cli::run();