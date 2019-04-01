<?php
require "../../global.inc.php";
$global = new GlobalHandler(false);

$template = $global->template;
$database = $global->db;
$functions = $global->functions;

if(isset($_SESSION['userID'])) {
    echo json_encode(
        array(
            "notifCount" => $database->getNumberOfRows("SELECT * FROM users_notifications WHERE UserID='{$_SESSION['userID']}' AND ReadNotif=0"),
            "msgCount" => "WIP",
            "friendCount" => $database->getNumberOfRows("SELECT * FROM users_friends_requests WHERE UserID='{$_SESSION['userID']}'")
            )
        , JSON_UNESCAPED_SLASHES);
} else {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
}
?>