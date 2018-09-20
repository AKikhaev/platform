executeScriptDir=$(dirname "$(readlink -f "$0")")/.. && . $executeScriptDir/loadProjectData.sh

set -e
if grep -qE "(Microsoft|WSL)" /proc/version &> /dev/null ; then
    echo "Windows 10 Bash"
else
    echo "native Linux"
    exit
fi

if pgrep -x "socat" > /dev/null
then
    pkill socat
    mv ${projectPhpSocket}.original ${projectPhpSocket}
else
    echo "Already Stopped"
fi

service nginx stop
service php7.2-fpm stop
service postgresql stop