#!/bin/sh

root=$PWD

#find cache/ -type f -size 0 -exec rm {} \;
find images/ -type f -size 0 -exec rm {} \;

php getfavs.php
php getprofiles.php

cp data/images.json $root/backup/images.json_`date +%F`
cp data/profiles.json $root/backup/profiles.json_`date +%F`

cd $root/images/original/
wget -ci $root/images.txt

cd $root
./make_thumbs.sh
