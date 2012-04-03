#!/bin/sh

PDIR="$( pwd )/"

echo -e "\n\n*************** Installing Helium framework! ***************"

echo -e "\n\nPlease enter a domain name for this installation (e.g. phphelium.com) [ENTER]: "
read SERVNAME

if [ -z "$SERVNAME" ]
then
    echo -e "\n\n !!!!!!!!!!!!! You must supply a domain name. Script terminating. !!!!!!!!!!!!!"
    exit
fi

echo -e "\nPlease enter a location to send sources, or leave blank for current location [ENTER]: "
read SRC

if [ -z "$SRC" ]
then
    SRC="$PDIR"
    MOVE=""
else
    MOVE="yes"
    cd ..
fi

echo -e "\nPlease enter MySQL server address, or leave blank to create local server [ENTER]: "
read MYSERV

if [ -z "$MYSERV" ]
then
    MYSERV="localhost"
fi

echo -e "\nPlease enter your MySQL root username, or blank for root [ENTER]: "
read MYUSER

if [ -z "$MYUSER" ]
then
    MYUSER="root"
fi

echo -e "\nPlease enter a MySQL user password and press [ENTER]: "
read MYPASS

if [ -z "$MYPASS" ]
then
    echo -e "\n\n !!!!!!!!!!!!! You must supply a MySQL password. Script terminating. !!!!!!!!!!!!!"
    exit
fi

echo -e "\n\n...Begin installation...\n\n"

apt-get update
apt-get install -y apache2 php5-mysql libapache2-mod-php5

if [ "$MYSERV" -ne "localhost" ]
then
    echo "mysql-server mysql-server/root_password select $MYPASS" | debconf-set-selections
    echo "mysql-server mysql-server/root_password_again select $MYPASS" | debconf-set-selections

    apt-get install -y mysql-server
fi

apt-get install -y php-pear php5-dev apache2-dev memcached
apt-get install -y php5-memcache php-apc

printf "\n" | pecl install apc
printf "\n" | pecl install memcache

cd "$PDIR"

if [ -z "$MOVE" ]
then
    cd ..
    echo -e "\n...Leaving files where they are..."
else
    echo -e "\n...Moving files to $SRC..."

    mkdir "$SRC"
    chmod 777 "$SRC"
    cd ..
    mv * "$SRC"
    cd "$SRC"
fi

echo -e "\n\nModifying core files in $SRC"

sed -e "s|{RDIR}|$SRC|g;s|{MYPASS}|$MYPASS|g;s|{MYUSER}|$MYUSER|g;s|{MYSERV}|$MYSERV|g;s|{SERVNAME}|$SERVNAME|g" <settings.ini.default >settings.ini
chmod 777 settings.ini
rm settings.ini.default

sed -e "s|{RDIR}|$SRC|g;s|{SERVNAME}|$SERVNAME|g" <helium.conf.default >helium.conf
chmod 777 helium.conf
mv helium.conf /etc/apache2/sites-enabled/
rm helium.conf.default

sed -e "s|{RDIR}|$SRC|g" <cron.default >cron

a2enmod rewrite

cd "$SRC/scripts/"
php buildModel.php Pages
php buildModel.php EmailQueue
php buildModel.php References
php buildModel.php Users

echo "*/2 * * * * php $SRCscripts/emailer.php" | crontab -

/etc/init.d/apache2 restart

echo -e "\n\nInstallation complete!"
echo -e "\n\nNOTE: You may need to append your php.ini file with the follow:"
echo -e "\n\nextension=apc.so\nextension=memcache.so"