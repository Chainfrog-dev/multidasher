#!/bin/bash
#git clone https://github.com/Chainfrog-dev/multidasher.git

## Commands
# AWS: scp -i /home/USER/.ssh/KEY.pem /var/www/multidasher/multidasher_server.sh ubuntu@YOURIP:/home/ubuntu
# DO: scp /var/www/multidasher/multidasher_server.sh root@YOURIP:/home/root
# AWS ssh -i /home/USER/.ssh/KEY.pem ubuntu@YOURIP
# DO: ssh root@YOURIP

echo "IMPORTANT: You must make sure that your cloud instance allows incoming HTTP AND HTTPS (80 / 443) traffic. This is a default in some cloud providers, but not in others (for example AWS)."
echo "IMPORTANT: We highly recommend you assign a domain name, e.g. YOURSITE.com. You must edit the DNS settings (A record) to point to the IP address of your cloud instance."
echo "IMPORTANT: Certbot will prompt you for an email. You must provide one."
echo "IMPORTANT: When Certbot prompts you for DNS settings, choose [1], no redirect."

if [ -z $BASH_VERSION ] ; then
	echo "You must run this script using bash." 1>&2
	exit 1
fi

# Make sure we are running as root
if [[ $EUID -ne 0 ]]; then
	echo "This script must be run as root." 1>&2
	exit 1
fi

read -p 'If you have setup a domain redirected to this IP address, enter it here (e.g. panel.multidasher.org), or [enter] to not setup a domain and exit => ' domain
if [ -z $domain ] ; then
	echo "Non-domain installations not supported. Exiting..."
	exit 1
fi
read -p 'Select a NEW user to be configured in MySQL: ' uservar
if [ -z $uservar ] ; then
	echo "MySQL user is required. Exiting..."
	exit 1
fi
read -sp 'Select a password to be configured for user in MySQL: ' passvar
if [ -z $passvar ] ; then
	echo "MySQL password is required. Exiting..."
	exit 1
fi
read -sp 'Select a password for user admin in Drupal: ' drupalpassword
if [ -z $drupalpassword ] ; then
	echo "Drupal password is required. Exiting..."
	exit 1
fi

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
apt-get -y update
apt-get -y upgrade

echo ""
echo "-----------------------------------------------"
echo "Install NGINX 								 "
echo "-----------------------------------------------"
echo ""

if ! which nginx > /dev/null 2>&1; then
    echo "Nginx not installed -- installing."
    apt-get -qy install nginx
    mkdir /var/log/multidasher
    chmod -R 777 /var/www
else
	echo "Nginx installed -- skipping."
    apt-get -qy install nginx
fi

echo ""
echo "-----------------------------------------------"
echo "Install php && related packages				 "
echo "-----------------------------------------------"
echo ""

if ! grep -q "^deb .*ppa:ondrej/php" /etc/apt/sources.list /etc/apt/sources.list.d/*; then
	add-apt-repository -y ppa:ondrej/php
	apt-get -y update
fi
apt-get -y install curl php-cli php-mbstring git unzip php7.2 php7.2-curl php7.2-gd php7.2-mbstring php7.2-xml php7.2-json php7.2-mysql php7.2-opcache php7.2-fpm
cd /var/www
git clone https://github.com/Chainfrog-dev/multidasher.git


echo ""
echo "-----------------------------------------------"
echo "Add site to hosts								 "
echo "-----------------------------------------------"
echo ""

echo '127.0.0.1	'$domain >> /etc/hosts
echo ""
echo "-----------------------------------------------"
echo "Install certbot 								 "
echo "-----------------------------------------------"
echo ""
ufw allow OpenSSH
ufw allow in 443/tcp comment "https: for certbot"
ufw allow 'Nginx HTTP'
ufw enable
ufw status

add-apt-repository -y ppa:certbot/certbot
apt-get -y update
apt-get install -qy python-certbot-nginx
sudo certbot --nginx -d $domain

echo ""
echo "-----------------------------------------------"
echo "Configure settings php						 "
echo "-----------------------------------------------"
echo ""
if [ ! -f /var/www/multidasher/drupal/web/sites/default/settings.php ]; then
	echo '<?php
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
	wget https://www.multichain.com/download/multichain-2.0-alpha-5.tar.gz
	tar -xvzf multichain-2.0-alpha-5.tar.gz
	cd multichain-2.0-alpha-5
	mv multichaind multichain-cli multichain-util /usr/local/bin
	cd /var/www
	mkdir .multichain
	chmod -R 777 .multichain
	cd ~
fi

echo ""
echo "-----------------------------------------------"
echo "Install MySQL database							 "
echo "-----------------------------------------------"
echo ""

if type mysql >/dev/null 2>&1 ; then
	echo "MySQL installed"
else 
	sudo apt-get -y install mysql-server
fi

if mysqlshow "multidasher" > /dev/null 2>&1 ; then
	 echo "Database exists."
else
	 echo "Database doesn't exist. Importing..."
     mysql -e "CREATE DATABASE multidasher /*\!40100 DEFAULT CHARACTER SET utf8 */;"
	 mysql -e "CREATE USER "$uservar"@localhost IDENTIFIED BY '"$passvar"';"
	 mysql -e "GRANT ALL PRIVILEGES ON multidasher.* TO '"$uservar"'@'localhost';"
	 mysql -e "FLUSH PRIVILEGES;"
	 mysql -u $uservar -p${passvar} multidasher < '/var/www/multidasher/example-database/startup-db.sql'
fi

echo ""
echo "-----------------------------------------------"
echo "Install Composer     						 "
echo "-----------------------------------------------"
echo ""

if composer -v > /dev/null 2>&1 ; then
	'composer already installed'
else 
	cd ~
	curl -sS https://getcomposer.org/installer -o composer-setup.php
	php composer-setup.php --install-dir=/usr/local/bin --filename=composer
fi

echo ""
echo "-----------------------------------------------"
echo "Install Drush     						 "
echo "-----------------------------------------------"
echo ""

cd ~
wget -O drush.phar https://github.com/drush-ops/drush-launcher/releases/download/0.6.0/drush.phar
chmod +x drush.phar
mv drush.phar /usr/local/bin/drush
cd /var/www/multidasher/drupal
drush upwd admin $drupalpassword
drush cr
composer install

cp /var/www/multidasher/nginx/multidasher.cloud.nginx /etc/nginx/sites-enabled/multidasher
sed -i -e 's/CHANGEME/'$domain'/g' /etc/nginx/sites-enabled/multidasher
rm /etc/nginx/sites-enabled/default

service nginx restart

echo ""
echo "-----------------------------------------------"
echo "Done       						             "
echo "-----------------------------------------------"
echo ""
echo 'Installation complete. You can now connect to your site on: '$domain
