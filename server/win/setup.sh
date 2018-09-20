set -e
if grep -qE "(Microsoft|WSL)" /proc/version &> /dev/null ; then
  echo "Windows 10 Bash"
else
  echo "native Linux"
  exit
fi

mount --bind /mnt/d/Documents/Projects /data/nfs
cd ~/