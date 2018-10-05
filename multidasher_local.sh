#!/bin/bash
#git clone git@github.com:git@github.com:Chainfrog-dev/multidasher.git 

echo ""
echo "------------------------------------------------"
echo "Fixed locale                                    "
echo "------------------------------------------------"
echo ""
export LC_ALL="en_US.UTF-8"
export LC_CTYPE="en_US.UTF-8"
dpkg-reconfigure locales

echo ""
echo "------------------------------------------------"
echo "Update server                                   "
echo "------------------------------------------------"
echo ""
apt-get update
apt-get upgrade

echo ""
echo "-----------------------------------------------"
echo "Install NGINX 								 "
echo "-----------------------------------------------"
echo ""

if ! which nginx > /dev/null 2>&1; then
    echo "Nginx not installed -- installing"
    apt-get -qy install nginx
else
	echo "Nginx installed -- skipping"
    apt-get -qy install nginx

echo ""
echo "-----------------------------------------------------------------"
echo "Installing MultiChain                                            "
echo "-----------------------------------------------------------------"
echo ""
# Check whether we need to install MultiChain
if test -x /usr/local/bin/multichaind ; then
	echo "MultiChain already installed"
else
	cd /tmp
	wget http://www.multichain.com/download/multichain-1.0.2.tar.gz
	tar -xvzf multichain-1.0.2.tar.gz
	cd multichain-1.0.2
	mv multichaind multichain-cli multichain-util /usr/local/bin
	cd ~
fi

echo ""
echo "-----------------------------------------------"
echo "Install php && related packages				 "
echo "-----------------------------------------------"
echo ""
if ! grep -q "^deb .*ppa:ondrej/php" /etc/apt/sources.list /etc/apt/sources.list.d/*; then
	add-apt-repository ppa:ondrej/php
	apt-get update
fi
apt-get -y install curl php-cli php-mbstring git unzip php7.2 php7.2-curl php7.2-gd php7.2-mbstring php7.2-xml php7.2-json php7.2-mysql php7.2-opcache php7.2-fpm

echo ""
echo "-----------------------------------------------"
echo "Install mysql & database								 "
echo "-----------------------------------------------"
echo ""
if type mysql >/dev/null 2>&1 ; then
	echo "mysql installed"
else 
	sudo apt-get -y install mysql-server

if mysqlshow "multidasher" > /dev/null 2>&1 ; then
	 echo "Database exists."
else
	 echo "Database doesn't exist."
     mysql -e "CREATE DATABASE multidasher /*\!40100 DEFAULT CHARACTER SET utf8 */;"
	 mysql -e "CREATE USER drupal@localhost IDENTIFIED BY 'drupal';"
	 mysql -e "GRANT ALL PRIVILEGES ON multidasher.* TO 'drupal'@'localhost';"
	 mysql -e "FLUSH PRIVILEGES;"
	 mysql -udrupal -pdrupal multidasher < '/var/www/multidasher/database/db.sql'
fi

echo ""
echo "-----------------------------------------------"
echo "Install certbot 								 "
echo "-----------------------------------------------"
echo ""
if ! grep -q "^deb .*ppa:certbot/certbot" /etc/apt/sources.list /etc/apt/sources.list.d/*; then
	add-apt-repository ppa:certbot/certbot
	apt-get update
fi
apt-get install -qy python-certbot-nginx

echo ""
echo "-----------------------------------------------"
echo "Install multichain     						 "
echo "-----------------------------------------------"
echo ""
# Check whether we need to install MultiChain
if test -x /usr/local/bin/multichaind ; then
	echo MultiChain already installed
else
	echo ""
	echo "-----------------------------------------------------------------"
	echo "Installing MultiChain                                            "
	echo "-----------------------------------------------------------------"
	echo ""
	cd /tmp
	wget https://www.multichain.com/download/multichain-2.0-alpha-5.tar.gz
	tar -xvzf multichain-2.0-alpha-5.tar.gz
	cd multichain-2.0-alpha-5
	mv multichaind multichain-cli multichain-util /usr/local/bin
	cd ~
fi

echo ""
echo "-----------------------------------------------"
echo "Install compoesr     						 "
echo "-----------------------------------------------"
echo ""
if composer -v > /dev/null 2>&1 ; then
	cd ~
	curl -sS https://getcomposer.org/installer -o composer-setup.php
	sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
else 
	'composer already installed'
fi

cd /var/www/multidasher
php composer install

# not needed on local install
# echo ""
# echo "-----------------------------------------------"
# echo "Configure firewall     						 "
# echo "-----------------------------------------------"
# echo ""
# ufw allow OpenSSH
# ufw allow in 443/tcp comment "https: for certbot"
# ufw allow 'Nginx HTTP'
# ufw enable
# ufw status

if grep -Fxq "127.0.0.1	multidasher.local.com" /etc/hosts ; then
    echo "site already exists."
else
	echo '127.0.0.1	multidasher.local.com' >> /etc/hosts
fi

ln -s /var/www/multidasher/config/multichain.local.nginx /etc/sites-enabled/
service nginx restart
echo 'installation complete, you can now connect to your site on "http://multidasher.local.com"'
# echo ""
# echo "-----------------------------------------------"
# echo "Installing nodejs     						 "
# echo "-----------------------------------------------"
# echo ""
# curl -sL https://deb.nodesource.com/setup_10.x | bash - 
# apt-get -qy install nodejs

# echo "Linking /usr/bin/nodejs to usr/bin/node" 
# ln -s /usr/bin/nodejs /usr/bin/node
# apt-get -qy install libtool pkg-config build-essential autoconf automake

echo ""
echo "-----------------------------------------------"
echo "Done       						             "
echo "-----------------------------------------------"
echo ""



