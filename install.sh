#!/bin/bash
# (C) 2018 Chainfrog Oy

if [ -z $BASH_VERSION ] ; then
	echo -e "You must run this script using bash." 1>&2
	exit 1
fi

# Make sure we are running as root
if [[ $EUID -ne 0 ]]; then
	echo -e "This script must be run as root." 1>&2
	exit 1
fi

# Determine the installation type: development or server
INSTALL=""
while true; do
  read -p "Multidasher can be installed for [d]evelopment or as a production [S]erver. Chose [d/S]? " -n 1 -r
  echo    # (optional) move to a new line
  if [[ $REPLY =~ ^[dD]$ ]]
  then
    INSTALL="DEVELOPMENT" && break
    exit 1
  fi
  if [[ $REPLY =~ ^[Ss]$ ]]
  then
    INSTALL="SERVER" && break
  fi
done

echo -e "--------------------------------------------------------------------------------"
echo -e " Installation type: $INSTALL"
echo -e "--------------------------------------------------------------------------------"

echo -e ""
echo -e "--------------------------------------------------------------------------------"
if [ $INSTALL = "SERVER" ] ; then
  echo -e "IMPORTANT: You must make sure that your cloud instance allows incoming HTTP AND "
  echo -e "           HTTPS (80/443) traffic. This is a default in some cloud providers, "
  echo -e "           but not in others (for example AWS)."
  echo -e "IMPORTANT: You must assign a domain name (or subdomain) for the frontend, e.g. "
  echo -e "           YOURSITE.com. or frontend.YOURSITE.com"
  echo -e "           Edit the DNS settings to point to the IP address of your cloud "
  echo -e "           instance."
  echo -e "IMPORTANT: You must assign a domain name (or subdomain), e.g. api.YOURSITE.com "
  echo -e "           for the backend. Edit the DNS settings A record to point to the IP "
  echo -e "           address of your cloud instance."
  echo -e "IMPORTANT: Certbot will prompt you for an email. You must provide one."
  echo -e "IMPORTANT: When Certbot prompts you for DNS settings, choose [1], no redirect."
  echo -e "--------------------------------------------------------------------------------"
  echo -e ""
fi

# Messages for setting up domains for server install
if [ $INSTALL = "SERVER" ] ; then
  read -p $'Enter the domain name redirected to this IP address for the backend \x0a(e.g. api.multidasher.org), \x0aor [enter] to not setup a domain and exit. => ' DOMAIN
  if [ -z $DOMAIN ] ; then
  	echo -e "Non-domain installations not supported. Exiting..."
	exit 1
  fi
  read -p $'Enter the domain name redirected to this IP address for the frontend \x0aor(e.g. frontend.multidasher.org), or [enter] to not setup a domain and exit. \x0a=> ' FRONTDOMAIN
  if [ -z $FRONTDOMAIN ] ; then
	echo -e "Non-domain installations not supported. Exiting..."
	exit 1
  fi
fi

# Messages for setting up domains for development install
if [ $INSTALL = "DEVELOPMENT" ] ; then
  read -p $'Enter a made-up domain name for the install, like md.local.com\x0aMultiDasher installer will write this to your /etc/hosts file. \x0a=> ' DOMAIN
  if [ -z $DOMAIN ] ; then
  	echo -e "Non-domain installations not supported. Exiting..."
	exit 1
  fi
fi

# New MySQL user
read -p $'\x0aSelect a NEW user to be configured in MySQL: ' USERVAR
if [ -z $USERVAR ] ; then
  echo -e "MySQL user is required. Exiting..."
  exit 1
fi

# Passwords for MySQL user and Drupal admin
while true; do
    read -sp 'Select a password to be configured for user in MySQL: ' PASSVAR
    echo
    read -s -p "Password (again): " PASSWORD2
    echo
    [ "$PASSVAR" = "$PASSWORD2" ] && break || echo "Passwords do not match."
done
if [ -z $PASSVAR ] ; then
	echo -e "MySQL password is required. Exiting..."
	exit 1
fi

echo

while true; do
    read -sp $'\x0aSelect a password for user admin in Drupal: ' DPASSVAR
    echo
    read -s -p "Password (again): " PASSWORD2
    echo
    [ "$DPASSVAR" = "$PASSWORD2" ] && break || echo "Passwords do not match."
done
if [ -z $DPASSVAR ] ; then
	echo -e "Drupal password cannot be blank. Exiting..."
	exit 1
fi

