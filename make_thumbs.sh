#!/bin/sh

thumbsdir=images/mythumbs

for imagepath in images/*.jpg
do
	thumbpath=$thumbsdir/`basename $imagepath`

	if [ ! -e $thumbpath ]; then
		convert $imagepath -resize x120 -quality 80 -interlace Plane $thumbpath
	fi
done
