<?php
require "core/global.inc.php";
$global = new GlobalHandler(false);
$database = $global->db;
$database->executeQuery("UPDATE users_sessions SET Valid=0 WHERE UserID='{$_SESSION['userID']}' AND SessionString='{$_SESSION['sessionString']}'");
echo '<script>localStorage.clear();</script>';

session_destroy();
header("Location: /");
?>