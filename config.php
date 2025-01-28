<?php

// website name/title
$title = "Demo App Guestbook by JB";

// messages to display per page
$per_page = 10;

// Timezone to use (default to UTC)
// For example UTC, Asia/Kathmandu, America/New_York
$timezone = "UTC";
date_default_timezone_set($timezone);

$dbname = 'database01';
$tablename = 'guestbook';

# [START cloud_sql_mysql_pdo_connect_unix]


try {
    // Note: Saving credentials in environment variables is convenient, but not
    // secure - consider a more secure solution such as
    // Cloud Secret Manager (https://cloud.google.com/secret-manager) to help
    // keep secrets safe.
    $username = getenv('DB_USER'); // e.g. 'your_db_user'
    $password = getenv('DB_PASS'); // e.g. 'your_db_password'
    $dbName = getenv('DB_NAME'); // e.g. 'your_db_name'
    $instanceUnixSocket = getenv('INSTANCE_UNIX_SOCKET'); // e.g. '/cloudsql/project:region:instance'
    $dbtech = getenv('DB_TECH');
    $dsndb = getenv('DSNDB');
   
    if ($dbtech == 'Cloud SQL for PostgreSQL w/ Redis'){
        // Connect to Memorystore from App Engine.
        if (!$host = getenv('REDIS_HOST')) {
            throw new Exception('To use REDIS you need to set the REDIS_HOST env var');
        }

        # Memorystore Redis port defaults to 6379
        $port = getenv('REDIS_PORT') ?: '6379';

        try {
            $redis = new Redis();
            $redis->connect($host, $port);
        } catch (Exception $e) {
            throw new Exception('Error: ' . $e->getMessage());
        }
    }    
    
    if ($dsndb == "pgsql"){
        $schema='database01';
        $conntype='host';
    }else if ($dsndb == 'mysql'){
        $schema=$dbname;
        $conntype='unix_socket';
    }
   
    // Build the DSN depending on each database requirements
    $dsn = sprintf(
        $dsndb.':dbname=%s;'.$conntype.'=%s',
        $dbName,
        $instanceUnixSocket
    );

    // Connect to the database.
    $conn = new PDO(
        $dsn,
        $username,
        $password,
        # [START_EXCLUDE]
        // Here we set the connection timeout to five seconds and ask PDO to
        // throw an exception if any errors occur.
        [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
        # [END_EXCLUDE]
    );
} catch (TypeError $e) {
    throw new RuntimeException(
        sprintf(
            'Invalid or missing configuration! Make sure you have set ' .
                '$username, $password, $dbName, ' .
                'and $instanceUnixSocket (for UNIX socket mode). ' .
                'The PHP error was %s',
            $e->getMessage()
        ),
        (int) $e->getCode(),
        $e
    );
} catch (PDOException $e) {
    throw new RuntimeException(
        sprintf(
            'Could not connect to the Cloud SQL Database. Check that ' .
                'your username and password are correct, that the Cloud SQL ' .
                'proxy is running, and that the database exists and is ready ' .
                'for use. For more assistance, refer to %s. The PDO error was %s',
            'https://cloud.google.com/sql/docs/mysql/connect-external-app',
            $e->getMessage()
        ),
        (int) $e->getCode(),
        $e
    );
}
# CREATE TABLE guestbook (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(60) NOT NULL, message TEXT(600) NOT NULL, date DATETIME DEFAULT NOW() NOT NULL) AUTO_INCREMENT=1;
        return $conn;
# [END cloud_sql_mysql_pdo_connect_unix]