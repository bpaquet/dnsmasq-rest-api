#!/bin/bash

set -e

target=/opt/dnsmasq-rest-api/

echo "Installing dnsmasq-rest-api to $target."

[ -d $target ] || git clone git://github.com/bpaquet/dnsmasq-rest-api.git $TARGET

echo "Configuring dnsmasq."

ln -sf $target/config/dnsmasq/dnsmasq-rest-api.conf /etc/dnsmasq.d/dnsmasq-rest-api.conf
/etc/init.d/dnsmasq restart

echo "Allow dnsmasq-rest-api to send signal to dnsmasq"

cp $target/config/sudo/dnsmasq /etc/sudoers.d/dnsmasq
chmod 0440 /etc/sudoers.d/dnsmasq

echo "Configuring apache2"

ln -sf $target/config/apache2/dnsmasq-rest-api.conf /etc/apache2/conf.d/dnsmasq-rest-api.conf
/etc/init.d/apache2 restart
chown -R www-data $targetzones
cp $target/www/config.example.php $target/www/config.php

echo "Dnsmasq-rest-api installed."

echo "Running tests"

curl -f -s http://localhost/dnsmasq-rest-api/zones
curl -f -s http://localhost/dnsmasq-rest-api/zones/myTest/127.0.0.2/localhost.test
curl -f -s http://localhost/dnsmasq-rest-api/reload
host localhost.test 127.0.0.1 | grep 127.0.0.2
curl -f -s -X DELETE http://localhost/dnsmasq-rest-api/zones/myTest

echo "Tests ok."