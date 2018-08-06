<?php // Generate start configuration: nginx, php7, {self}

/**
 * getStarted - генератор начальной конфигурации
 */
class getStarted extends cliUnit {
    public $projectName = '';
    protected $options_available = ['-bash_completion','--silence_greetings'];

    public function __construct()
    {
        $this->projectName = $this->getProjectName();
        if(PHP_SAPI!=='cli')die('<!-- not allowed -->');
    }

    public function getProjectName(){
        $path = explode(DIRECTORY_SEPARATOR,getcwd());
        $projectName = $path[count($path)-2];
        return $projectName;
    }

    /**
     * Generate all configuration files
     * @throws DBException
     */
    public function allAction(){
        $this->phpAction();
        $this->nginxAction();
        $this->postgresAction();
    }

    /**
     * Создаёт конфигурацию в /tmp/nginx/*
     * @throws DBException
     */
    public function nginxAction(){
        global $cfg;
        $path = '/etc/nginx/sites-enabled';
        //if (!file_exists($path)) mkdir($path,0755,true);
        $data = file_get_contents('../server/nginx/project_name.conf');
        $data = str_replace('project_name',$this->projectName,$data);
        $data = str_replace('{#domain#}',$cfg['server_prod'][0],$data);
        file_put_contents($path.'/'.$this->projectName.'.conf',$data);
        echo "  Nginx confutation saved to $path/*.\n";
    }

    /**
     * Команды создания БД
     */
    public function postgresAction() {
        global $cfg;
        $data = <<<SQL
  su postgres
  psql
  CREATE ROLE user_name NOINHERIT LOGIN PASSWORD 'password_raw';
  CREATE DATABASE database_name WITH OWNER = user_name ENCODING = 'UTF8';
  ALTER DATABASE database_name SET timezone TO 'Europe/Moscow';
  \c database_name
  ALTER SCHEMA public OWNER TO user_name;
  \q
  
  truncate ~/.psql_history --size 0
  
  pg_restore -d database_name -x -O -v --role=user_name /data/nfs/project_name/server/postgres/newsite.dmp
  psql -c "ALTER SCHEMA newsite RENAME TO schema_name" database_name
  
  #reset system passwords:
  psql -c "UPDATE schema_name.cms_users SET usr_password_md5 = md5('password_salt' || set_config('myapp.psw', md5(random()::text), true)) WHERE id_usr<10;SELECT array_to_string(array(SELECT usr_login FROM schema_name.cms_users),',') || ':' || current_setting('myapp.psw');" database_name
  acli getStarted resetDvPassword

  # backup:
  su postgres
  pg_dump -F c -E UTF8 -Z 9 -x -O -n schema_name -v -f /data/nfs/project_name/tmp/schema_name.dmp database_name
  su postgres -c "pg_dump -F c -E UTF8 -Z 9 -x -O -n schema_name -v -f /data/nfs/project_name/tmp/schema_name.dmp database_name"
SQL;
        $data = str_replace('project_name',$this->projectName,$data);
        $data = str_replace('database_name',$cfg['db'][1]['database'],$data);
        $data = str_replace('schema_name',$cfg['db'][1]['schema'],$data);
        $data = str_replace('user_name',$cfg['db'][1]['username'],$data);
        $data = str_replace('password_raw',str_replace('\'','\\\'',$cfg['db'][1]['password']),$data);
        $data = str_replace('password_salt',str_replace('\'','\\\'',$cfg['usrprepass']),$data);
        echo $data.PHP_EOL;
    }

    /**
     * Создаёт конфигурацию в /tmp/nginx/*
     * @throws DBException
     */
    public function phpAction(){
        global $cfg;
        $path = '/etc/php/7.2/fpm/pool.d';
        //if (!file_exists($path)) mkdir($path,0755,true);
        $data = file_get_contents('../server/php-fpm/project_name.conf');
        $data = str_replace('project_name',$this->projectName,$data);
        $data = str_replace('{#domain#}',$cfg['server_prod'][0],$data);
        file_put_contents($path.'/'.$this->projectName.'.conf',$data);
        echo "  php-fpm confutation saved to $path/*.\n";
    }

    public function resetDvPasswordAction(){
        $password = md5(random_bytes(20));
        CmsUser::setNewPassword('dv',$password);
        echo $password.PHP_EOL;
    }
}