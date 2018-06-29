name=knpz-ken.ru
nameShort=`echo $name | sed 's/[ \t\.-]//g'`

# mount project to point
mkdir -p /data/nfs/$name
mount --bind /mnt/d/Documents/Projects/$name /data/nfs/$name

# mount and crate needs tree
mkdir -p /data/nfs/$name/dev/pts && mount --bind /dev/pts /data/nfs/$name/dev/pts
mkdir -p /data/nfs/$name/cache
mkdir -p /data/nfs/$name/logs
mkdir -p /data/nfs/$name/tmp
mkdir -p /data/nfs/$name/var/lib/php/sessions

mkdir -p /data/nfs/$name/etc
cp /etc/hosts /data/nfs/$name/etc/
cp /etc/resolv.conf /data/nfs/$name/etc/

mkdir -p /data/nfs/$name/lib
cp /lib/x86_64-linux-gnu/libnss_dns* /data/nfs/$name/lib/

mkdir -p /data/nfs/$name/usr/share/zoneinfo/Europe
cp /usr/share/zoneinfo/Europe/Moscow /data/nfs/$name/usr/share/zoneinfo/Europe/

service php7.0-fpm start
service nginx start
mv /tmp/php-fpm-$nameShort.sock /tmp/php-fpm-$nameShort.sock.original
socat UNIX-LISTEN:/tmp/php-fpm-$nameShort.sock,mode=777,reuseaddr,fork UNIX-CONNECT:/tmp/php-fpm-$nameShort.sock.original &
