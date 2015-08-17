#!/bin/bash

#variables
targetDirectory=""
background=""

opts=$(getopt -o d:b -l dir:,background -- "$@")
eval set -- "${opts}"
for i; do
	case "$i" in
		-d) targetDirectory="$2"; shift; shift;;
		--dir) targetDirectory="$2"; shift; shift;;
		-b) background=true; shift;;
		--background) background=true; shift;;
		--) shift; break;
	esac
done

if test "${background}" = true; then
	nohup "$0" -d "${targetDirectory}" >/dev/null 2>&1 &
	disown -a >/dev/null 2>&1
	exit 0
fi

if test -n "${targetDirectory}"; then
	cd "${targetDirectory}"
	IFS=$' \t\n'
	for repo in $(find \. -name \*.git -type d -prune)
	do
		export GIT_DIR="${repo}"
		git fetch -v origin +refs/heads/*:refs/heads/*
	done
else
	echo "Specify the directory which contains git repositories"
	echo "Example: $0 -d '/path/to/file'"
fi
