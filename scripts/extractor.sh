#!/bin/sh

PDIR="$( pwd )/"
EXTENSION=$1
ROOT=$2

if [ -z "$ROOT" ]
then
    ROOT=${PDIR/scripts\//}
fi

echo "$ROOT"
ZIP="${ROOT}src/utilities/${EXTENSION}tmp/${EXTENSION}.tar.gz"
echo "$ZIP"
tar -zxvf $ZIP -C ${ROOT}src/utilities/
echo "Extracting tarbell..."
rm -fr ${ROOT}src/utilities/${EXTENSION}tmp -R
echo "Removing temporary files..."