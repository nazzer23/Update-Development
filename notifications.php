<?php
require "core/global.inc.php";
$global = new GlobalHandler();
$template = $global->template;
$database = $global->db;
$functions = $global->functions;

// Template Variable Initialization
$template->vars['{pageName}'] = "Notifications";

switch(key($_GET)) {
    case "markAsRead":
        $notificationQuery = $database->executeQuery("SELECT * FROM users_notifications WHERE NotifID='{$_GET['id']}' AND UserID='{$_SESSION['userID']}'");
        if($notificationQuery->num_rows > 0) {
            $database->executeQuery("UPDATE users_notifications SET ReadNotif=1 WHERE NotifID='{$_GET['id']}' AND UserID='{$_SESSION['userID']}'") or die ("There was an error.");
        }
        header('Location: '.$_SERVER['HTTP_REFERER']);
        break;
    case "friends":
        $template->vars['{content}'] = $template->loadTemplate("notifications.friends");
        $template->vars['{friendsResults}'] = "";

        $notificationsQuery = $database->executeQuery("SELECT * FROM users_friends_requests WHERE UserID='{$_SESSION['userID']}' ORDER BY SenderID DESC");

        if($notificationsQuery->num_rows <= 0) {
            $post = "";
            $post .='
            <div class="card flex-md-row mb-4 box-shadow h-md-250">';
            $img = '<img class="card-img-left flex-auto d-none d-md-block" data-src="holder.js/150x150?theme=thumb" alt="Thumbnail [200x250]" style="width: 150px; height: 150px;" src="http://cdn.updatesocial.co.uk/no-avatar.png" data-holder-rendered="true">';
            $post .= $img;
            $post .= '<div class="card-body d-flex flex-column align-items-start">
            <h3 class="mb-0">
                You have no friend requests!
            </h3></div>';
            $post .= '</div>';
            $template->vars['{friendsResults}'] = $post;
        } else {
            while($rows = $notificationsQuery->fetch_object()) {
                $template->vars['{friendsResults}'] .= buildFriendRequest($rows);
            }
        }
        break;
    default:
        $template->vars['{content}'] = $template->loadTemplate("notifications");
        $template->vars['{notifications}'] = "";

        $notificationsQuery = $database->executeQuery("SELECT * FROM users_notifications WHERE UserID='{$_SESSION['userID']}' AND ReadNotif=0 ORDER BY NotifID DESC");

        if($notificationsQuery->num_rows <= 0) {
            $post = "";
            $post .='
            <div class="card flex-md-row mb-4 box-shadow h-md-250">';
            $img = '<img class="card-img-left flex-auto d-none d-md-block" data-src="holder.js/150x150?theme=thumb" alt="Thumbnail [200x250]" style="width: 150px; height: 150px;" src="http://cdn.updatesocial.co.uk/no-avatar.png" data-holder-rendered="true">';
            $post .= $img;
            $post .= '<div class="card-body d-flex flex-column align-items-start">
            <h3 class="mb-0">
                You have no new notifications!
            </h3></div>';
            $post .= '</div>';
            $template->vars['{notifications}'] = $post;
        } else {
            while($rows = $notificationsQuery->fetch_object()) {
                $template->vars['{notifications}'] .= buildNotification($rows);
            }
        }
    break;
}

$template->content();

