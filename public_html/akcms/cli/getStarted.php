<?php // Generate start configuration: nginx, php7, {self}

/**
 * getStarted - генератор начальной конфигурации
 */
class getStarted extends cliUnit {
    public $projectName = '';

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

    public function runAction(){
        $this->helpAction();
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
SQL;
        $data = str_replace('database_name',$cfg['db'][1]['database'],$data);
        $data = str_replace('user_name',$cfg['db'][1]['username'],$data);
        $data = str_replace('password_raw',str_replace('\'','\\\'',$cfg['db'][1]['password']),$data);
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
}