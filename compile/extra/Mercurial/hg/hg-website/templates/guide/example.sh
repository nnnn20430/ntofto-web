#!/bin/sh

# Example script which shows the actions in the workflow guide.


# make sure , project does not exist before starting.
rm -r project feature1

# plain output
export HGPLAIN=1

echo init, add and commit

hg init project
cd project
echo "[ui]" >> .hg/hgrc
echo "username = Mr. Johnson <johnson@smith.com>" >> .hg/hgrc
echo 'print("Hello")' > hello.py
hg add
#hg commit
hg commit --date "2011-11-20 11:00" -m "Initial commit."
hg log

echo status

echo 'print("Hello World")' > hello.py
hg status
hg diff
#hg commit
hg commit --date "2011-11-20 11:11" -m "Say Hello World, not just Hello."
hg log

echo move and copy

hg cp hello.py copy
hg mv hello.py target
hg status
hg diff
hg ci --date "2011-11-20 11:20" -m "Copy and move."

echo log
hg log

echo Lone developer with nonlinear history

hg update 1
hg identify -n

echo 'print("Hello Mercurial")' > hello.py
hg ci --date "2011-11-20 20:00" -m "Greet Mercurial"
hg merge
hg ci --date "2011-11-20 20:11" -m "merge greeting and copy+move."

hg log

cd ..
hg clone project feature1
cd feature1
hg update 3
echo 'print("Hello feature1")' > hello.py
hg commit --date "2011-11-20 20:11:11" -m "Greet feature1"
cd ../project
hg in ../feature1
hg pull ../feature1
hg merge
hg commit --date "2011-11-20 20:20" -m "merged feature1"
hg log -r -1:-3
hg rollback
hg commit --date "2011-11-20 20:20:11 +1100" -m "Merged Feature 1"
hg log -r -1:-2

echo sharing changes
hg out ../feature1