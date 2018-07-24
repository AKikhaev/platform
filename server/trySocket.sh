scriptsDir=$(dirname "$(readlink -f "$0")")
. $scriptsDir/getProjectData.sh

SCRIPT_FILENAME=/public_html/index.php \
HTTP_HOST=${projectName} \
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
cgi-fcgi -bind -connect /data/nfs/$projectName/tmp/php-fpm-${projectName}.sock
#cgi-fcgi -bind -connect /data/nfs/zi.ru/tmp/php-fpm.sock

echo
echo 

#SCRIPT_FILENAME=/status \
#SCRIPT_NAME=/status \
#REQUEST_METHOD=GET \
#QUERY_STRING=full \
#cgi-fcgi -bind -connect /data/nfs/zi.ru/tmp/php-fpm.sock
#cgi-fcgi -bind -connect /data/nfs/$projectName/tmp/php-fpm-${projectName}.sock

echo 
echo 
