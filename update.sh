#!/usr/bin/sh

read -r -p "Stop the service and pull fresh code? (Y/n)" response
if ! [[ $response =~ ^([nN][oO]|[nN])$ ]]
then
	webservice stop
	echo -e "\nUpdating the code..."
	git reset --hard origin/release
    git pull origin release
    composer install
    php yii cache/flush-all
	echo
	read -r -p "OK to start the service? (Y/n)" response
	if ! [[ $response =~ ^([nN][oO]|[nN])$ ]]
	then
		webservice --backend=kubernetes php7.2 start
	fi
fi