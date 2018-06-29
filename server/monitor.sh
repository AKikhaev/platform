mv /tmp/php-fpm-knpzkenru.sock /tmp/php-fpm-knpzkenru.sock.original
socat -x -v UNIX-LISTEN:/tmp/php-fpm-knpzkenru.sock,mode=777,reuseaddr,fork UNIX-CONNECT:/tmp/php-fpm-knpzkenru.sock.original
mv /tmp/php-fpm-knpzkenru.sock.original /tmp/php-fpm-knpzkenru.sock
