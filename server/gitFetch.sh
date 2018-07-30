scriptsDir=$(dirname "$(readlink -f "$0")") && . $scriptsDir/loadProjectData.sh
cd ..
git --git-dir=/home/mstr/cms.git fetch
git --git-dir=/home/mstr/cms.git pull
