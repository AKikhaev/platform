alias cls='clear && echo -en "\e[3J"'
alias du1="du -h --max-depth=1 | sort -h"
alias du2="du -h --max-depth=2 | sort -h"
alias du="du -h "
alias df="df -h "
#alias acli="php akcms/core/acli.php"
alias itteka="php /usr/share/asterisk/agi-bin/_multifon.php"
alias bash_reload="unalias -a && . ~/.bashrc"

pushd () {
    command pushd "$@" > /dev/null
}

popd () {
    command popd "$@" > /dev/null
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
		local line_full=${COMP_LINE}
		local line=$(printf " %s" "${COMP_WORDS[@]:1}"); line=${line:1}

		local suggestAcli=$(php /data/nfs/$projectName/public_html/akcms/core/acli.php $line --silence_greetings --bash_completion_cword $cur_word)
		COMPREPLY=($(compgen -W "$suggestAcli" -- $cur_word))
	else
		COMPREPLY=()
	fi
}

complete -F _acli_complete_ acli
