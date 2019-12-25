<?php

class CmsException_unavailable extends CmsException
{
    public function __construct($message = '', $code = 0, $line = 0, $file = '')
    {
        parent::__construct($message, $code, $line, $file);
        $stamp = new DateTime();
        $stamp->setTimezone(new DateTimeZone('UTC'));
        $shape['REMOTE_ADDR'] = core::get_client_ip();
        $shape['TIME'] = $stamp->format('Y-m-d H:i:s').' UTC';
        $shape['Ray_ID'] = mt_rand(100000000000000,999999999999999);
        $shape['HTTP_HOST'] = $_SERVER['HTTP_HOST'];
        @ob_end_flush();
        //http://cloudflarepreview.com/preview-cpage?act=preview&target=cf-error:500s
        $errorPageTemplate = 'error_unavailable';
        echo shp::tmpl('errors/'.$errorPageTemplate, $shape);
        die;
    }
}