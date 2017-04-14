#!/bin/bash

exec 9<$0
flock -n 9 || exit 1

SCRIPT_ROOT=$(cd "${0%/*}" && echo "$PWD")
cd "$SCRIPT_ROOT"

export GIT_TERMINAL_PROMPT=0

IFS=$' \t\n'
for repo in $(find root/mirrors -name *.git -type d -prune)
do
	export GIT_DIR="./${repo}"
	git fetch -v origin +refs/heads/*:refs/heads/*
done
