<?php // Show useful information

/**
 * info - Отображает полезную информацию
 */
class info extends cliUnit {
    //protected $options_available = ['-bash_completion','--silence_greetings'];

    public function __construct()
    {
        if(PHP_SAPI!=='cli')die('<!-- not allowed -->');
    }

    /** О странице по ID
     * @param null $id
     * @throws DBException
     */
    public function sectionAction($id=null){
        global $cfg;
        if ($id!==null) {
            $cmsSections = (new modelCmsSections($id))->fields()->get();
            echo '  Url    : http://'. $cfg['server_prod'][0] . '/' . $cmsSections->secUrlFull . PHP_EOL;
            echo '  Title  : '. $cmsSections->secTitle . PHP_EOL;
            echo '  Created: '. $cmsSections->secCreated . PHP_EOL;
        } else echo '  Укажите ID' . PHP_EOL;
    }
}