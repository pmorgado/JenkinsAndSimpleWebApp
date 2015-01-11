<?php

$params = getArgs($argv);
$jobs = getAllJobsFromJenkins($params);
insertUpdateIntoDB($jobs);
exit(0);

/**
 * Insert jobs data into DB
 * @param array $jobs
 */
function insertUpdateIntoDB(array $jobs)
{
    if(!empty($jobs)) {
        $dbh = getDbConn();
        foreach ($jobs as $job) {
            $stmt = $dbh->prepare("select id from jobs where name=:name");
            $stmt->execute(array(':name' => $job['name']));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if(!empty($result)) {
                $dbh->prepare("UPDATE jobs SET status=:status, updated_at=datetime('now') where id=:id;")
                    ->execute(array(
                        ':status' => $job['color'],
                        ':id' => $result['id']
                    ));
            } else {
                $dbh->prepare("INSERT INTO jobs(name, status ,updated_at) VALUES (:name, :status, datetime('now'));")
                    ->execute(array(
                        ':name' => $job['name'],
                        ':status' => $job['color']));
            }
        }
    } else {
        exit("There are not jobs configured. No data inserted/updated." . PHP_EOL);
    }
}

/**
 * @param array $params
 * @return array
 */
function getAllJobsFromJenkins(array $params)
{
    $error = false;
    $result = parse_url($params['url']);
    $url = $result['scheme'] . '://' . $params['username'] . ':' . $params['token'] . '@' . $result['host'];
    $url .= !empty($params['port']) ? ':' . $params['port'] : '';
    $url .= '/api/json';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $out = curl_exec($ch);

    //save the error
    if(curl_errno($ch)) {
        $error = curl_error($ch);
    } else if (strpos($out,'<html>') !== false) {
        $error = $out;
    }
    curl_close($ch);

    if(!$error) {
        $out = json_decode($out, true);
        return $out['jobs'];
    } else {
        exit($error . PHP_EOL);
    }

}

/**
 * @param array $argv
 * @return array
 */
function getArgs(array $argv)
{
    unset($argv[0]);
    $errorMessage = '::::Usage::::
    php jenkins/jenkins.php
    --url=http://domain_of_your_jenkins_server
    --port=8080 (optional)
    --username=user
    --token=123usertoken123' . PHP_EOL;

    $params = array(
        'url' => '',
        'username' => '',
        'token' => '',
        'port' => ''
    );

    if(count($argv) != 3 && count($argv) != 4) {
        exit($errorMessage);
    }

    foreach ($argv as $arg) {
        $res = explode('=',$arg);
        $param = substr($res[0], 2);
        if(isset($params[$param])) {
            $params[$param] = $res[1];
        } else {
            exit($errorMessage);
        }
    }
    return $params;
}

/**
 * @return PDO
 */
function getDbConn()
{
    $path = dirname(__DIR__) . '/jenkins';
    $dir = 'sqlite:'. $path .'/database.db';

    try {
        $conn = new PDO($dir);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        exit("Connection failed: " . $e->getMessage() . PHP_EOL);
    }
}