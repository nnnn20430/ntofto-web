#!/bin/bash
CGIT_ROOT=$(cd "${0%/*}" && echo $PWD)
../../../server/tools/gitrepo_bare_update.sh "$CGIT_ROOT/root"
