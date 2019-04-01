<?php
require "../../global.inc.php";
$global = new GlobalHandler(false);

$template = $global->template;
$database = $global->db;
$functions = $global->functions;

if(isset($_GET['id'])) {
    if($_GET['id'] == "") {
        header('HTTP/1.0 403 Forbidden');
    } else if ($_GET['id'] == $_SESSION['userID']) {
        header('HTTP/1.0 403 Forbidden');
    } else {
        $isUserValid = $database->getNumberOfRows("SELECT * FROM users WHERE UserID='{$_GET['id']}'");
        if($isUserValid <= 0) {
            header('HTTP/1.0 403 Forbidden');
        } else {
            $requestInvalid = $database->getNumberOfRows("SELECT * FROM users_friends WHERE FriendID='{$_GET['id']}' AND UserID='{$_SESSION['userID']}';");
            if($requestInvalid <= 0) {
                header('Location: '.$_SERVER['HTTP_REFERER']);
            } else {
                $database->executeQuery("DELETE FROM users_friends WHERE FriendID='{$_GET['id']}' AND UserID='{$_SESSION['userID']}'");
                $database->executeQuery("DELETE FROM users_friends WHERE UserID='{$_GET['id']}' AND FriendID='{$_SESSION['userID']}'");
                header('Location: '.$_SERVER['HTTP_REFERER']);
            }
        }
    }
} else {
    header('HTTP/1.0 403 Forbidden');
}
?>