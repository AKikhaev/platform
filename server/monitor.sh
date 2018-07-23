name=knpz-ken.ru
nameShort=`echo $name | sed 's/[ \t\.-]/_/g'`
echo $nameShort
#touch /data/nfs/$name/logs/terminal && chmod 666 /data/nfs/$name/logs/terminal && truncate --size 0 /data/nfs/$name/logs/terminal
#trap "rm /data/nfs/$name/logs/terminal" EXIT INT TERM
#tail -f /data/nfs/$name/logs/terminal
