#!/bin/bash -e

echo "Installing dnsmasq-rest-api"

ln -sf /opt/dnsmasq-rest-api/config/dnsmasq/dnsmasq-rest-api.conf /etc/dnsmasq.d/dnsmasq-rest-api.conf
/etc/init.d/dnsmasq restart

ln -sf /opt/dnsmasq-rest-api/config/apache2/dnsmasq-rest-api.conf /etc/apache2/conf.d/dnsmasq-rest-api.conf
/etc/init.d/apache2 restart
chown -R www-data /opt/dnsmasq-rest-api/zones
cp /opt/dnsmasq-rest-api/www/config.example.php /opt/dnsmasq-rest-api/www/config.php

cp /opt/dnsmasq-rest-api/config/sudo/dnsmasq /etc/sudoers.d/dnsmasq
chmod 0440 /etc/sudoers.d/dnsmasq

echo "Dnsmasq-rest-api installed."

echo "Running tests"

curl -f -s http://localhost/dnsmasq-rest-api/zones
curl -f -s http://localhost/dnsmasq-rest-api/zones/myTest/127.0.0.1/localhost.test
curl -f -s http://localhost/dnsmasq-rest-api/reload
curl -f -s host localhost.test 127.0.0.1
curl -f -s -X DELETE http://localhost/dnsmasq-rest-api/zones/myTest

echo "Tests ok."