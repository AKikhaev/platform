<?php
define('CMS_VERSION','3.0');
define('CMS_OBJOWNER','owner');
define('CMS_ADMIN','admin');

require_once 'akcms/core/functs.php';
require_once 'akcms/u/config/config.php';
//require_once 'akcms/core/pgdb.php';
require_once 'akcms/core/classes.php'; core::init();

//if ($cfg['debug']===true) CmsLogger::clearScreen();
//if ($cfg['debug']===true) profiler::showOverallTimeToTerminal();
//if ($cfg['debug']===true) CmsLogger::write('===>>> '.$_SERVER['SCRIPT_URL']);

$sql = new pgdb();
$Cacher = new CacheController();
$shape = ['js_admin'=>''];

function LOAD_CORE_CLI() {
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
    $_SERVER['REQUEST_URI'] = '/'.$GLOBALS['argv'][0];
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

	#set_error_handler("GlobalErrorHandler");
	if (core::$OS_WIN) {
		//system('chcp 65001>null');
		//mb_http_output('cp866'); ob_start('mb_output_handler');
		set_include_path(str_replace(':',';', get_include_path()));
	}

	if (!in_array('--silence_greetings',$_SERVER['argv']))
	    echo (_ls(35)._ls(1).'ITteka CMS'._ls(36).'v'.CMS_VERSION._ls().' ('._ls(33)._ls(1).$cfg['site_domain']._ls().') '._ls(34)._ls(1).'CLI MODE'._ls()."\n");
}
/* Загрузка */
/**
 * @throws CmsException
 */
function LOAD_CORE() {
	GLOBAL $cfg,$path,$pathstr,$pathlen;
	
	# error tracking
	core::$serverName = strtolower($_SERVER['SERVER_NAME']);

	if (isset($cfg['domains_redirects'][core::$serverName])) {
	  header('Location: '.$cfg['domains_redirects'][core::$serverName].$_SERVER['REQUEST_URI'],true,301);
	  exit;
	}

	if (!in_array(core::$serverName, $cfg['domains_approved'], true)) throw new CmsException('domain_not_approved');
	if (in_array(core::$serverName, $cfg['server_test'], true)) core::$testServer = true;
	if (in_array(core::$serverName, $cfg['server_prod'], true)) core::$prodServer = true;
	core::$devTest = isset($_COOKIE['devtest']) && $_COOKIE['devtest'] === 't';

	// System variables
	header('Content-type: text/html; charset=UTF-8');
	header('X-XSS-Protection: 1; mode=block');
	header('X-Powered-By: itteka.ru');
	session_name($cfg['site_session_name']);
	#ini_set('mbstring.func_overload ','1');
	$path = array();
	#$iplong = sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
	#$browser = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:null;
	CmsUser::init();
	core::$userAuth = CmsUser::isLogin();

	if (isset($_SERVER['SCRIPT_URL']))  $pathurl = urldecode($_SERVER['SCRIPT_URL']);
	else {
		$pathurl = urldecode($_SERVER['REQUEST_URI']);
		if (mb_strpos($pathstr,'?')!==false) $pathstr = mb_substr($pathstr,0,mb_strpos($pathstr,'?'));
		if (mb_strpos($pathstr,'#')!==false) $pathstr = mb_substr($pathstr,0,mb_strpos($pathstr,'#'));
		if (mb_strpos($pathstr,'&')!==false) $pathstr = mb_substr($pathstr,0,mb_strpos($pathstr,'&'));
        $_SERVER['SCRIPT_URL'] = $pathstr;
	}

	$path = array_filter(explode('/',$pathurl),function ($v){return $v!=='';});
    if (@$path[1]==='ajx')
    {
        core::$isAjax = true;
        unset($path[1]);
        if (mb_strpos(end($path), '_') === 0) {
            core::$ajaxAction = current($path);
            unset($path[key($path)]);
        }
    }
    elseif (@$path[1]==='_')
    {
        core::$inEdit = true;
        unset($path[1]);
        if (!core::$userAuth) throw new CmsException('login_needs');
    }
    elseif (mb_substr($pathurl,-1)!='/')
    {
        $QUERY_STRING = $_SERVER['QUERY_STRING']!==''?'?'.$_SERVER['QUERY_STRING']:'';
        $REQUEST_URI = rtrim($_SERVER['REQUEST_URI'],'?');
        header('Location: '.mb_substr($REQUEST_URI,0,mb_strlen($REQUEST_URI)-mb_strlen($QUERY_STRING)).'/'.$QUERY_STRING);
        exit;
    }

    core::$renderPage = core::$userAuth || core::$inEdit;
    $path = array_values($path);
    $pathlen = count($path);
    $pathstr = implode('/',$path).'/';

	if (file_exists('akcms/u/config/redirect.php')) {#site redirect
		require_once 'akcms/u/config/redirect.php';
	}

    require_once 'akcms/u/VisualTheme.php';
}