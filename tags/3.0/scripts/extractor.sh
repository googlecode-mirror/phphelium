#!/bin/sh

PDIR="$( pwd )/"
EXTENSION=$1
ROOT=$2

if [ -z "$ROOT" ]
then
    ROOT=${PDIR/scripts\//}
fi

echo "$ROOT"
ZIP="${ROOT}utilities/${EXTENSION}tmp/${EXTENSION}.tar.gz"
echo "$ZIP"
tar -zxvf $ZIP -C ${ROOT}utilities/
echo "Extracting tarbell..."
rm -fr ${ROOT}utilities/${EXTENSION}tmp -R
echo "Removing temporary files..."