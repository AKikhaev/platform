<?php #❶❷❸❹❺❻❼❽❾
#core::$time_start = microtime(true);
# error tracking
try {
    require_once 'akcms/core/core.php'; LOAD_CORE(); # init core
    /* @var $sql pgdb */

    #ob_start(); // Start output buffer
    $pageClass = '';
    $pageTemplate = '';

    /* @var  $page CmsPage */
    $page = null;
    foreach ($cfg['CmsPages_load'] as $pageClass) {
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


    if (core::$isAjax) {
        core::$outputData .= core::proceedAjax();
    }
    else {
        if (core::$inEdit) {
            $shape['content'] = $page->getContent();
            $shape['title'] = $page->getTitle();
            #$shape['worktime'] = (microtime(true)-core::$time_start);
            core::$outputData = shp::tmpl('pages/'.$pageTemplate, $shape, true);
        } else {
            $html = '';
            #if ($_SERVER['REMOTE_ADDR']=='109.172.77.170') {var_dump__($pathstr);}
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
            if (core::$testServer) {
                $html = str_replace('<head>', '<head><meta name="robots" content="noindex, nofollow, noarchive"/>', $html);
                header('Cache-Control: no-store');
                header('X-Robots-Tag: noindex, nofollow, noarchive');
            }
            core::$outputData = $html;
            unset($html);
        }
    }
    #$outputData = ob_get_contents(); ob_end_clean();
    echo core::$outputData;
    if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
} catch(Exception $e) {
    if (class_exists('core')) core::InTryErrorHandler($e);
    else echo '<script>console.log('.json_encode(sprintf("%s, %s(%s)",$e->getMessage(),basename($e->getFile(),'php'),$e->getLine())).')</script>';
    unset($e);
}
