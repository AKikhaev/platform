alias cls='clear && echo -en "\e[3J"'
alias du1="du -h --max-depth=1 | sort -h"
alias du2="du -h --max-depth=2 | sort -h"
alias du="du -h "
alias df="df -h "
#alias acli="php akcms/core/acli.php"
alias itteka="php /usr/share/asterisk/agi-bin/_multifon.php"
alias bash_reload="unalias -a && . ~/.bashrc"

#cd ~/

pushd () {
    command pushd "$@" > /dev/null
}

popd () {
    command popd "$@" > /dev/null
}

_itteka_() {
	pushd /usr/share/asterisk/agi-bin
	php _multifon.php "$@"
	popd
}

function _acli_dir_fies_wo_ext(){
	if [ -d $1 ]; then
		local file suggests
		suggests="";
		for file in $(find $1/ -maxdepth 1 -type f -name \*$2); do
			#suggests=$suggests "$(basename "${file%.*}")";
			echo -n "$(basename "${file%.*}") ";
		done
		echo $suggests
	fi
}

function acli(){
	local PWD=$(pwd -P)
	if  [[ "$PWD" == "/data/nfs/"* ]] ; then
		local pwds
		readarray -d / -t pwds <<<"$PWD/"
		projectName=${pwds[3]}
		php /data/nfs/$projectName/public_html/akcms/core/acli.php "$@"
	else
		echo Not in site folder!
	fi
}

function _acli_complete_()
{
	local PWD=$(pwd -P)
	if  [[ "$PWD" == "/data/nfs/"* ]] ; then
		local pwds
		readarray -d / -t pwds <<<"$PWD/"
		projectName=${pwds[3]}

		local cmd="${1##*/}"
		local cur_word="${COMP_WORDS[COMP_CWORD]}"
		local prev_word="${COMP_WORDS[COMP_CWORD-1]}"
		local line=${COMP_LINE}

		case ${COMP_CWORD} in
			1)
				local suggestBase=$(_acli_dir_fies_wo_ext "/data/nfs/$projectName/public_html/akcms/cli" ".php")
				local suggestUser=$(_acli_dir_fies_wo_ext "/data/nfs/$projectName/public_html/akcms/u/cli" ".php")
				COMPREPLY=($(compgen -W "$suggestBase $suggestUser" -- $cur_word))
				;;
			*)
				local line=$(printf " %s" "${COMP_WORDS[@]:1}"); line=${line:1}
				local suggestAcli=$(php /data/nfs/$projectName/public_html/akcms/core/acli.php $line --silence_greetings -bash_completion --bash_completion_cword $cur_word)
				#echo $suggestAcli
				COMPREPLY=($(compgen -W "$suggestAcli" -- $cur_word))
#				case ${prev_word} in
#					analyze)
#						COMPREPLY=($(compgen -W "--help blame dump show" -- $cur_word))
#						;;
#					clean)
#						COMPREPLY=($(compgen -W "--help --logs --reboot --seed" -- $cur_word))
#						;;
#						COMPREPLY=($(compgen -W "--help --long --wait" -- $cur_word))
#						;;
#				esac
				;;
			#*)
			#	COMPREPLY=()
			#	;;
		esac


		#php /data/nfs/$projectName/public_html/akcms/core/acli.php


	else
		COMPREPLY=()
	fi
}

complete -F _acli_complete_ acli
