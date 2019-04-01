<?php
require "../../global.inc.php";
$global = new GlobalHandler(false);

$template = $global->template;
$database = $global->db;
$functions = $global->functions;
$success = true;
if(isset($_POST['postID'])) {
    if($_POST['postID'] == "") {
        header('HTTP/1.0 403 Forbidden');
    } else {
        if($database->getNumberOfRows("SELECT * FROM users_posts WHERE PostID='{$_POST['postID']}'") <= 0) {
            header('HTTP/1.0 403 Forbidden');
        } else {
            if($_POST['commentText'] == "") {
                header('Location: '.strtok($_SERVER['HTTP_REFERER'], '?'));
            } else {
                $getPostData = $database->fetchObject("SELECT * FROM users_posts WHERE PostID='{$_POST['postID']}'");            
                $database->executeQuery("INSERT INTO users_posts_comments (PostID, UserID, Text) VALUES ('{$_POST['postID']}', '{$_SESSION['userID']}', '{$_POST['commentText']}')");
                if($getPostData->UserID != $_SESSION['userID']) {
                    $database->executeQuery("INSERT INTO users_notifications (PostID, SenderID, UserID, Message) VALUES ('{$_POST['postID']}', '{$_SESSION['userID']}', '{$getPostData->UserID}', 'has commented on your post.')");
                }
                header('Location: '.strtok($_SERVER['HTTP_REFERER'], '?'));
            }
        }
    }
} else {
    header('HTTP/1.0 403 Forbidden');
}
?>