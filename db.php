<?php
//conexão com o banco

session_start();

$db_name = "moviestar";
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
try {
  $conn = new PDO("mysql:dbname=" . $db_name . ";host=" . $db_host, $db_user, $db_pass);

  // habilitar erros
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);



} catch (PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}