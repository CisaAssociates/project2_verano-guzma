<?php
// db.php
$host = "localhost";
$user = "u347279731_pj2_vera_guz";
$pass = "Project2_2025";
$db   = "u347279731_pj2_vera_guzdb";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
