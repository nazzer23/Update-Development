<?php
require "../../global.inc.php";
$global = new GlobalHandler(false);

$template = $global->template;
$database = $global->db;
$functions = $global->functions;
if(isset($_POST['bioSubmit'])) {
$isUserValid = $database->getNumberOfRows("SELECT * FROM users WHERE UserID='{$_SESSION['userID']}'");
    if($isUserValid <= 0) {
        header('Location: '.$_SERVER['HTTP_REFERER']);
    } else {
        $database->executeQuery("UPDATE users SET Bio='{$_POST['bioSubmit']}' WHERE UserID='{$_SESSION['userID']}'");
        header('Location: '.$_SERVER['HTTP_REFERER']);
    }
}
?>