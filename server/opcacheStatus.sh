scriptsDir=$(dirname "$(readlink -f "$0")") && . $scriptsDir/loadProjectData.sh

perform_socket_query "/server/opcacheStatus.php"
echo
echo 
