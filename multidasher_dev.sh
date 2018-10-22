#!/bin/bash
#git clone https://github.com/Chainfrog-dev/multidasher.git

## Commands
# AWS: scp -i /home/USER/.ssh/KEY.pem /var/www/multidasher/multidasher_server.sh ubuntu@YOURIP:/home/ubuntu
# DO: scp /var/www/multidasher/multidasher_server.sh root@YOURIP:/home/root
# AWS ssh -i /home/USER/.ssh/KEY.pem ubuntu@YOURIP
# DO: ssh root@YOURIP

if [ -z $BASH_VERSION ] ; then
	echo -e "You must run this script using bash." 1>&2
	exit 1
fi

# Make sure we are running as root
if [[ $EUID -ne 0 ]]; then
	echo -e "This script must be run as root." 1>&2
	exit 1
fi

echo -e ""
echo -e "--------------------------------------------------------------------------------"
echo -e "THIS IS A SCRIPT TO HELP DEVELOPERS INSTALL A LOCAL COPY OF MULTIDASHER FOR "
echo -e "DEVELOPMENT PURPOSES. DO NOT USE UNLESS YOU ARE A CODE CONTRIBUTOR. USE"
echo -e "./multidasher_server.sh INSTEAD!"
echo -e "--------------------------------------------------------------------------------"
echo -e ""

read -p "Press Enter to continue"
echo -e ""

read -p $'Enter a made-up domain name like multidasher.local.com\x0aMultiDasher installer will write this to your /etc/hosts file. \x0a=> ' domain
if [ -z $domain ] ; then
	echo -e "Non-domain installations not supported. Exiting..."
	exit 1
fi
read -p $'\x0aSelect a NEW user to be configured in MySQL: ' uservar
if [ -z $uservar ] ; then
	echo -e "MySQL user is required. Exiting..."
	exit 1
fi
while true; do
    read -sp 'Select a password to be configured for user in MySQL: ' passvar
    echo
    read -s -p "Password (again): " password2
    echo
    [ "$passvar" = "$password2" ] && break || echo "Passwords do not match."
done
if [ -z $passvar ] ; then
	echo -e "MySQL password is required. Exiting..."
	exit 1
fi

echo

while true; do
    read -sp $'\x0aSelect a password for user admin in Drupal: ' drupalpassword
    echo
    read -s -p "Password (again): " password2
    echo
    [ "$drupalpassword" = "$password2" ] && break || echo "Passwords do not match."
done
if [ -z $drupalpassword ] ; then
	echo -e "Drupal password cannot be blank. Exiting..."
	exit 1
fi

echo -e ""
echo -e "--------------------------------------------------------------------------------"
echo -e "Updating server                                   "
echo -e "--------------------------------------------------------------------------------"
echo -e ""
apt-get -y update
apt-get -y upgrade

echo -e ""
echo -e "--------------------------------------------------------------------------------"
echo -e "Installing NGINX 								 "
echo -e "--------------------------------------------------------------------------------"
echo -e ""

if ! which nginx > /dev/null 2>&1; then
    echo -e "Nginx not installed -- installing."
    apt-get -qy install nginx
    mkdir /var/log/multidasher
    chmod -R 777 /var/www
else
	echo -e "Nginx installed -- skipping."
    apt-get -qy install nginx
fi


read -p "Press Enter to continue"
echo -e ""

echo -e ""
echo -e "--------------------------------------------------------------------------------"
echo -e "Installing php && related packages				 "
echo -e "--------------------------------------------------------------------------------"
echo -e ""

