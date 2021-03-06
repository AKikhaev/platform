<?php // Show useful information

/**
 * info - Отображает полезную информацию
 */
class info extends cliUnit {
    //protected $options_available = ['-bash_completion','--silence_greetings'];

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
    public function postgresSizeAction($filter = '%.%'){
        global $sql,$cfg;
        if (mb_strpos($filter,'.')===false) $filter = $cfg['db'][1]['schema'].'.'.$filter;
        $filter = str_replace('*','%',$filter);
        $_filter = $sql->t($filter);

        $query = "
SELECT * FROM (
select 
schemaname||'.'||tablename as table,pg_total_relation_size(schemaname||'.'||tablename) as bytes,
pg_size_pretty(pg_relation_size(schemaname||'.'||tablename)) as data,
pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) as total,
hasindexes,hasrules,hastriggers,rowsecurity
from pg_tables where tableowner<>'postgres'

UNION ALL

SELECT '['||current_database()||'].*',pg_database_size(current_database()),'',pg_size_pretty(pg_database_size(current_database())),false,false,false,false

UNION ALL

SELECT schemaname||'.*',SUM(pg_total_relation_size(schemaname||'.'||tablename)),'',pg_size_pretty(SUM(pg_total_relation_size(schemaname||'.'||tablename))::BIGINT),false,false,false,false
from pg_tables where schemaname IN (select DISTINCT schemaname from pg_tables where tableowner<>'postgres')
group by schemaname

ORDER BY bytes DESC
) a WHERE \"table\" ilike $_filter";
        $data = $sql->query_all($query);
        if ($data!==false) {
            CmsLogger::table($data,['bytes'=>false],['data'=>STR_PAD_LEFT,'total'=>STR_PAD_LEFT]);
//            foreach ($data as $datum) {
//                echo implode("\t",$datum).PHP_EOL;
//            }
        } else CmsLogger::logError("Таблицы не найдены");
    }

    /** Writes unix time as readable text
     * @param $time
     * @throws Exception
     */
    public function timeToStringAction($time){
        CmsLogger::writeLn(date('c',$time));
    }

}