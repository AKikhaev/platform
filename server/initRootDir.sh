scriptsDir=$(dirname "$(readlink -f "$0")") && . $scriptsDir/loadProjectData.sh

# mount and crate needs tree
mkdir -p /data/nfs/$projectName/dev/pts
umount /data/nfs/$projectName/dev/pts > /dev/null
mount --bind /dev/pts /data/nfs/$projectName/dev/pts
#mount /dev/tty1 /data/nfs/$name/dev/tty1

mknod -m 0644 /data/nfs/$projectName/dev/random c 1 8
mknod -m 0644 /data/nfs/$projectName/dev/urandom c 1 9
chown root:root /data/nfs/$projectName/dev/random /data/nfs/$projectName/dev/urandom

mkdir -p -m 0777 /data/nfs/$projectName/cache
chown mstr:www-user /data/nfs/$projectName/cache
mkdir -p -m 0777 /data/nfs/$projectName/logs
mkdir -p -m 0777 /data/nfs/$projectName/tmp
chown mstr:www-user /data/nfs/$projectName/tmp
mkdir -p -m 0777 /data/nfs/$projectName/var/lib/php/sessions
chown mstr:www-user /data/nfs/$projectName/var/lib/php/sessions

mkdir -p /data/nfs/$projectName/etc
cp /etc/hosts /data/nfs/$projectName/etc/
cp /etc/resolv.conf /data/nfs/$projectName/etc/
wget -O /data/nfs/$projectName/etc/cacert.pem https://curl.haxx.se/ca/cacert.pem

mkdir -p /data/nfs/$projectName/lib
cp /lib/x86_64-linux-gnu/libnss_dns* /data/nfs/$projectName/lib/

mkdir -p /data/nfs/$projectName/usr/share/zoneinfo/Europe
cp /usr/share/zoneinfo/Europe/Moscow /data/nfs/$projectName/usr/share/zoneinfo/Europe/

chown -R mstr:www-user /data/nfs/$projectName/public_html/s/
chown -R mstr:www-user /data/nfs/$projectName/public_html/img/gallery/
chown -R mstr:www-user /data/nfs/$projectName/public_html/img/pages/

echo
echo "Add this line to /etc/fstab (for auto terminal notification feature):"
echo /dev/pts /data/nfs/$projectName/dev/pts auto bind 0 0