function buildNotification($notifData) {
    global $global, $template, $database, $functions;
    $friendReqDesktop = "";
    $friendReqMobile = "";
    $post = "";

    $postData = $database->fetchObject("SELECT * FROM users WHERE UserID='{$notifData->SenderID}'");

    $friendReqDesktop .= '<a href="/notifications.php?markAsRead&id='.$notifData->NotifID.'" class="btn btn-outline-danger float-right">Mark as Read</a>';
    $friendReqMobile .= '<a href="/notifications.php?markAsRead&id='.$notifData->NotifID.'" class="btn btn-outline-danger">Mark as Read</a>';

    if($notifData->PostID > 0) {
        $friendReqDesktop .= '<a href="/profile.php?id='.$_SESSION['userID'].'&postID='.$notifData->PostID.'&mode=viewPost" class="btn btn-outline-danger float-right">See Post</a>';
        $friendReqMobile .= '<a href="/profile.php?id='.$_SESSION['userID'].'&postID='.$notifData->PostID.'&mode=viewPost" class="btn btn-outline-danger">See Post</a>';    
    }


    // Desktop View   
    $post .='<div class="d-none d-md-block">
    <div class="card flex-md-row mb-4 box-shadow">
    <div class="card-header border-0">
    <img class="card-img-left flex-left rounded img-fluid" style="min-width:100px; max-width: 150px; min-height:100px; max-height:150px; width:50px; height:50px;" src="'.$functions->getProfilePicture($postData->UserID).'" data-holder-rendered="true">
    </div>
    <div class="card-body">
    <h3 class="mb-0">
    <a class="text-dark" href="/profile.php?id='.$postData->UserID.'">'.$postData->FirstName.' ' . $postData->LastName . '</a>';
    if($postData->Verified) {
        $post .= '<button type="button" class="btn btn-outline-danger ml-1">Verified</button>';
    }
    $post .='</h3>';

    $post .= '<div class="mb-1 text-muted">';
    $post .= $friendReqDesktop;
    $post .= '<small>'. $notifData->Message .'</small>';
    $post .='</div>';
    $post .= '</div></div></div>';

    // Mobile View   
    $post .='<div class="d-md-none">
    <div class="card flex-md-row mb-4 box-shadow">
    <div class="card-header border-0">
    <img class="card-img-left flex-left rounded img-fluid" style="max-width: 50px; max-height:50px; width:50px; height:50px;" src="'.$functions->getProfilePicture($postData->UserID).'" data-holder-rendered="true">';

    $isFriend = $functions->checkIfFriendOrSelf($postData->UserID);

    if($postData->Verified) {
        $post .= '<button type="button" class="btn btn-outline-danger ml-1">Verified</button>';
    }
    
    $post .= $friendReqMobile;
    $post .= '</div>
    <div class="card-body">
    <h3 class="mb-0">
    <a class="text-dark" href="/profile.php?id='.$postData->UserID.'">'.$postData->FirstName.' ' . $postData->LastName . '</a>';
    
    $post .='</h3>';
    $post .= '<small>'. $notifData->Message .'</small>';
    $post .= '</div></div></div>';
    return $post;
}

function buildFriendRequest($requestData) {
    global $global, $template, $database, $functions;
    $friendReqDesktop = "";
    $friendReqMobile = "";
    $post = "";

    $postData = $database->fetchObject("SELECT * FROM users WHERE UserID='{$requestData->SenderID}'");

    $friendReqDesktop = '<a href="/core/handlers/friends/acceptFriend.php?id='.$postData->UserID.'" class="btn btn-outline-danger float-right">Accept Request</a>';
    $friendReqMobile = '<a href="/core/handlers/friends/acceptFriend.php?id='.$postData->UserID.'" class="btn btn-outline-danger">Accept Request</a>';


    // Desktop View   
    $post .='<div class="d-none d-md-block">
    <div class="card flex-md-row mb-4 box-shadow">
    <div class="card-header border-0">
    <img class="card-img-left flex-left rounded img-fluid" style="min-width:100px; max-width: 150px; min-height:100px; max-height:150px; width:50px; height:50px;" src="'.$functions->getProfilePicture($postData->UserID).'" data-holder-rendered="true">
    </div>
    <div class="card-body">
    <h3 class="mb-0">
    <a class="text-dark" href="/profile.php?id='.$postData->UserID.'">'.$postData->FirstName.' ' . $postData->LastName . '</a>';
    if($postData->Verified) {
        $post .= '<button type="button" class="btn btn-outline-danger ml-1">Verified</button>';
    }
    $post .='</h3>';

    $isFriend = $functions->checkIfFriendOrSelf($postData->UserID);
    $post .= '<div class="mb-1 text-muted">';
    
    $post .= $friendReqDesktop;
    $post .= '<small>' . nl2br($postData->Bio) . '</small>';

    $post .='</div>';
    $post .= '</div></div></div>';

    // Mobile View   
    $post .='<div class="d-md-none">
    <div class="card flex-md-row mb-4 box-shadow">
    <div class="card-header border-0">
    <img class="card-img-left flex-left rounded img-fluid" style="max-width: 50px; max-height:50px; width:50px; height:50px;" src="'.$functions->getProfilePicture($postData->UserID).'" data-holder-rendered="true">';

    $isFriend = $functions->checkIfFriendOrSelf($postData->UserID);

    if($postData->Verified) {
        $post .= '<button type="button" class="btn btn-outline-danger ml-1">Verified</button>';
    }
    
    $post .= $friendReqMobile;
    $post .= '</div>
    <div class="card-body">
    <h3 class="mb-0">
    <a class="text-dark" href="/profile.php?id='.$postData->UserID.'">'.$postData->FirstName.' ' . $postData->LastName . '</a>';
    $post .='</h3>';

    $post .= '</div></div></div>';
    return $post;
}
?>