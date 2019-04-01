<?php
session_start();
if(isset($_POST['session']) && isset($_POST['userID'])) {
    $_SESSION['sessionString'] = $_POST['session'];
    $_SESSION['userID'] = $_POST['userID'];
    $_SESSION['loggedIn'] = true;
    echo json_encode(array(
        "status" => true
    ), JSON_UNESCAPED_SLASHES);
} else {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
}
