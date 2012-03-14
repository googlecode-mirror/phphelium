#!/bin/sh

PDIR="$( pwd )/"
ROOT=${PDIR/scripts\//}
EXTENSION=$1

ZIP="${ROOT}src/utilities/${EXTENSION}tmp/${EXTENSION}.tar.gz"
echo "$ZIP"
tar -zxvf $ZIP -C ${ROOT}src/utilities/
rm -fr ${ROOT}src/utilities/${EXTENSION}tmp -R