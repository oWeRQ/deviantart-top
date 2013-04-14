#!/bin/sh

root=$PWD

find $root/images/ -type f -size 0 -exec rm {} \;

php clean_images.php

while read file
do
	mv $file images/trash/
done < trash_images.txt

cd $root/images/
wget --no-verbose -ci $root/images.txt

cd $root
./make_thumbs.sh

php make_keywords.php
php make_sig.php