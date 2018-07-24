scriptsDir=$(dirname "$(readlink -f "$0")") && . $scriptsDir/loadProjectData.sh

# mount and crate needs tree
mkdir -p /data/nfs/$projectName/dev/pts
mount --bind /dev/pts /data/nfs/$projectName/dev/pts
#mount /dev/tty1 /data/nfs/$name/dev/tty1

mkdir -p /data/nfs/$projectName/cache
mkdir -p /data/nfs/$projectName/logs
mkdir -p /data/nfs/$projectName/tmp
mkdir -p /data/nfs/$projectName/var/lib/php/sessions

mkdir -p /data/nfs/$projectName/etc
cp /etc/hosts /data/nfs/$projectName/etc/
cp /etc/resolv.conf /data/nfs/$projectName/etc/

mkdir -p /data/nfs/$projectName/lib
cp /lib/x86_64-linux-gnu/libnss_dns* /data/nfs/$projectName/lib/

mkdir -p /data/nfs/$projectName/usr/share/zoneinfo/Europe
cp /usr/share/zoneinfo/Europe/Moscow /data/nfs/$projectName/usr/share/zoneinfo/Europe/

