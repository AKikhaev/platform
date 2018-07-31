#scriptsDir - scpipt source dir
#projectRoot - all project root dir
#projectHome - ../public_html
#projectName - extract name from ../{projectName}/public_html
#projectNameShort - name without spaces, dashes instead tabs,dots

#scriptsDir=$(dirname "$(readlink -f "$0")")
projectRoot=$(dirname "$scriptsDir")
projectHome=$(dirname "$scriptsDir")/public_html
projectName=$(basename "$(dirname "$scriptsDir")")
projectNameShort=`echo $name | sed 's/[ \t\.-]//g'`

#to call from another script paste at the begin:
#scriptsDir=$(dirname "$(readlink -f "$0")")
#. $scriptsDir/getProjectData.sh

perform_socket_query()
{
#phpSocket=/data/nfs/$projectName/tmp/php-fpm-${projectName}.sock
phpSocket=/tmp/php-fpm-${projectName}.sock

SCRIPT_FILENAME="$1" \
SCRIPT_NAME="$1" \
HTTP_HOST=${projectName} \
REQUEST_URI=/ \
QUERY_STRING="$2" \
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
cgi-fcgi -bind -connect "$phpSocket"

}