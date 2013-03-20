#!/bin/sh

root=$PWD

#find cache/ -type f -size 0 -exec rm {} \;
find images/ -type f -size 0 -exec rm {} \;

php getfavs.php
php getprofiles.php

cp images_by_author.json $root/backup/images_by_author.json_`date +%F`
cp profiles.json $root/backup/profiles.json_`date +%F`

cd $root/images/
wget -ci $root/images.txt
cd $root/images/middle/
wget -ci $root/middle.txt
cd $root/images/thumbs/
wget -ci $root/thumbs.txt

cd $root
./make_thumbs.sh
