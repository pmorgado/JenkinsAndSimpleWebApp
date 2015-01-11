# Requirements #

* PHP 5.3
* php5-curl
* sqlite3

### Jenkins ###

```
#!bash

sqlite3 jenkins/database.db < jenkins/table_jobs.sql
php jenkins/jenkins.php --url=http://domain_of_your_jenkins_server --port=8080 --username=user --token=123usertoken123

```