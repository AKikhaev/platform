#!/bin/sh

SCRIPT_FILENAME=/public_html/index.php \
REQUEST_URI=/ \
QUERY_STRING= \
REQUEST_METHOD=GET \
DOCUMENT_ROOT=/public_html \
SCRIPT_URL=/ \
USER="" \
HOME="" \
GATEWAY_INTERFACE=CGI/1.1 \
SERVER_SOFTWARE=nginx \
REMOTE_ADDR=127.0.0.1 \
REMOTE_PORT=7777 \
SERVER_ADDR=127.0.0.1 \
SERVER_PORT=80 \
SERVER_NAME=knpz-ken.ru.local \
cgi-fcgi -bind -connect /tmp/php-fpm-knpzkenru.sock