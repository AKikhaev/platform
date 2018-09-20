executeScriptDir=$(dirname "$(readlink -f "$0")")/.. && . $executeScriptDir/loadProjectData.sh

set -e
if grep -qE "(Microsoft|WSL)" /proc/version &> /dev/null ; then
    echo "Windows 10 Bash"
else
    echo "native Linux"
    exit
fi

service postgresql start
service php7.2-fpm start
service nginx start

if pgrep -x "socat" > /dev/null
then
    echo "Already Running"
else
    mv ${projectPhpSocket} ${projectPhpSocket}.original
    socat UNIX-LISTEN:${projectPhpSocket},mode=777,reuseaddr,fork UNIX-CONNECT:${projectPhpSocket}.original &
fi

