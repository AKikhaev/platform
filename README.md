##Платформа itTeka 1.0
##### Что это?
Страница-ориентированная компонентная среда развертывания web-проектов. Особенности:
* позволяет разворачивать эффективно работающие сайты быстро
* расширяемость модулями,  пользовательскими конфигурациями
* Время холодной генерации <30 мсек, горячей <10 мсек
* Редактор-интегрированнный шаблонизатор 

##### Как развернуть?
* Извлечь
* Пользовательская конфирация расположена в `akcms/u`
* Всё включено. Зависимости обновляются централизованное по мере необходимости
* Database postgresql

##### О студии
* Разработано в itTeka, AKikhaev, 2010-2018
* Не предназначено для публичного распространения
* Передача прав на использование платформы не исключительная и 
  не предпологает использование вне проектов, реализованых itTeka 

##### Требования
* nginx
```bash
apt nginx
```
* PHP 7+: [supported-versions](http://php.net/supported-versions.php)
```bash
dpkg -l | grep php

sudo add-apt-repository ppa:ondrej/php
sudo apt update

apt install libfcgi-bin mc nginx php7.2-fpm php7.2-cli php7.2-common php7.2-curl php7.2-gd php7.2-json php7.2-mbstring php7.2-mysql php7.2-pgsql php7.2-xml

apt install php7.2-zip php7.2-opcache  
apt install php7.2-mcrypt
```

* Postgresql

```bash
apt-get install postgresql-10
service postgresql stop
mkdir -p /data/db
cp -r -p /var/lib/postgresql/10/main /data/db/pg10
/etc/postgresql/10/main/postgresql.conf:
replace /var/lib/postgresql/10/main to /data/db/pg10

# Каждый сайт
service postgresql start
su postgres
psql
CREATE ROLE astr NOINHERIT LOGIN PASSWORD 'password';
CREATE DATABASE astr WITH OWNER = astr ENCODING = 'UTF8';
ALTER DATABASE astr SET timezone TO 'Europe/Moscow';
```

* Базовая настройка сервера

```bash
adduser --system --no-create-home --group www-user
adduser mstr

## prepare project deploy
mkdir -p /data/nfs
chmod 0777 /data/nfs
su mstr
git clone --bare https://itteka_deploy@bitbucket.org/itteka/cms.git /home/mstr/cms.git
git --git-dir=/home/mstr/cms.git worktree add /data/nfs/project_name
#exit
chmod 0755 /data/nfs
##

### connect as mstr  ###
cat server/.bash_aliases > ~/.bash_aliases
first time: git config credential.helper store
git pull && git fetch

```

##### Известные проблемы

* opcache:

При работе нескольких сайтов ocpache не различает пулы php-fpm допуская запуск кода одних пулов 
в рабочих каталогах других пулов.
```bash
/etc/php/7.2/fpm/conf.d/10-opcache.ini добавить строки:
opcache.enable=1
opcache.use_cwd=1
opcache.revalidate_path=1
opcache.save_comments=1
opcache.validate_root=1
```

###### Важно знать
* [Learn Markdown](https://bitbucket.org/tutorials/markdowndemo), [Markdown-Cheatsheet](https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet)
* [Как работает opcache](https://habr.com/company/mailru/blog/310054/)

###### TODO
* Встраиваемые фотографии
* Прикрепляемые файлы
* Отмечать не активные разделы при редактировании заголовков в режиме редактирования. 
  Отобразить инструмент включения отключения перед `{#ed:namefull:l#}` 
* Права на редактирование разделов, подразделов
* Создание подстраниц с панели кнопкой +
* Редактирование параметров раздела с панели
* Ускорить загрузку TinyMce
* Хранение изображений в `/s/...` общим классом
* Разработать хранилище с автодополняемыми полями таблицы на замену 
  `section_string` в `section_storage`
* [Оптимизация](https://github.com/jupeter/clean-code-php), 
  [по-русски](https://github.com/peter-gribanov/clean-code-php)
* [auto deploy](https://gist.github.com/noelboss/3fe13927025b89757f8fb12e9066f2fa#file-post-receive)