# Messages for setting up domains for server install
if [ $INSTALL = "SERVER" ] ; then
  echo -e ""
  echo -e "--------------------------------------------------------------------------------"
  echo -e "Fixing locale (cloud instances often do not have this set)"
  echo -e "--------------------------------------------------------------------------------"
  echo -e ""
  export LC_ALL="en_US.UTF-8"
  export LC_CTYPE="en_US.UTF-8"
  dpkg-reconfigure locales
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

if [ $INSTALL = "DEVELOPMENT" ] ; then
 echo -e ""
 echo -e "--------------------------------------------------------------------------------"
 echo -e "Copying MultiDasher files into /var/www. Your development work should take place"
 echo -e "there. After this install you can delete the copy you cloned into your "
 echo -e "workspace area."
 echo -e "--------------------------------------------------------------------------------"
 echo -e ""
fi

if [ $INSTALL = "SERVER" ] ; then
 echo -e ""
 echo -e "--------------------------------------------------------------------------------"
 echo -e "Copying MultiDasher files into /var/www. After this install you can delete the "
 echo -e "copy you initally cloned into your workspace area."
 echo -e "--------------------------------------------------------------------------------"
 echo -e ""
fi

cd /var/www
git clone https://github.com/Chainfrog-dev/multidasher.git
chmod -R 777 /var/www/multidasher

echo -e ""
echo -e "--------------------------------------------------------------------------------"
echo -e "Adding site to hosts								 "
echo -e "--------------------------------------------------------------------------------"
echo -e ""

echo -e '127.0.0.1	'$DOMAIN >> /etc/hosts
if [ $INSTALL = "SERVER" ] ; then
  echo -e '127.0.0.1	'$FRONTDOMAIN >> /etc/hosts
fi

if [ $INSTALL = "SERVER" ] ; then
  echo -e ""
  echo -e "--------------------------------------------------------------------------------"
  echo -e "Installing certbot 								 "
  echo -e "--------------------------------------------------------------------------------"
  echo -e ""
  ufw limit ssh
  ufw allow in 443/tcp comment "https: for certbot"
  ufw allow 'Nginx HTTP'
  ufw enable
  ufw status

  add-apt-repository -y ppa:certbot/certbot
  apt-get -y update
  apt-get install -qy python-certbot-nginx
  echo -e "REMINDER: Certbot will prompt you for an email. You must provide one."
  echo -e "REMINDER: When Certbot prompts you for DNS settings, choose [1], no redirect."
  sudo certbot --nginx -d $DOMAIN || { echo -e "\nCertbot failed to generate valid certificate."; echo -e "Perhaps your A record for $DOMAIN is not set up correctly."; echo -e "Exiting..." ; exit 1; }
  sudo certbot --nginx -d $FRONTDOMAIN || { echo -e "\nCertbot failed to generate valid certificate."; echo -e "Perhaps your A record for $FRONTDOMAIN is not set up correctly."; echo -e "Exiting..." ; exit 1; }
fi


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
	  "username" => "'$USERVAR'", 
	  "password" => "'$PASSVAR'",
	  "prefix" => "",
	  "host" => "localhost",
	  "port" => "3306",
	  "namespace" => "Drupal\\Core\\Database\\Driver\\mysql",
	  "driver" => "mysql",
	);' >> /var/www/multidasher/drupal/web/sites/default/settings.php
	chmod 644 /var/www/multidasher/drupal/web/sites/default/settings.php
fi

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
	mkdir .multichain  || echo "Multichain folder already exists."
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
	 mysql -e "CREATE USER "$USERVAR"@localhost IDENTIFIED BY '"$PASSVAR"';"
	 mysql -e "GRANT ALL PRIVILEGES ON multidasher.* TO '"$USERVAR"'@'localhost';"
	 mysql -e "FLUSH PRIVILEGES;"
	 mysql -u $USERVAR -p${PASSVAR} multidasher < '/var/www/multidasher/example-database/startup-db.sql'
fi

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

