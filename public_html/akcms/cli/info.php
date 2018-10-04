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

    /** About page by ID
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

    /** Tables size
     * @param string $filter
     */
    public function postgreSizeAction($filter = '%.%'){
        global $sql,$cfg;
        if (mb_strpos($filter,'.')===false) $filter = $cfg['db'][1]['schema'].'.'.$filter;
        $filter = str_replace('*','%',$filter);
        $_filter = '(schemaname || \'.\' || tablename) ilike '.$sql->t($filter);

        $query = <<<SQL
select 
schemaname||'.'||tablename as table,
pg_size_pretty(pg_relation_size(schemaname||'.'||tablename)) as data,
pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) as total,
hasindexes,hasrules,hastriggers,rowsecurity
from pg_tables 
where tableowner<>'postgres' AND $_filter
order by pg_total_relation_size(schemaname||'.'||tablename) DESC
SQL;
        $data = $sql->query_all($query);
        if ($data!==false) {
            CmsLogger::table($data);
//            foreach ($data as $datum) {
//                echo implode("\t",$datum).PHP_EOL;
//            }
        } else CmsLogger::logError("Таблицы не найдены");
    }

}