#!/usr/bin/sh

git reset --hard origin/master
git pull
composer update
php yii cache/flush-all