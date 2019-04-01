<?php
require "../../global.inc.php";
$global = new GlobalHandler(false);

$template = $global->template;
$database = $global->db;
$functions = $global->functions;

if(isset($_POST['postID'])) {
    if($_POST['postID'] == "") {
        header('HTTP/1.0 403 Forbidden');
    } else {
        $isUserValid = $database->getNumberOfRows("SELECT * FROM users_posts WHERE PostID='{$_POST['postID']}' AND (UserID='{$_SESSION['userID']}')");
        if($isUserValid <= 0) {
            header('Location: '.strtok($_SERVER['HTTP_REFERER'], '?'));
        } else {
            $database->executeQuery("UPDATE users_posts SET Content='{$_POST['content']}' WHERE PostID='{$_POST['postID']}' AND (UserID='{$_SESSION['userID']}')");
            header('Location: '.strtok($_SERVER['HTTP_REFERER'], '?'));
        }
    }
} else {
    header('HTTP/1.0 403 Forbidden');
}
?>