#!/bin/bash

TARGET_GIT_ROOT=""
background=""
opts=$(getopt -o d:b -l dir:,background -- "$@")

eval set -- "${opts}"
for i; do
	case "$i" in
		-d) TARGET_GIT_ROOT="$2"; shift; shift;;
		--dir) TARGET_GIT_ROOT="$2"; shift; shift;;
		-b) background=true; shift;;
		--background) background=true; shift;;
		--) shift; break;
	esac
done

echo $TARGET_GIT_ROOT
echo $background
