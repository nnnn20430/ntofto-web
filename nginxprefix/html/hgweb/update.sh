#!/bin/bash
SCRIPT_ROOT=$(cd "${0%/*}" && echo $PWD)

IFS=$' \t\n'
for repo in $(find root/mirrors -name *.hg -type d -prune)
do
	cd $SCRIPT_ROOT
	cd $repo/..
	hg -y pull
	hg -y update null
done
