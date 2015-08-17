#!/bin/bash

#variables
gitRepoUrl=""
gitwebIndexUrl=""
targetDirectory=""
background=false

opts=$(getopt -o r:i:d:b -l repo:,index:,dir:,background -- "$@")
eval set -- "${opts}"
for i; do
	case "$i" in
		-r) gitRepoUrl="$2"; shift; shift;;
		--repo) gitRepoUrl="$2"; shift; shift;;
		-i) gitwebIndexUrl="$2"; shift; shift;;
		--index) gitwebIndexUrl="$2"; shift; shift;;
		-d) targetDirectory="$2"; shift; shift;;
		--dir) targetDirectory="$2"; shift; shift;;
		-b) background=true; shift;;
		--background) background=true; shift;;
		--) shift; break;
	esac
done

if test "${background}" = true; then
	nohup "$0" -i "${gitwebIndexUrl}" >/dev/null 2>&1 &
	disown -a >/dev/null 2>&1
	exit 0
fi

if test -n "${gitRepoUrl}" && test -n "${gitwebIndexUrl}" && test -n "${targetDirectory}"; then
	gitwebIndex="$(wget -q -O- ${gitwebIndexUrl})."
	IFS=$'\n'
	for repo in ${gitwebIndex}; do
		repo=$(echo -n $repo | cut -f1 -d" ")
		git clone --bare ${gitRepoUrl}${repo} ${targetDirectory}/${repo}
	done
else
	echo "Specify the git repo url, gitweb index url and destination directory"
	echo "Example: $0 -r 'git://git.sv.gnu.org/' -i 'http://git.savannah.gnu.org/gitweb/?a=project_index' -d '/destination/dir'"
fi
	
