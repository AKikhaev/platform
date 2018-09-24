##Платформа itTeka 2.0
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
apt install nginx
```
* PHP 7+: [supported-versions](http://php.net/supported-versions.php)
```bash
dpkg -l | grep php

add-apt-repository ppa:ondrej/php
apt update
apt upgrade

apt install libfcgi-bin mc nginx socat php7.2-fpm php7.2-cli php7.2-common php7.2-curl php7.2-gd php7.2-json php7.2-mbstring php7.2-mysql php7.2-pgsql php7.2-xml p7zip-full

apt install php7.2-zip php7.2-opcache  
apt install php7.2-mcrypt
```

* Postgresql

```bash
apt install postgresql-10
service postgresql stop
mkdir -p /data/db
cp -r -p /var/lib/postgresql/10/main /data/db/pg10
/etc/postgresql/10/main/postgresql.conf:
replace /var/lib/postgresql/10/main to /data/db/pg10

# Каждый сайт
service postgresql start
su postgres
psql
CREATE ROLE project_name NOINHERIT LOGIN PASSWORD 'password';
CREATE DATABASE project_name WITH OWNER = project_name ENCODING = 'UTF8';
ALTER DATABASE project_name SET timezone TO 'Europe/Moscow';
\c project_name
ALTER SCHEMA public OWNER TO project_name;
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
read -p "Enter project name: " project_name; git --git-dir=/home/mstr/cms.git worktree add /data/nfs/$project_name
#exit
chmod 0755 /data/nfs
sh server/initRootDir.sh
##

### connect as mstr  ###
cat server/.bash_aliases > ~/.bash_aliases
id -u 
# add to congif.php: $cfg['user_terminal_uid'] = 1000;
first time: git config credential.helper store
git fetch && git pull

## update production
git reset --hard && git pull
```

##### SSL
* Включение

`$cfg['ssl'] = true;`
```text
* Генерация конфигов для получения сертификата
cls && acli getStarted nginx --ssl-prepare && service nginx configtest && service nginx reload
* Получение сертификата:
php server/acme/certs.php
* Генерация конфигов с использованием сертификатов 
cls && acli getStarted nginx && service nginx configtest && service nginx reload
* Обновление сертификатов (для cron)
php server/acme/certs.php
```
* [Оценка ssl](https://www.ssllabs.com/ssltest/)
* Список доменов сайта: 

`true | openssl s_client -showcerts -connect habrahabr.ru:443 2>&1 | openssl x509 -text | grep -o 'DNS:[^,]*' | cut -f2 -d:`
* Сведения о сертификате:
```text
openssl x509 -text -in /data/certs/project_name/cert.crt
cat /data/certs/project_name/cert.crt | openssl x509 -text | grep -o 'DNS:[^,]*' | cut -f2 -d:
cat /data/certs/project_name/cert.crt | openssl x509 -text | grep -o 'Not After :[^,]*'

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

* postgresql

Умирающие постоянные соединения при подключении создают ошибку:pg_query(): Cannot set connection to blocking mode
```bash
/etc/php/7.2/fpm/php.ini включить параметр:
pgsql.auto_reset_persistent = on
````
##### ORM
* Таблицы
Определение характеристик таблиц и полей производится указанием технических комментариев: 
Формирование таблицы:
```
Название|i
i - игнорировать таблицу
```
Модели таблиц, имена которых начинаются с `cms_` создаются в `/akcms/models`,
остальные считаются пользовательскими таблицами и их модели размещаются в `/akcms/u/models` 

* Поля
```
Название поля|>=cms_sections
>=cms_sections - Указывает что поле связано с указанной таблицей, по её первичному ключу
i - игнорировать поле
```
Генерация моделей производится командой
```
acli getStarted genModel
```

##### Шаблонизатор
`{#tmpl:parts/header#}` - Вставить шаблон parts/header.shtm

`{#_tmpl_left_menu:…:…#}` - вызвать функцию VisualTheme::_ph_tmpl_left_menu(&$pageData,$editMode,$text,$secId=336), где:
```text
* $pageData - данные текущей страницы
* $editMode - флаг режима редактирования
* $text - содержимое шаблон-тега
… явно указанные параметры, любое количество, в шаблоне разделаются двоеточиями
```

`{#ep:content:m#/}{/#ep:content:m#}` - редактируемое поле, где:
```text
* ep|eg - глобальный параметр|параметр только этой страницы
* ep:content,ep:namefull,any - заререзвированинные имена текущей страницы, хранятся в CmsSection
                               другие кобинации хранятся в таблице CmsSectionStrings
* m|s|l - многострочное поле|однострочное после|простая строка без переносов
```

`{#_tmpl_children:parts/novosti_lasttop:2:3:21:t#}` - вызов VisualThemeAbstract::_ph_tmpl_children c: 
```text
* $template - шаблоном parts/novosti_lasttop.shtm для каждого потомка, 
* $howchild - Как сортировать потомков
* $limit    - 3
* $sec_id   - Потомки для раздела: 
              -1|id - текущий|любой
* $skipthis - пропустить, если потомок по id совпадает с текущей страницей
```

`{#_tmpl_children_e:raskrinf_types:3:0:-1:f#}`, - вызов VisualThemeAbstract::_ph_tmpl_children_e c:
```text
* $template - шаблоном parts/raskrinf_types.php для каждого потомка,
* $howchild - Как сортировать потомков
* $limit    - 0
* $sec_id   - Потомки для раздела: 
              -1|id - текущий|любой
* $mode:
              a - один за одним, запуск для каждой сущности
              f - общий запуск, foreach необходимо выполнять вручную
```
 
Прочие готовые функции:
```text
{#_date:sec_from:d F Y#}
_ph_date: 
    * $field  - Обязательный. поле из шаблоны
    * $format - формат даты как в date, русский язык
_ph_text:
    * $field  - Обязательный. поле из шаблоны
    * $quote  - Формат кавычек:
                0 - не экранировать
                1 - одинарные
                2 - двойные
_ph_text_trunc:
    * $field  - Обязательный. поле из шаблоны
    * $quote  - Формат кавычек:
                0 - не экранировать
                1 - одинарные
                2 - двойные
    * $cnt    - Длина строки 
```

##### Важно знать
* [Learn Markdown](https://bitbucket.org/tutorials/markdowndemo), [Markdown-Cheatsheet](https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet)
* [Как работает opcache](https://habr.com/company/mailru/blog/310054/)
* [chroot](https://wiki.debian.org/chroot)
* [nginx location](https://nginx.org/ru/docs/http/ngx_http_core_module.html#location)
* [PostgreSql 10](https://postgrespro.ru/docs/postgrespro/10/)

##### TODO
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

##### Задачи для CRON 
* truncate -s 0 /data/nfs/*/logs/*.log - очистка всех логов во всех проектах

#### useful
* acli getStarted nginx && service nginx configtest && service nginx reload
* cls && acli getStarted nginx && service nginx configtest && service nginx reload && tail -n 0 -f /data/nfs/knpzken_ru/logs/*
* cls && tail -n 0 -f /data/nfs/project_name/logs/*