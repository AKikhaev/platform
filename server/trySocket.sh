scriptsDir=$(dirname "$(readlink -f "$0")") && . $scriptsDir/loadProjectData.sh

perform_socket_query "/public_html/index.php"
echo
echo

