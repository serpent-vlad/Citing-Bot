#!/usr/bin/sh

read -r -p "Stop the service and pull fresh code? (Y/n)" response
if ! [[ $response =~ ^([nN][oO]|[nN])$ ]]
then
	webservice stop
	echo -e "\nUpdating the code..."
	git reset --hard origin/master
    git pull
    composer update
    php yii cache/flush-all
	echo
	read -r -p "OK to start the service? (Y/n)" response
	if ! [[ $response =~ ^([nN][oO]|[nN])$ ]]
	then
		webservice start
	fi
fi