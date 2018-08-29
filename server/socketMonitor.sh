scriptsDir=$(dirname "$(readlink -f "$0")") && . $scriptsDir/loadProjectData.sh

trap 'mv ${projectPhpSocket}.original ${projectPhpSocket}' EXIT INT TERM
mv ${projectPhpSocket} ${projectPhpSocket}.original && socat -v -lh UNIX-LISTEN:${projectPhpSocket},mode=777,reuseaddr,fork UNIX-CONNECT:${projectPhpSocket}.original
