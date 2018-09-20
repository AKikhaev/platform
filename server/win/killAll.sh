set -e
if grep -qE "(Microsoft|WSL)" /proc/version &> /dev/null ; then
  echo "Windows 10 Bash"
else
  echo "native Linux"
  exit
fi

killall -r '.*'
