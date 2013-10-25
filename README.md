dnsmasq-rest-api
================

[![Build Status](https://travis-ci.org/bpaquet/dnsmasq-rest-api.png)](https://travis-ci.org/bpaquet/dnsmasq-rest-api)

Dead simple REST Api for controlling [dnsmasq](http://www.thekelleys.org.uk/dnsmasq/doc.html) server.

Why in PHP ? Because it's easy to deploy : no rbenv, ruby, java or pyhton lib to install !

Installation
---

This procedure has been tested under Ubuntu 12.04.

Requirements :
* Install dnsmasq
* Install and configure your PHP Server
* Ensure you have, git, curl, nslookup and killall installed

Automated install :

```
curl http://rawgithub.com/bpaquet/dnsmasq-rest-api/master/install.sh | sudo bash
```

Manual install :

Please read the [install script](https://github.com/bpaquet/dnsmasq-rest-api/blob/master/install.sh).

Example of config is [here](https://github.com/bpaquet/dnsmasq-rest-api/blob/master/www/config.example.php).

### Security token

You can set a security token is config file.

In this case, you have to set the ``X-Auth-Token`` header in all your HTTP request.

Example :
```
$ curl -H 'X-Auth-Token: mySecurityToken' http://localhost/dnsmasq-rest-api/zones
["myZone"]
```

API
---

### Manipulating zone

For each zone, dnsmasq-rest-api will write a file (named by the zone name) in the hosts dnsmasq directory.

Each zone can contains multiples lines, like a standard hosts file.

* List zones

```
$ curl http://localhost/dnsmasq-rest-api/zones
["myZone"]
```

* Delete zone

```
$ curl -X DELETE http://localhost/dnsmasq-rest-api/zones/myZone
OK Zone deleted
```

* There is no create zone command. Just add a record into a new zone

```
$ curl -X POST http://localhost/dnsmasq-rest-api/zones/myZone/127.0.0.1/localhost
OK Record added
```

Another syntax :

```
$ curl -X POST -d '{"127.0.0.1":["localhost"]}' http://localhost/dnsmasq-rest-api/zones/myZone
OK Record added
```

You can set multiple records with one call.

* List zone records

```
$ curl http://localhost/dnsmasq-rest-api/zones/myZone
{"127.0.0.1":["localhost"]}
```

* Multiple hosts for same IP are supported

```
$ curl -X POST http://localhost/dnsmasq-rest-api/zones/myZone/127.0.0.1/localhost2
OK Record added
$ curl http://localhost/dnsmasq-rest-api/zones/myZone
{"127.0.0.1":["localhost","localhost2"]}
```

* Reload dnsmasq config : MUST be done after a change or a batch of changes,to force dnsmasq to re read config files

```
$ curl -X POST http://localhost/dnsmasq-rest-api/reload
OK Dnmasq config reloaded
```

### Backup / restore

* Backup all zones

```
$ curl -f -s -o backup http://localhost/dnsmasq-rest-api/backup
$ cat backup
{"myZone":{"127.0.0.1":["localhost2"]}}
```

* Restore all zones from a backup file

```
$ curl -f -s -d @backup -X POST http://localhost/dnsmasq-rest-api/restore
OK All zones restored : myZone
```

### Leases

* To get all leases from dnsmasq

```
$ http://localhost/dnsmasq-rest-api/leases
[{"timestamp":"1384349107","mac":"52:54:00:68:4d:74","ip":"10.1.126.4","hostname":"toto","client_id":"01:52:54:00:68:4d:74"},{"timestamp":"1384349327","mac":"52:54:00:2a:36:c2","ip":"10.1.118.125","hostname":"*","client_id":"*"}]
```

* To filter results

```
$ http://localhost/dnsmasq-rest-api/leases?ip=126
[{"timestamp":"1384349107","mac":"52:54:00:68:4d:74","ip":"10.1.126.4","hostname":"toto","client_id":"01:52:54:00:68:4d:74"}]
```

Available filters : ``mac``, ``ip``, ``timestamp``, ``hostname``, ``client_id``. Filter values are regex.
