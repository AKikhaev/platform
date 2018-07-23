#scriptsDir - scpipt source dir
#projectRoot - all project root dir
#projectHome - ../public_html
#projectName - extract name from ../{projectName}/public_html
#projectNameShort - name without spaces, dashes instead tabs,dots

#scriptsDir=$(dirname "$(readlink -f "$0")")
projectRoot=$(dirname "$scriptsDir")
projectHome=$(dirname "$scriptsDir")/public_html
projectName=$(basename "$(dirname "$scriptsDir")")
projectNameShort=`echo $name | sed 's/[ \t\.-]//g'`

#to call from another script paste at the begin:
#scriptsDir=$(dirname "$(readlink -f "$0")")
#. $scriptsDir/getProjectData.sh