#!/bin/sh

root=$PWD

find images/ -type f -size 0 -exec rm {} \;

cp data/images.json $root/backup/images.json_`date +%F`
cp data/profiles.json $root/backup/profiles.json_`date +%F`

php getgalleries.php
php getfavs.php $@
./make_images.sh

php getprofiles.php
