##Windows Subsytem Linux
##### Развертывание WSL
* Power shell
```bash
Enable-WindowsOptionalFeature -Online -FeatureName Microsoft-Windows-Subsystem-Linux
```
* [Ubuntu](https://www.microsoft.com/store/p/ubuntu/9nblggh4msv6)
* [ConEmu](https://conemu.github.io/en/Downloads.html). Options->task bar->TSA always show, TSA auto minimize
* `cmd`, `bash`, `sudo bash`
* Указать пользователя mstr, пароль значения не имеет
* `visudo`
* `mstr  ALL=(ALL) NOPASSWD:ALL` добавить для mstr
* или `%sudo  ALL=(ALL) NOPASSWD:ALL` добавить для всех
* Скопировать .bash_aliases и добавить туда строки:
```bash
mount --bind /mnt/d/Documents/Projects /data/nfs
cd /data/nfs
```
##### Развертывание
* `add-apt-repository ppa:ondrej/php`
* `apt update`
* `apt upgrade`
* `apt install libfcgi-bin mc nginx socat php7.2-fpm php7.2-cli php7.2-common php7.2-curl php7.2-gd php7.2-json php7.2-mbstring php7.2-mysql php7.2-pgsql php7.2-xml p7zip-full`
* `apt install postgresql-10`
* ... и далее по инсрукции

##### Использование
* `mc` работает только в станлартной консоли
* Но работать удобнее в ConEmu из-за сворачивания консоли в трей. При закрытии консоли WSL убивается. Поэтому важно:
* Запуск: `server/win/startAll.sh`
* Отановка: `server/win/stopAll.sh` (Обязательно!)
