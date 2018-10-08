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

echo -e "IMPORTANT: You must make sure that your cloud instance allows incoming HTTP AND \nHTTPS (80/443) traffic. This is a default in some cloud providers, but not in \nothers (for example AWS)."
echo -e "IMPORTANT: We highly recommend you assign a domain name, e.g. YOURSITE.com. You \nmust edit the DNS settings (A record) to point to the IP address of your cloud \ninstance."
echo -e "IMPORTANT: Certbot will prompt you for an email. You must provide one."
echo -e "IMPORTANT: When Certbot prompts you for DNS settings, choose [1], no redirect."
echo -e "\n"

read -pe 'If you have setup a domain redirected to this IP address, enter it here \n(e.g. panel.multidasher.org), or [enter] to not setup a domain and exit \n=> ' domain
if [ -z $domain ] ; then
	echo -e "Non-domain installations not supported. Exiting..."
	exit 1
fi
read -pe '\nSelect a NEW user to be configured in MySQL: ' uservar
if [ -z $uservar ] ; then
	echo -e "MySQL user is required. Exiting..."
	exit 1
fi
read -spe 'Select a password to be configured for user in MySQL: ' passvar
if [ -z $passvar ] ; then
	echo -e "MySQL password is required. Exiting..."
	exit 1
fi
read -spe '\nSelect a password for user admin in Drupal: ' drupalpassword
if [ -z $drupalpassword ] ; then
	echo -e "Drupal password is required. Exiting..."
	exit 1
fi

echo -e ""
echo -e "------------------------------------------------"
echo -e "Fixed locale                                    "
echo -e "------------------------------------------------"
echo -e ""
export LC_ALL="en_US.UTF-8"
export LC_CTYPE="en_US.UTF-8"
dpkg-reconfigure locales

echo -e ""
echo -e "------------------------------------------------"
echo -e "Update server                                   "
echo -e "------------------------------------------------"
echo -e ""
apt-get -y update
apt-get -y upgrade

echo -e ""
echo -e "-----------------------------------------------"
echo -e "Install NGINX 								 "
echo -e "-----------------------------------------------"
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

echo -e ""
echo -e "-----------------------------------------------"
echo -e "Install php && related packages				 "
echo -e "-----------------------------------------------"
echo -e ""

if ! grep -q "^deb .*ppa:ondrej/php" /etc/apt/sources.list /etc/apt/sources.list.d/*; then
	add-apt-repository -y ppa:ondrej/php
	apt-get -y update
fi
apt-get -y install curl php-cli php-mbstring git unzip php7.2 php7.2-curl php7.2-gd php7.2-mbstring php7.2-xml php7.2-json php7.2-mysql php7.2-opcache php7.2-fpm
cd /var/www
git clone https://github.com/Chainfrog-dev/multidasher.git


echo -e ""
echo -e "-----------------------------------------------"
echo -e "Add site to hosts								 "
echo -e "-----------------------------------------------"
echo -e ""

echo -e '127.0.0.1	'$domain >> /etc/hosts
echo -e ""
echo -e "-----------------------------------------------"
echo -e "Install certbot 								 "
echo -e "-----------------------------------------------"
echo -e ""
ufw allow OpenSSH
ufw allow in 443/tcp comment "https: for certbot"
ufw allow 'Nginx HTTP'
ufw enable
ufw status

add-apt-repository -y ppa:certbot/certbot
apt-get -y update
apt-get install -qy python-certbot-nginx
sudo certbot --nginx -d $domain

echo -e ""
echo -e "-----------------------------------------------"
echo -e "Configure settings php						 "
echo -e "-----------------------------------------------"
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

echo -e ""
echo -e "-----------------------------------------------------------------"
echo -e "Installing MultiChain                                            "
echo -e "-----------------------------------------------------------------"
echo -e ""

# Check whether we need to install MultiChain
if test -x /usr/local/bin/multichaind ; then
	echo -e "MultiChain already installed"
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

echo -e ""
echo -e "-----------------------------------------------"
echo -e "Install MySQL database							 "
echo -e "-----------------------------------------------"
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

echo -e ""
echo -e "-----------------------------------------------"
echo -e "Install Composer     						 "
echo -e "-----------------------------------------------"
echo -e ""

if composer -v > /dev/null 2>&1 ; then
	'composer already installed'
else 
	cd ~
	curl -sS https://getcomposer.org/installer -o composer-setup.php
	php composer-setup.php --install-dir=/usr/local/bin --filename=composer
fi

echo -e ""
echo -e "-----------------------------------------------"
echo -e "Install Drush     						 "
echo -e "-----------------------------------------------"
echo -e ""

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

echo -e ""
echo -e "-----------------------------------------------"
echo -e "Done       						             "
echo -e "-----------------------------------------------"
echo -e ""
echo -e 'Installation complete. You can now connect to your site on: '$domain
