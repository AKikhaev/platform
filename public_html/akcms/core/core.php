<?php
define('CMS_VERSION','3.0');
define('CMS_OBJOWNER','owner');
define('CMS_ADMIN','admin');
$cfg['debug']=true;

set_include_path(
	'akcms/u/units'.PATH_SEPARATOR.
	'akcms/u/models'.PATH_SEPARATOR.
	'akcms/units'.PATH_SEPARATOR.
	'akcms/classes'.PATH_SEPARATOR.
	'akcms/models'.PATH_SEPARATOR.
	'akcms/u/template'.PATH_SEPARATOR.
	'akcms/template'.PATH_SEPARATOR.'.');

require_once 'akcms/core/classes.php';
error_reporting(-1);
set_error_handler('core::GlobalErrorHandler',-1);
set_exception_handler('core::GlobalExceptionHandler');
register_shutdown_function('core::ShutdownHandler');
spl_autoload_register('core::cms_autoload');

require_once 'akcms/core/functs.php';
require_once 'akcms/u/config/config.php';
require_once 'akcms/core/pgdb.php';

$sql = new pgdb();
$Cacher = new CacheController();

function LOAD_CORE_BASE(){
    GLOBAL $cfg;
    umask(0077);
    setlocale(LC_CTYPE, 'ru_RU.UTF-8');
    setlocale(LC_COLLATE, 'ru_RU.UTF-8');
    mb_internal_encoding("UTF-8");
    mb_http_output('UTF-8');
    mb_http_input('UTF-8');
    mb_language('uni');
    mb_regex_encoding('UTF-8');
    date_default_timezone_set($cfg['default_timezone']);
}
function LOAD_CORE_CLI() {
    LOAD_CORE_BASE();
	GLOBAL $cfg,$CliUser,$OS_WIN;
	
	#function CORE_CLI_TERMINATE(){die();}
	#pcntl_signal(SIGINT, 'CORE_CLI_TERMINATE'); // Ctrl+C
	#pcntl_signal(SIGTERM, 'CORE_CLI_TERMINATE'); // killall myscript / kill <PID>
	#pcntl_signal(SIGHUP, 'CORE_CLI_TERMINATE'); // обрыв связи
	$CliUser = function_exists('posix_getpwuid') ? posix_getpwuid(posix_getuid()) : array();
	$OS_WIN = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
	$_SERVER['DOCUMENT_ROOT'] = getcwd();

	#set_error_handler("GlobalErrorHandler");
	if ($OS_WIN) {
		//system('chcp 65001>null');
		mb_http_output('cp866'); ob_start('mb_output_handler');
		set_include_path(str_replace(':',';', get_include_path()));
	}

	echo (_ls(35)._ls(1).'ITteka CMS'._ls(36).'v'.CMS_VERSION._ls().' ('._ls(33)._ls(1).$cfg['site_domain']._ls().') '._ls(34)._ls(1).'CLI MODE'._ls()."\n");
}
/* Загрузка */
/**
 * @throws CmsException
 */
function LOAD_CORE() {
    LOAD_CORE_BASE();
	GLOBAL $cfg,$path,$pathurl,$pathstr,$pathlen;
	
	# error tracking
	core::$isAjax = false;
	core::$inEdit = false;
	core::$serverName = strtolower($_SERVER['SERVER_NAME']);

	if (isset($cfg['domains_redirects'][core::$serverName])) {
	  header('Location: '.$cfg['domains_redirects'][core::$serverName].$_SERVER['REQUEST_URI'],true,301);
	  exit;
	}

	if (!in_array(core::$serverName, $cfg['domains_approved'], true)) throw new CmsException('domain_not_approved');
	if (in_array(core::$serverName, $cfg['server_test'], true)) core::$testServer = true;
	if (in_array(core::$serverName, $cfg['server_prod'], true)) core::$prodServer = true;

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

	if (isset($_SERVER['SCRIPT_URL']))  $pathurl = strtolower(urldecode($_SERVER['SCRIPT_URL']));
	else {
		$pathurl = strtolower(urldecode($_SERVER['REQUEST_URI']));
		if (mb_strpos($pathstr,'?')!==false) $pathstr = mb_substr($pathstr,0,mb_strpos($pathstr,'?'));
		if (mb_strpos($pathstr,'#')!==false) $pathstr = mb_substr($pathstr,0,mb_strpos($pathstr,'#'));
		if (mb_strpos($pathstr,'&')!==false) $pathstr = mb_substr($pathstr,0,mb_strpos($pathstr,'&'));
	}

	$path = array_filter(explode('/',$pathurl));
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
    elseif (substr($pathurl,-1)!='/')
    {
        header('Location: http://'.core::$serverName.$_SERVER['SCRIPT_URL'].'/'.substr($_SERVER['REQUEST_URI'],strlen($_SERVER['SCRIPT_URL'])));
        exit;
    }

    core::$renderPage = core::$userAuth || core::$inEdit;
    $path = array_values($path);
    $pathlen = count($path);
    $pathstr = implode('/',$path).'/';

	if (file_exists('akcms/u/config/redirect.php')) {#site redirect
		require_once 'akcms/u/config/redirect.php';
		/*
		$cfg['redirects']=array(
			''=>'',
		);
		if (isset($cfg['redirects'][$pathstr])) {
		  header('Location: '.$cfg['redirects'][$pathstr],true,301);
		  exit;
		}
		*/
	}

    require_once 'akcms/u/VisualTheme.php';
}