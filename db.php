<?php
$servername = "localhost";
$username1 = "root"; 
$password = "";
$database = "mindspeak1";
$host="localhost";
$dbname="mindspeak1";
$conn = mysqli_connect($servername, $username1, $password, $database);
$db = new mysqli($host, $username1, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username1, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>
