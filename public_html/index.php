<?php #❶❷❸❹❺❻❼❽❾
#core::$time_start = microtime(true);
# error tracking
try {
    require_once('akcms/core/core.php'); LOAD_CORE(); # init core
    /* @var $sql pgdb */

    #ob_start(); // Start output buffer
    //profiler::showOverallTimeToTerminal();
    $pageClass = '';
    $pageTemplate = '';
    core::$renderPage = core::$userAuth || core::$inEdit || core::$isAjax;

    if($pathlen==1 && $path[0]=='bot_test') {
        $tb = new TelegramBot();
        $tb->webHook();
        die('.');
    }
    else {

        //sendTelegram($pathurl.' '.core::get_client_ip());
        $classes = array(
            'PageUnit',
            'MngUnit',
            'SysUnit',
            //'FeedUnit',
            'TmplMapperUnit',
        );

        /* @var  $page CmsPage */
        $page = null;
        foreach ($classes as $pageClass) {
            try {
                $page = new $pageClass($pageTemplate);
                break;
            } catch (Exception $e) {
                $page = null;
                if ($e->getMessage() !== 'page_not_found') {
                    throw $e;
                }
            }
        }
        if ($page === null) throw $e;
    }

    if (core::$isAjax) {
        core::$outputData .= core::proceedAjax();
    }
    else {
        if (core::$inEdit) {
            $shape['content'] = $page->getContent();
            $shape['title'] = $page->getTitle();
            #$shape['worktime'] = (microtime(true)-core::$time_start);
            core::$outputData = GetShape('pages/'.$pageTemplate, $shape, true);
        } else {
            $html = '';
            #if ($_SERVER['REMOTE_ADDR']=='109.172.77.170') var_dump__($pathstr);
            //if (core::$renderPage || !$Cacher->cache_read($pathstr,$html))
            {
                $pagecontent = $page->getContent();
                $pagecontent = preg_replace('/\<img\s/','<img itemprop="image" ',$pagecontent,1);
                $shape['title'] = $page->getTitle();
                $html = shp::tmpl('pages/'.$pageTemplate,array('content'=>$pagecontent));
                $html = shp::str($html, $shape, false);
                VisualTheme::replaceStaticHolders($html, $page->page);
                //$html = Minify_HTML::minify($html);
                //
                //if (!core::$userAuth && $page->canCache()) $Cacher->cache_write($pathstr,$html,600);
            }
            if (core::$testServer)
                $html = str_replace('<head>','<head><meta name="robots" content="noindex, nofollow, noarchive"/>',$html);
            core::$outputData = $html;
            unset($html);
        }
    }
    #$outputData = ob_get_contents(); ob_end_clean();
    #gzipOutput($outputData);
    echo core::$outputData;

} catch(Exception $e) {
    if (class_exists('core') && function_exists('GetShape')) core::InTryErrorHandler($e);
    else echo '<script>console.log('.json_encode(sprintf("%s, %s(%s)",$e->getMessage(),basename($e->getFile(),'php'),$e->getLine())).')</script>';
    unset($e);
}
