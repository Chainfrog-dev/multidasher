if [ -z $BASH_VERSION ] ; then
	echo -e "You must run this script using bash." 1>&2
	exit 1
fi

# Make sure we are running as root
if [[ $EUID -ne 0 ]]; then
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
	echo -e "Installing composer and node modules				     						 "
	echo -e "--------------------------------------------------------------------------------"
	echo -e ""

	cd /var/www/multidasher/drupal
	composer install
	./vendor/drush/drush/drush upwd admin $drupalpassword
	./vendor/drush/drush/drush cr

	cd /var/www/multidasher/angular
	npm install
	ng build --aot --prod

	else
	echo -e "This script can't be run as root." 1>&2
	exit 1
fi

