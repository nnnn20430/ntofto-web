#!/bin/bash

exec 9<$0
flock -n 9 || exit 1

SCRIPT_ROOT=$(cd "${0%/*}" && echo "$PWD")
cd "$SCRIPT_ROOT"

IFS=$' \t\n'
for repo in $(find root/mirrors -name *.hg -type d -prune)
do
	cd $SCRIPT_ROOT
	cd $repo/..
	hg -y pull
	hg -y update null
done
