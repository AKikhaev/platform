<?php #❶❷❸❹❺❻❼❽❾ ② ₽
#core::$time_start = microtime(true);
# error tracking
try {
    require_once 'akcms/core/core.php'; CORE_LOAD_WEB(); # init core
    /* @var $sql pgdb */

    #ob_start(); // Start output buffer
    $pageClass = '';
    $pageTemplate = '';
    $site = new site(); //custom storage, loader

    //if ($GLOBALS['cfg']['debug']) profiler::showOverallTimeToTerminal(true);
    
    /* @var  $page CmsPage */
    $page = null;
    foreach ($cfg['CmsPages_load'] as $pageClass) {
        try {
            $page = new $pageClass();
            break;
        } catch (Exception $e) {
            $page = null;
            if ($e->getMessage() !== 'page_not_found') {
                throw $e;
            }
        }
    }
	if ($page === null) throw $e;

    core::proceedPage($page);
} catch(Exception $e) {
    if (class_exists('core')) core::InTryErrorHandler($e);
    else echo '<script>console.log('.json_encode(sprintf("%s, %s(%s)",$e->getMessage(),basename($e->getFile(),'php'),$e->getLine())).')</script>';
    unset($e);
}
