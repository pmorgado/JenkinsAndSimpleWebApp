# Requirements #

* PHP 5.3
* php5-curl
* sqlite3
* Apache 2.x + rewrite_module + php5_module

### Jenkins ###

```
#!bash

sqlite3 jenkins/database.db < jenkins/table_jobs.sql
php jenkins/jenkins.php --url=http://domain_of_your_jenkins_server --port=8080 --username=user --token=123usertoken123

```

### APP ###

When you are configuring the apache virtual hosts, you must:

* DocumentRoot "/var/www/jenkinsandsimplewebapp/app/public"
* DirectoryIndex index.php