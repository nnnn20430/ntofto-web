#!/bin/bash
#./hgweb.cgi | dd bs=1c skip=20 status=none
./hgweb.cgi | tail -n +2
