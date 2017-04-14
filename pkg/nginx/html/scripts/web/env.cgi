#!/bin/bash
echo -e ":\n"

echo "<html>"
echo "<head><title>env</title></head>"
echo "<body>"
echo "<pre>"

declare -x | sed 's/^declare -x //'

echo "</pre>"
echo "</body>"
echo "</html>"
