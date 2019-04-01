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
            $requestInvalid = $database->getNumberOfRows("SELECT * FROM users_friends_requests WHERE SenderID='{$_GET['id']}' AND UserID='{$_SESSION['userID']}';");
            if($requestInvalid <= 0) {
                header('Location: '.$_SERVER['HTTP_REFERER']);
            } else {
                $database->executeQuery("INSERT INTO users_friends (UserID, FriendID) VALUES ('{$_GET['id']}', '{$_SESSION['userID']}');");
                $database->executeQuery("INSERT INTO users_friends (FriendID, UserID) VALUES ('{$_GET['id']}', '{$_SESSION['userID']}');");
                $database->executeQuery("DELETE FROM users_friends_requests WHERE SenderID='{$_GET['id']}' AND UserID='{$_SESSION['userID']}'");
                
                $database->executeQuery("INSERT INTO users_notifications (SenderID, UserID, Message) VALUES ('{$_SESSION['userID']}', '{$_GET['id']}', 'has accepted your friend request.')");

                header('Location: '.$_SERVER['HTTP_REFERER']);
            }
        }
    }
} else {
    header('HTTP/1.0 403 Forbidden');
}
?>