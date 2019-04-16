<?php // Cron jobs
/**
 * jobs -  Переодические операции
 */
class jobs extends cliUnit {
    //protected $options_available = ['-bash_completion','--silence_greetings'];

    /**
     * Check left space, notify when necessary
     */
    public function diskSizeAction(){
        global $cfg;
        exec('df -h',$data);
        $data = array_filter($data,function($v){
            return mb_strpos($v,'/dev/loop')===false;
        });
        $out = implode(PHP_EOL,$data);
        if (preg_match('/([9][0-9]|100)%/ui',$out)) {
            CmsLogger::log('Диск почти заполнен');
            $mailer = new CmsMailer();
            $mailer->isHTML(true);
            $mailer->addAddress($cfg['email_error']);
            $mailer->Subject = 'Заканчивается место на '.gethostname();
            $mailer->Body = '<pre>'.$out.'</pre>';
            
            try {
                $mailer->send();
            } catch (Exception $e) {
                CmsLogger::logError('Не удалось отправить сообщение о нехватке места на диске');
            }
        } else echo 'Места достаточно'.PHP_EOL;
    }

}