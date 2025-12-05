<?php
$host = "localhost";
$user = "root"; // XAMPP default - change if needed
$pass = "";
$dbname = "jobs_portal";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>