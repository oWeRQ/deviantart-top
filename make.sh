#!/bin/sh

root=$PWD

find cache/ -type f -size 0 -exec rm {} \;
find images/ -type f -size 0 -exec rm {} \;

php getfavs.php
php getprofiles.php

cd $root/images/
wget -ci $root/images.txt
cd $root/middle/
wget -ci $root/middle.txt
cd $root/thumbs/
wget -ci $root/thumbs.txt

cd $root
./make_thumbs.sh
