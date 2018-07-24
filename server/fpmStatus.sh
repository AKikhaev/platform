scriptsDir=$(dirname "$(readlink -f "$0")") && . $scriptsDir/loadProjectData.sh

perform_socket_query "/status" "full"
echo
echo 