if ! grep -q "^deb .*ppa:ondrej/php" /etc/apt/sources.list /etc/apt/sources.list.d/*; then
	add-apt-repository -y ppa:ondrej/php
	apt-get -y update
fi
apt-get -y install curl php-cli php-mbstring git unzip php7.2 php7.2-curl php7.2-gd php7.2-mbstring php7.2-xml php7.2-json php7.2-mysql php7.2-opcache php7.2-fpm


read -p "Press Enter to continue"
echo -e ""

echo -e ""
echo -e "--------------------------------------------------------------------------------"
echo -e "Copying MultiDasher files into /var/www. Your development work should take place"
echo -e "there. After this install you can delete the copy you cloned into your "
echo -e "workspace area."
echo -e "--------------------------------------------------------------------------------"
echo -e ""
cd /var/www
git clone https://github.com/Chainfrog-dev/multidasher.git


read -p "Press Enter to continue"
echo -e ""

echo -e ""
echo -e "--------------------------------------------------------------------------------"
echo -e "Adding site to hosts								 "
echo -e "--------------------------------------------------------------------------------"
echo -e ""

echo -e '127.0.0.1	'$domain >> /etc/hosts


read -p "Press Enter to continue"
echo -e ""

echo -e ""
echo -e "--------------------------------------------------------------------------------"
echo -e "Configure settings php						 "
echo -e "--------------------------------------------------------------------------------"
echo -e ""
if [ ! -f /var/www/multidasher/drupal/web/sites/default/settings.php ]; then
	echo -e '<?php
	$databases = [];
	$config_directories = [];
	$settings["hash_salt"] = "3r0PBfdcAFRH9SsWAAEDWb6ZIscdRx1nmrCMUiwQX3qUtcYjYHDtIS075D1qZIVyF55MQJ9QLQ";
	$settings["update_free_access"] = FALSE;
	$settings["container_yamls"][] = $app_root . "/" . $site_path . "/services.yml";
	$settings["file_scan_ignore_directories"] = [
	  "node_modules",
	  "bower_components",
	];
	$settings["entity_update_batch_size"] = 50;
	$config_directories["sync"] = "../config/sync";
	$databases["default"]["default"] = array (
	  "database" => "multidasher",
	  "username" => "'$uservar'", 
	  "password" => "'$passvar'",
	  "prefix" => "",
	  "host" => "localhost",
	  "port" => "3306",
	  "namespace" => "Drupal\\Core\\Database\\Driver\\mysql",
	  "driver" => "mysql",
	);' >> /var/www/multidasher/drupal/web/sites/default/settings.php
	chmod 644 /var/www/multidasher/drupal/web/sites/default/settings.php
fi


read -p "Press Enter to continue"
echo -e ""

echo -e ""
echo -e "--------------------------------------------------------------------------------"
echo -e "Installing MultiChain                                            "
echo -e "--------------------------------------------------------------------------------"
echo -e ""

# Check whether we need to install MultiChain
if test -x /usr/local/bin/multichaind ; then
	echo -e "MultiChain already installed"
    echo -e "But is there a .multichain in /var/www? I will try to make it."
	cd /var/www
	mkdir .multichain  || echo "It already exists"
	cd ~
else
	cd /tmp
	wget https://www.multichain.com/download/multichain-2.0-alpha-5.tar.gz
	tar -xvzf multichain-2.0-alpha-5.tar.gz
	cd multichain-2.0-alpha-5
	mv multichaind multichain-cli multichain-util /usr/local/bin
	cd /var/www
	mkdir .multichain
	cd ~
fi


read -p "Press Enter to continue"
echo -e ""

echo -e ""
echo -e "--------------------------------------------------------------------------------"
echo -e "Install MySQL database							 "
echo -e "--------------------------------------------------------------------------------"
echo -e ""

if type mysql >/dev/null 2>&1 ; then
	echo -e "MySQL installed"
else 
	sudo apt-get -y install mysql-server
fi

if mysqlshow "multidasher" > /dev/null 2>&1 ; then
	 echo -e "Database exists."
else
	 echo -e "Database doesn't exist. Importing..."
     mysql -e "CREATE DATABASE multidasher /*\!40100 DEFAULT CHARACTER SET utf8 */;"
	 mysql -e "CREATE USER "$uservar"@localhost IDENTIFIED BY '"$passvar"';"
	 mysql -e "GRANT ALL PRIVILEGES ON multidasher.* TO '"$uservar"'@'localhost';"
	 mysql -e "FLUSH PRIVILEGES;"
	 mysql -u $uservar -p${passvar} multidasher < '/var/www/multidasher/example-database/startup-db.sql'
fi


read -p "Press Enter to continue"
echo -e ""

echo -e ""
echo -e "--------------------------------------------------------------------------------"
echo -e "Installing Composer     						 "
echo -e "--------------------------------------------------------------------------------"
echo -e ""

if composer -v > /dev/null 2>&1 ; then
	echo -e 'Composer already installed.'
else 
	cd ~
	curl -sS https://getcomposer.org/installer -o composer-setup.php
	php composer-setup.php --install-dir=/usr/local/bin --filename=composer
fi


read -p "Press Enter to continue"
echo -e ""

echo -e ""
echo -e "--------------------------------------------------------------------------------"
echo -e "Installing Drush     						 "
echo -e "--------------------------------------------------------------------------------"
echo -e ""

cd ~
wget -O drush.phar https://github.com/drush-ops/drush-launcher/releases/download/0.6.0/drush.phar
chmod +x drush.phar
mv drush.phar /usr/local/bin/drush
cd /var/www/multidasher/drupal
composer install
drush upwd admin $drupalpassword
drush cr

cp /var/www/multidasher/nginx/multidasher.cloud.nginx /etc/nginx/sites-enabled/multidasher
sed -i -e 's/CHANGEME/'$domain'/g' /etc/nginx/sites-enabled/multidasher
rm /etc/nginx/sites-enabled/default
chmod -R 777 /var/www/.multichain

service nginx restart


read -p "Press Enter to continue"
echo -e ""

echo -e ""
echo -e "--------------------------------------------------------------------------------"
echo -e "All done!       						             "
echo -e "--------------------------------------------------------------------------------"
echo -e ""
echo -e 'Installation complete. You can now connect to your site on: '$domain
