#!/bin/sh

root=$PWD

find images/ -type f -size 0 -exec rm {} \;

php getgalleries.php
php getfavs.php $@
./make_images.sh

php getprofiles.php
