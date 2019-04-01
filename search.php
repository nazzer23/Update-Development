<?php
require "core/global.inc.php";
$global = new GlobalHandler();
$template = $global->template;
$database = $global->db;
$functions = $global->functions;

// Template Variable Initialization
$template->vars['{pageName}'] = "Search - Results for " . $_GET['searchData'];
$template->vars['{content}'] = $template->loadTemplate("searchResults");

// Local Varaible Initialization
$template->vars['{searchResults}'] = null;

$searchData = explode(" ",$_GET['searchData']);
$searchCriteria = "";

for($i = 0; $i < sizeof($searchData); $i++) {
    if($searchData[$i] != ""){
        $searchCriteria .= " FirstName LIKE '%{$searchData[$i]}%' OR LastName LIKE '%{$searchData[$i]}%' ";
        if($i != sizeof($searchData)-1) {
            $searchCriteria .= " or ";
        }
    }
}

$post .='
<div class="card flex-md-row mb-4 box-shadow h-md-250">';
$img = '<img class="card-img-left flex-auto d-none d-md-block" data-src="holder.js/150x150?theme=thumb" alt="Thumbnail [200x250]" style="width: 150px; height: 150px;" src="http://cdn.updatesocial.co.uk/no-avatar.png" data-holder-rendered="true">';
$post .= $img;
$post .= '<div class="card-body d-flex flex-column align-items-start">
<h3 class="mb-0">
    Search results for "'.$_GET['searchData'].'"
</h3></div>';
$post .= '</div>';
$template->vars['{searchResults}'] = $post;

$query = "SELECT * FROM users WHERE ". $searchCriteria;
$sqlQuery = $database->executeQuery($query);
if($sqlQuery->num_rows <= 0 || !isset($_GET['searchData'])) {
    $post = "";
    $post .='
    <div class="card flex-md-row mb-4 box-shadow h-md-250">';
    $img = '<img class="card-img-left flex-auto d-none d-md-block" data-src="holder.js/150x150?theme=thumb" alt="Thumbnail [200x250]" style="width: 150px; height: 150px;" src="http://cdn.updatesocial.co.uk/no-avatar.png" data-holder-rendered="true">';
    $post .= $img;
    $post .= '<div class="card-body d-flex flex-column align-items-start">
    <h3 class="mb-0">
        No results were found.
    </h3></div>';
    $post .= '</div>';
    $template->vars['{searchResults}'] = $post;
} else {
    while($results = $sqlQuery->fetch_object()) {
        $template->vars['{searchResults}'] .= buildSearchRequest($results);
    }
}

$template->content();

function buildSearchRequest($postData) {
    global $global, $template, $database, $functions;
    $friendReqDesktop = "";
    $friendReqMobile = "";
    $currentUserData = $database->fetchObject("SELECT * FROM users WHERE UserID='{$_SESSION['userID']}'");
    $isFriend = $functions->checkIfFriendOrSelf($postData->UserID);
    if(!$isFriend) {
        if($database->getNumberOfRows("SELECT * FROM users_friends_requests WHERE UserID='{$postData->UserID}' AND SenderID='{$_SESSION['userID']}'")) {
            $friendReqDesktop = '<a href="/core/handlers/friends/cancelRequest.php?id='.$postData->UserID.'" class="btn btn-outline-danger float-right">Cancel Request</a>';
            $friendReqMobile = '<a href="/core/handlers/friends/cancelRequest.php?id='.$postData->UserID.'" class="btn btn-outline-danger">Cancel Request</a>';
        } else if($database->getNumberOfRows("SELECT * FROM users_friends_requests WHERE SenderID='{$postData->UserID}' AND UserID='{$_SESSION['userID']}'")) {
            $friendReqDesktop = '<a href="/core/handlers/friends/acceptFriend.php?id='.$postData->UserID.'" class="btn btn-outline-danger float-right">Accept Request</a>';
            $friendReqMobile = '<a href="/core/handlers/friends/acceptFriend.php?id='.$postData->UserID.'" class="btn btn-outline-danger">Accept Request</a>';
        } else if(!$postData->Verified || ($currentUserData->Verified)) {
            $friendReqDesktop = '<a href="/core/handlers/friends/requestFriend.php?id='.$postData->UserID.'" class="btn btn-outline-danger float-right">Add Friend</a>';
            $friendReqMobile = '<a href="/core/handlers/friends/requestFriend.php?id='.$postData->UserID.'" class="btn btn-outline-danger">Add Friend</a>';
        }
        
    } else {
        if($postData->UserID != $_SESSION['userID']) {
            $friendReqDesktop = '<a href="/core/handlers/friends/deleteFriend.php?id='.$postData->UserID.'" class="btn btn-outline-danger float-right">Delete Friend</a>';
            $friendReqMobile = '<a href="/core/handlers/friends/deleteFriend.php?id='.$postData->UserID.'" class="btn btn-outline-danger">Delete Friend</a>';    
        }
    }


    $post = "";

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

    if($postData->Bio != "") {
        $post .= '<small>' . nl2br($postData->Bio) . '</small>';
    }

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