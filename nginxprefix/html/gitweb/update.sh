#!/bin/bash

export GIT_TERMINAL_PROMPT=0

IFS=$' \t\n'
for repo in $(find root/mirrors -name *.git -type d -prune)
do
	export GIT_DIR="./${repo}"
	git fetch -v origin +refs/heads/*:refs/heads/*
done
