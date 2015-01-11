# README #

## Jenkins ##

### Requirements ###

* PHP 5.3
* php5-curl
* sqlite3

### Set up Database and Run ###

sqlite3 database.db < table_jobs.sql
php jenkins/jenkins.php --url=http://domain_of_your_jenkins_server --port=8080 --username=user --token=123usertoken123'