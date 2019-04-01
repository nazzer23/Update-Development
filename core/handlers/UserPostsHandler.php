<?php
require "../global.inc.php";
$global = new GlobalHandler(false);

$template = $global->template;
$database = $global->db;
$functions = $global->functions;
if(isset($_POST["limit"], $_POST["start"]))
{
    if(isset($_POST['profileID'])) {
        if($_POST['profileID'] == $_SESSION['userID']) {
            $queryRaw = "SELECT users_posts.*, users.FirstName, users.LastName FROM users_posts INNER JOIN users ON users_posts.UserID = users.UserID WHERE users_posts.UserID={$_SESSION['userID']} OR users_posts.ProfileID={$_SESSION['userID']} ORDER BY PostID DESC LIMIT ".$_POST["start"].", ".$_POST["limit"]."";
        } else {
            $queryRaw = "SELECT DISTINCT users_posts.*, users.FirstName, users.LastName FROM users_posts INNER JOIN users ON users_posts.UserID = users.UserID LEFT JOIN users_friends ON users_friends.UserID = users.UserID WHERE (users_posts.ProfileID={$_POST['profileID']}) OR (users_friends.FriendID={$_SESSION['userID']} AND users_friends.UserID={$_POST['profileID']}) ORDER BY PostID DESC LIMIT ".$_POST["start"].", ".$_POST["limit"]."";
        }
    } else {
        $queryRaw = "SELECT DISTINCT users_posts.*, users.FirstName, users.LastName FROM users_posts INNER JOIN users ON users_posts.UserID = users.UserID LEFT JOIN users_friends ON users_friends.UserID = users.UserID WHERE users_friends.FriendID={$_SESSION['userID']} OR users_posts.UserID={$_SESSION['userID']} ORDER BY PostID DESC LIMIT ".$_POST["start"].", ".$_POST["limit"]."";
    }
    $query = $database->executeQuery($queryRaw);
    while($rows = $query->fetch_object()) {
        echo $functions->buildPost($rows);
    }
}
?>