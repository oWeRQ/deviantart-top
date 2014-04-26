#!/bin/sh

thumbsdir=images/mythumbs

for imagepath in images/original/*.jpg
do
	thumbpath=$thumbsdir/`basename $imagepath`

	if [ ! -e $thumbpath ]; then
		convert $imagepath -resize x120 -quality 85 -interlace Plane $thumbpath || mv $imagepath images/corrupted/
	fi
done

for imagepath in images/original/*.png images/original/*.gif
do
	thumbpath=$thumbsdir/`basename $imagepath`

	if [ ! -e $thumbpath ]; then
		convert $imagepath -resize x120 $thumbpath || mv $imagepath images/corrupted/
	fi
done
