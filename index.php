<?php
require "core/global.inc.php";
$global = new GlobalHandler();
$template = $global->template;
$database = $global->db;
$functions = $global->functions;

// Template Variable Initialization
$template->vars['{pageName}'] = "Homepage";
$template->vars['{content}'] = $template->loadTemplate("home");

switch(($_GET['mode'])) {
    case "editPost":
        if(isset($_GET['postID'])) {
            $functions->editPostModal($_GET['postID']);
        }
        break;
    case "deletePost":
        if(isset($_GET['postID'])) {
            $functions->deletePostModal($_GET['postID']);
        }
        break;
}


if(isset($_POST['updatePostText'])) {
    if($_POST['updatePostText'] != "") {
        $checkUsersLastPost = $database->fetchObject("SELECT * FROM users_posts WHERE UserID='{$_SESSION['userID']}' ORDER BY PostID DESC");
        $databaseTime = strtotime($checkUsersLastPost->Date);
        $serverTime = time();
        $finalTime = $serverTime - $databaseTime;
        if($database->escapeString(strtolower($checkUsersLastPost->Content)) == strtolower($_POST['updatePostText']) && ($serverTime - $databaseTime < (60*60*24))) {
            $tempTime = (60 * 60 *24) - ($serverTime - $databaseTime);
            $hours = floor($tempTime / 3600);
            $mins = floor($tempTime / 60 % 60);
            $secs = floor($tempTime % 60);
            $functions->popupModal("Woah, slow down there, partner.", "You can't post the same message so soon. You must wait ". $hours . " hour(s), ". $mins ." minute(s) and " . $secs . " second(s).");
        } else if($serverTime - $databaseTime < 5) {
            $tempTime = (5) - ($serverTime - $databaseTime);
            $hours = floor($tempTime / 3600);
            $mins = floor($tempTime / 60 % 60);
            $secs = floor($tempTime % 60);
            $functions->popupModal("Woah, slow down there, partner.", "You must wait ". $hours . " hour(s), ". $mins ." minute(s) and " . $secs . " second(s), before you can post again.");
        } else {
            $database->executeQuery("INSERT INTO users_posts (UserID, Content) VALUES ('{$_SESSION['userID']}', '{$_POST['updatePostText']}')");
        }
    } else {
        $functions->popupModal("An error occured", "You have to actually insert something into the textarea in order to post.");
    }
}

$template->content();
?>