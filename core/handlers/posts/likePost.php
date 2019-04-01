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
        $liked = true;
        if($database->getNumberOfRows("SELECT * FROM users_posts WHERE PostID='{$_POST['postID']}'") <= 0) {
            header('HTTP/1.0 403 Forbidden');
        } else {
            $getPostData = $database->fetchObject("SELECT * FROM users_posts WHERE PostID='{$_POST['postID']}'");
            $postLikedByUser = $database->getNumberOfRows("SELECT * FROM users_posts_likes WHERE PostID='{$_POST['postID']}' AND UserID='{$_SESSION['userID']}'");
            if($postLikedByUser > 0) {
                $database->executeQuery("DELETE FROM users_posts_likes WHERE PostID='{$_POST['postID']}' AND UserID='{$_SESSION['userID']}'");
                if($database->getNumberOfRows("SELECT * FROM users_notifications WHERE UserID='{$getPostData->UserID}' AND PostID='{$getPostData->PostID}' AND Message LIKE '%liked%' AND SenderID='{$_SESSION['userID']}'") > 0) {
                    $database->executeQuery("DELETE FROM users_notifications WHERE UserID='{$getPostData->UserID}' AND PostID='{$getPostData->PostID}' AND Message LIKE '%liked%' AND SenderID='{$_SESSION['userID']}'");
                }
                $liked = false;
            } else {
                $database->executeQuery("INSERT INTO users_posts_likes (PostID, UserID) VALUES ('{$_POST['postID']}', '{$_SESSION['userID']}')");
                $database->executeQuery("INSERT INTO users_notifications (PostID, SenderID, UserID, Message) VALUES ('{$_POST['postID']}', '{$_SESSION['userID']}', '{$getPostData->UserID}', 'has liked your post.')");
                $liked = true;
            }
            echo json_encode(
                array(
                    "likedPost" => $liked
                )
                , JSON_UNESCAPED_SLASHES);
        }
    }
} else {
    header('HTTP/1.0 403 Forbidden');
}
?>