<?php
require "../../global.inc.php";
$global = new GlobalHandler(false);

$template = $global->template;
$database = $global->db;
$functions = $global->functions;

if(isset($_FILES['image']) && (!isset($_POST['removePic']))){
    $imageURL = $functions->uploadImage($_FILES['image']);
    if($imageURL == "error") {
        header('HTTP/1.0 500 Internal Server Error');
    } else {
        $database->executeQuery("INSERT INTO users_pictures (UserID, Picture) VALUES ('{$_SESSION['userID']}', '{$imageURL}')");
        $lastPhotoID = $database->fetchObject("SELECT * FROM users_pictures WHERE UserID='{$_SESSION['userID']}' ORDER BY PhotoID DESC");
        $database->executeQuery("UPDATE users SET CoverPhotoID='{$lastPhotoID->PhotoID}' WHERE UserID='{$_SESSION['userID']}'");
        header('Location: '.strtok($_SERVER['HTTP_REFERER'], '?'));
    }
} else if(isset($_POST['removePic'])) {
    $database->executeQuery("UPDATE users SET CoverPhotoID=0 WHERE UserID='{$_SESSION['userID']}'");
    header('Location: '.strtok($_SERVER['HTTP_REFERER'], '?'));
} else {
    header('HTTP/1.0 500 Internal Server Error');
}
?>