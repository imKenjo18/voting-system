<?php
session_start();

if (!isset($_SESSION["isLoggedIn"]) || $_SESSION["isLoggedIn"] !== true) {
  header("location: ./");
  exit;
}

if ($_SESSION["privilege"] == "voter") {
  header("location: home.php");
  exit;
}

require_once "assets/dbhandler.php";
require_once "assets/functions.php";

$row_id = $_GET["id"];
$table_name = $_GET["of"];

quick_query("DELETE FROM $table_name WHERE id = '$row_id'");

header("location: admin.php");
exit;
?>