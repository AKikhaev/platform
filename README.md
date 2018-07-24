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
* PHP 7+: [supported-versions](http://php.net/supported-versions.php)
```bash
dpkg -l | grep php

sudo add-apt-repository ppa:ondrej/php
sudo apt update

apt-get install php7.2-cli php7.2-common php7.2-curl php7.2-fpm php7.2-gd php7.2-json php7.2-mbstring php7.2-mcrypt php7.2-mysql php7.2-pgsql

apt-get install php7.2-zip php7.2-readline php7.2-opcache 
```
###### Важно знать
* [Learn Markdown](https://bitbucket.org/tutorials/markdowndemo), [Markdown-Cheatsheet](https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet)


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
