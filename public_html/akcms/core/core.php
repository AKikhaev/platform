<?php
define('CMS_VERSION','3.0');
define('CMS_OBJOWNER','owner');
define('CMS_ADMIN','admin');

require_once 'akcms/core/functs.php';
require_once 'akcms/u/config/config.php';
require_once 'akcms/core/classes.php'; core::init();

//if ($cfg['debug']===true) CmsLogger::clearScreen();
//if ($cfg['debug']===true) profiler::showOverallTimeToTerminal();
//if ($cfg['debug']===true) CmsLogger::write('===>>> '.$_SERVER['SCRIPT_URL']);

$sql = new pgDB();
$sphql = new sphDB();
$Cacher = new CacheController();
$shape = ['js_admin'=>''];

/* Загрузка */
/**
 * @throws CmsException
 */
function CORE_LOAD_WEB() {
	GLOBAL $cfg,$path,$pathstr,$pathlen;
	
	# error tracking
	core::$serverHost = strtolower($_SERVER['HTTP_HOST']);

	if (isset($cfg['domains_redirects'][core::$serverHost])) {
	  header('Location: '.$cfg['domains_redirects'][core::$serverHost].$_SERVER['REQUEST_URI'],true,301);
	  exit;
	}

	if (!in_array(core::$serverHost, $cfg['domains_approved'], true)) {
        throw new CmsException('domain_not_approved:'.core::$serverHost);
    }

	if (in_array(core::$serverHost, $cfg['server_test'], true)) core::$testServer = true;
	if (in_array(core::$serverHost, $cfg['server_prod'], true)) core::$prodServer = true;
	core::$devTest = isset($_COOKIE['devtest']) && $_COOKIE['devtest'] === 't';

	// System variables
	header('Content-type: text/html; charset=UTF-8');
	header('X-XSS-Protection: 1; mode=block');
	header('X-Powered-By: itteka.ru');
	session_name($cfg['site_session_name']);
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
        if (!mb_check_encoding($_SERVER['SCRIPT_URL'])) throw new CmsException('page_not_found');
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

	class_exists('VisualTheme');
}
function CORE_LOAD_LITE(){
    GLOBAL $cfg;
    header('Content-type: text/html; charset=UTF-8');
    header('X-XSS-Protection: 1; mode=block');
    header('X-Powered-By: itteka.ru');
    session_name($cfg['site_session_name']);
}
