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
        echo "There are not jobs configured. No data inserted/updated.";
        exit(0);
    }
}

/**
 * @param array $params
 * @return array
 */
function getAllJobsFromJenkins(array $params)
{
    $result = parse_url($params['url']);
    $url = $result['scheme'] . '://' . $params['username'] . ':' . $params['password'] . '@' . $result['host'];
    $url .= isset($result['port']) ? ':' . $result['port'] : '';
    $url .= '/api/json';

    /**
     * This should authenticate via token generated on jenkins to avoid CSRF attacks on jenkins
     * I didn't have time to explore it.
     */
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    $out = curl_exec($ch);
    curl_close($ch);

    if (!$out) {
        echo "Invalid response from the jenkins server. Please, check if the server is well configured.";
        exit(1);
    }
    $out = json_decode($out, true);
    return $out['jobs'];
}

/**
 * @param array $argv
 * @return array
 */
function getArgs(array $argv)
{
    $errorMessage = 'Usage:: php jenkins.php --url=http://localhost:8080 --username=user --password=123password123';
    $params = array('url' => '', 'username' => '', 'password' => '');

    if(count($argv) != 4) {
        echo $errorMessage;
        exit(1);
    }

    unset($argv[0]);
    foreach ($argv as $arg) {
        $res = explode('=',$arg);
        $param = substr($res[0], 2);
        if(isset($params[$param])) {
            $params[$param] = $res[1];
        } else {
            echo $errorMessage;
            exit(1);
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
    /**
     * CREATE TABLE jobs (
     * id integer primary key AUTOINCREMENT,
     * name varchar(100),
     * status varchar(40),
     * updated_at datetime
     * );
     */
    //TODO: check the exception
    $dir = 'sqlite:'. $path .'/database1.db';

    try {
        $conn = new PDO($dir);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
        exit(1);
    }
}