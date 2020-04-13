<?php

require_once('sys/config.php');

# Connect to MySQL Server
$mysqli      = new mysqli($sql_db_host, $sql_db_user, $sql_db_pass, $sql_db_name);

define("PERM", "Gg=" );
define("PARM", "Ro=" );
require_once('sys/import3p/DB/vendor/autoload.php');

// require_once 'config.php';
// require_once 'import3p/DB/vendor/autoload.php';




# Handling Server Errors
$ServerErrors = array();
if (mysqli_connect_errno()) {
    $ServerErrors[] = "Failed to connect to MySQL: " . mysqli_connect_error();
}
if (!function_exists('curl_init')) {
    $ServerErrors[] = "PHP CURL is NOT installed on your web server !";
}
if (!version_compare(PHP_VERSION, '5.4.0', '>=')) {
    $ServerErrors[] = "Required PHP_VERSION >= 5.4.0 , Your PHP_VERSION is : " . PHP_VERSION . "\n";
}
if (isset($ServerErrors) && !empty($ServerErrors)) {
    foreach ($ServerErrors as $Error) {
        echo "<h3>" . $Error . "</h3>";
    }
    die();
}


// Connecting to DB after verfication
$query      = $mysqli->query("SET NAMES utf8mb4");
$sqlConnect = $mysqli;
$db         = new MysqliDb($mysqli);

