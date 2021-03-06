##Windows Subsytem Linux
##### Развертывание WSL
* Power shell
```bash
Enable-WindowsOptionalFeature -Online -FeatureName Microsoft-Windows-Subsystem-Linux
```
* [Ubuntu](https://www.microsoft.com/store/p/ubuntu/9nblggh4msv6)
* [ConEmu](https://conemu.github.io/en/Downloads.html). Настройки ниже
* `cmd`, `bash`, `sudo bash`
* Указать пользователя mstr, пароль значения не имеет
* `visudo`
* `mstr  ALL=(ALL) NOPASSWD:ALL` добавить для mstr
* или `%sudo  ALL=(ALL) NOPASSWD:ALL` добавить для всех
* Скопировать .bash_aliases и добавить туда строки:
```bash
declare -x DISPLAY="localhost:0.0"
declare -x PULSE_SERVER="tcp:127.0.0.1"
sudo mount --bind /mnt/d/Documents/Projects /data/nfs
cd ~/
```
##### Развертывание
* `add-apt-repository ppa:ondrej/php`
* `apt update`
* `apt upgrade`
* `apt install libfcgi-bin mc nginx socat php7.2-fpm php7.2-cli php7.2-common php7.2-curl php7.2-gd php7.2-json php7.2-mbstring php7.2-mysql php7.2-pgsql php7.2-xml p7zip-full`
* `apt install postgresql-10`
* ... и далее по инсрукции

##### Конфигурирование [ConEmu](https://conemu.github.io/en/Downloads.html)
* Параметры
```
Main -> Appearance
- Single instance mode (use existing window instead of running new instance)
- Multiple consoles in one ConEmu window
Main -> Task bar:
- Always show TSA icon
- Auto minimize to TSA
0 Close ConEmu with last tab
- Minimize on dosing last tab
- Hide to the TSA
0 Quit on dose (e.g. caption bar cross dicking)
Features -> Status bar
> Terminal modes
```
* Создать ярлык `ConEmu64.exe -run {bash}`
* Если терминал зпапущен другим способом:
  кликнуть в статус баре терминала на Terminal modes и включить XTerm, AppKeys

##### Использование

* `mc` работает только в станлартной консоли
* Но работать удобнее в ConEmu из-за сворачивания консоли в трей. При закрытии консоли WSL убивается. Поэтому важно:
* Запуск: `server/win/startAll.sh`
* Оcтановка: `server/win/stopAll.sh` (Обязательно!)
* Подключение [Xming](https://sourceforge.net/projects/xming/): `declare -x DISPLAY="localhost:0.0"`
* Подключение [PulseAudio](https://www.freedesktop.org/wiki/Software/PulseAudio/Ports/Windows/Support/): [manual](https://token2shell.com/howto/x410/enabling-sound-in-wsl-ubuntu-let-it-sing/), `declare -x PULSE_SERVER="tcp:127.0.0.1"`
* Путь к rootfs 

`%LOCALAPPDATA%\Packages\CanonicalGroupLimited.UbuntuonWindows_79rhkp1fndgsc\LocalState\rootfs`