if [ $INSTALL = "SERVER" ] ; then
  
  # correcting ownership of .composer folder
  chown -R $(logname):$(logname) .composer

  echo -e ""
  echo -e "--------------------------------------------------------------------------------"
  echo -e "Installing NGINX redirects     						 "
  echo -e "--------------------------------------------------------------------------------"
  echo -e ""
  chmod -R 777 /var/www/.multichain
  chmod -R 777 /var/www/multidasher
  cp /var/www/multidasher/nginx/multidasher.cloud.nginx /etc/nginx/sites-enabled/multidasher-api
  sed -i -e 's/CHANGEME/'$DOMAIN'/g' /etc/nginx/sites-enabled/multidasher-api
  cp /var/www/multidasher/nginx/multidasher.frontend.nginx /etc/nginx/sites-enabled/multidasher-frontend
  sed -i -e 's/CHANGEME/'$FRONTDOMAIN'/g' /etc/nginx/sites-enabled/multidasher-frontend
  rm /etc/nginx/sites-enabled/default

  echo ""
  echo "-----------------------------------------------------------------"
  echo "Installing node.js and angular-cli                               "
  echo "-----------------------------------------------------------------"
  echo ""
  curl -sL https://deb.nodesource.com/setup_10.x | bash -
  apt-get -qy install nodejs

  if test -x /usr/bin/node ; then
	echo "/usr/bin/node already exists"
  else
	echo "Linking /usr/bin/nodejs to /usr/bin/node"
	ln -s /usr/bin/nodejs /usr/bin/node
  fi
  apt-get -qy install libtool pkg-config build-essential autoconf automake

   npm install -g @angular/cli
   rm /var/www/multidasher/angular/src/environments/environment.prod.ts
   echo -e 'export const environment = {
    production: false,
    host: "'$DOMAIN'"
  };
  ' >> /var/www/multidasher/angular/src/environments/environment.prod.ts
  chmod 644 /var/www/multidasher/angular/src/environments/environment.prod.ts

  echo ""
  echo "-----------------------------------------------------------------"
  echo "Restarting NGINX after Angular install"
  echo "-----------------------------------------------------------------"
  echo ""
  service nginx restart

  # Run all remaining commands as logged-in user, not root.

  echo -e ""
  echo -e "--------------------------------------------------------------------------------"
  echo -e "Installing Composer and cache resetting Drush				     						 "
  echo -e "--------------------------------------------------------------------------------"
  echo -e ""

  su $(logname) -c 'cd /var/www/multidasher/drupal; composer install; ./vendor/drush/drush/drush upwd admin $1; ./vendor/drush/drush/drush cr' -- myshell $DPASSVAR

  echo -e ""
  echo -e "--------------------------------------------------------------------------------"
  echo -e "Building website. If this fails, your site server is probably underpowered. "
  echo -e "--------------------------------------------------------------------------------"
  echo -e ""

  su $(logname) -c 'cd /var/www/multidasher/angular; npm install; ng build --prod'
  echo -e "Installation complete. You can now see your site at $FRONTDOMAIN."

fi

# Development node install script
if [ $INSTALL = "DEVELOPMENT" ] ; then

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
  drush upwd admin $DPASSVAR
  drush cr

  echo -e ""
  echo -e "--------------------------------------------------------------------------------"
  echo -e "Configuring NGINX     						 "
  echo -e "--------------------------------------------------------------------------------"
  echo -e ""
  cp /var/www/multidasher/nginx/multidasher.local.nginx /etc/nginx/sites-available/multidasher
  sed -i -e 's/CHANGEME/'$domain'/g' /etc/nginx/sites-available/multidasher
  ln -s /etc/nginx/sites-available/multidasher /etc/nginx/sites-enabled/multidasher
r  m /etc/nginx/sites-enabled/default
  chmod -R 777 /var/www/.multichain

  echo ""
  echo "-----------------------------------------------------------------"
  echo "Installing node.js and angular-cli...                                      "
  echo "-----------------------------------------------------------------"
  echo ""
  curl -sL https://deb.nodesource.com/setup_10.x | bash -
  apt-get -qy install nodejs

  if test -x /usr/bin/node ; then
  	echo "/usr/bin/node already exists"
  else
	echo "Linking /usr/bin/nodejs to /usr/bin/node"
	ln -s /usr/bin/nodejs /usr/bin/node
  fi

  apt-get -qy install libtool pkg-config build-essential autoconf automake
  cd /var/www/multidasher/angular
  npm install -g @angular/cli
  npm install


  service nginx restart

  echo -e ""
  echo -e "--------------------------------------------------------------------------------"
  echo -e "All done!       						             "
  echo -e "--------------------------------------------------------------------------------"
  echo -e ""
  echo -e 'Installation complete. Go to /var/www/multidasher/angular and run "ng serve" to'
  echo -e 'start the development server. Then visit $DOMAIN:4200 to see the site.'
fi
