<?php
require "core/global.inc.php";
$global = new GlobalHandler();
$template = $global->template;
$database = $global->db;
$functions = $global->functions;

$profileID = 0;

// Template Variable Initialization
$template->vars['{content}'] = $template->loadTemplate("profile");

$template->vars['{friendStatusDesktop}'] = "";
$template->vars['{friendStatusMobile}'] = "";

$template->vars['{modals}'] = "";

if(!isset($_GET['id'])) {
    $profileID = $_SESSION['userID'];
} else {
    $profileID = $_GET['id'];
}

$userQuery = $database->executeQuery("SELECT * FROM users WHERE UserID='{$profileID}'");
if($userQuery->num_rows <= 0) {
    header('Location: /profile.php');
}
$userData = $userQuery->fetch_object();
$template->vars['{pageName}'] = $userData->FirstName . ' ' . $userData->LastName;
$template->vars['{profilePicture}'] = $functions->getProfilePicture($profileID);

//$coverData = "background: #A30000;";
$coverData = "background-image:url('https://placeimg.com/640/480/any');";
if($functions->getProfileCover($profileID) != "none") {
    $coverData = "background-image: url('{$functions->getProfileCover($profileID)}');";
}

$template->vars['{coverData}'] = $coverData;

$template->vars['{postCount}'] = $database->getNumberOfRows("SELECT * FROM users_posts where UserID='{$profileID}'");
$template->vars['{friendCount}'] = $database->getNumberOfRows("SELECT * FROM users_friends where UserID='{$profileID}'");
$template->vars['{photoCount}'] = $database->getNumberOfRows("SELECT * FROM users_pictures where UserID='{$profileID}'");

$bio = "";
if($userData->Bio == "") {
    $bio = $userData->FirstName . " hasn't yet got a bio.";
} else {
    $bio = $userData->Bio;
}

$template->vars['{profileBio}'] = nl2br($bio);

$template->vars['{profileID}'] = $userData->UserID;
$template->vars['{firstName}'] = $userData->FirstName;
$template->vars['{displayName}'] = $userData->FirstName . " " . $userData->LastName;
if($userData->Verified) {
    $template->vars['{displayName}'] .= '<button type="button" class="btn btn-outline-danger ml-1"><i class="fas fa-check"></i></button>';
}

// Submit Post
if(isset($_POST['updatePostText'])) {
    if($_POST['updatePostText'] != "") {
        $checkUsersLastPost = $database->fetchObject("SELECT * FROM users_posts WHERE UserID='{$_SESSION['userID']}' ORDER BY PostID DESC");
        $databaseTime = strtotime($checkUsersLastPost->Date);
        $serverTime = time();
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
            if($profileID == $_SESSION['userID']) {
                $database->executeQuery("INSERT INTO users_posts (UserID, Content) VALUES ('{$_SESSION['userID']}', '{$_POST['updatePostText']}')");
            } else {
                $database->executeQuery("INSERT INTO users_posts (UserID, ProfileID, Content) VALUES ('{$_SESSION['userID']}', '{$profileID}', '{$_POST['updatePostText']}')");
                $currentUserData = $database->fetchObject("SELECT * FROM users WHERE UserID='{$_SESSION['userID']}'");
                $userPostData = $database->fetchObject("SELECT * FROM users_posts WHERE UserID='{$_SESSION['userID']}' ORDER BY PostID DESC");
                $database->executeQuery("INSERT INTO users_notifications (UserID, SenderID, PostID, `Message`) VALUES ('{$profileID}', '{$_SESSION['userID']}','{$userPostData->PostID}', '{$currentUserData->FirstName} has posted on your profile.')");
            }
        }
    } else {
        $functions->popupModal("An error occured", "You have to actually insert something into the textarea in order to post.");
    }
}

$isFriend = $functions->checkIfFriendOrSelf($userData->UserID);
if(!$isFriend) {
    if($database->getNumberOfRows("SELECT * FROM users_friends_requests WHERE UserID='{$userData->UserID}' AND SenderID='{$_SESSION['userID']}'")) {
        $template->vars['{friendStatusDesktop}'] = '<a href="/core/handlers/friends/cancelRequest.php?id='.$userData->UserID.'" class="btn btn-outline-danger float-right">Cancel Request</a>';
        $template->vars['{friendStatusMobile}'] = '<a href="/core/handlers/friends/cancelRequest.php?id='.$userData->UserID.'" class="btn btn-outline-danger">Cancel Request</a>';
    } else if($database->getNumberOfRows("SELECT * FROM users_friends_requests WHERE SenderID='{$userData->UserID}' AND UserID='{$_SESSION['userID']}'")) {
        $template->vars['{friendStatusDesktop}'] = '<a href="/core/handlers/friends/acceptFriend.php?id='.$userData->UserID.'" class="btn btn-outline-danger float-right">Accept Request</a>';
        $template->vars['{friendStatusMobile}'] = '<a href="/core/handlers/friends/acceptFriend.php?id='.$userData->UserID.'" class="btn btn-outline-danger">Accept Request</a>';
    } else {
        $template->vars['{friendStatusDesktop}'] = '<a href="/core/handlers/friends/requestFriend.php?id='.$userData->UserID.'" class="btn btn-outline-danger float-right">Add Friend</a>';
        $template->vars['{friendStatusMobile}'] = '<a href="/core/handlers/friends/requestFriend.php?id='.$userData->UserID.'" class="btn btn-outline-danger">Add Friend</a>';
    }
    
} else {
    if($userData->UserID != $_SESSION['userID']) {
        $template->vars['{friendStatusDesktop}'] = '<a href="/core/handlers/friends/deleteFriend.php?id='.$userData->UserID.'" class="btn btn-outline-danger float-right">Delete Friend</a>';
        $template->vars['{friendStatusMobile}'] = '<a href="/core/handlers/friends/deleteFriend.php?id='.$userData->UserID.'" class="btn btn-outline-danger">Delete Friend</a>';    
    } else {
        $template->vars['{friendStatusDesktop}'] = '<button data-toggle="modal" data-target="#editProfile" type="button" class="btn btn-outline-danger float-right">Edit Profile</button>';
        $template->vars['{friendStatusMobile}'] = '<button data-toggle="modal" data-target="#editProfile" type="button" class="btn btn-outline-danger">Edit Profile</button>';    
    }
}

if($_SESSION['userID'] == $profileID) {
    $template->vars['{modals}'] .= '
    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfile" tabindex="-1" role="dialog" aria-labelledby="editProfileLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileLabel">Edit Profile</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <button data-toggle="modal" data-target="#editBioModal" data-dismiss="modal" type="button" class="btn btn-outline-danger">Edit Bio</button>
                    <button data-toggle="modal" data-target="#editProfilePic" data-dismiss="modal" type="button" class="btn btn-outline-danger">Change Profile Picture</button>
                    <button data-toggle="modal" data-target="#editCoverPic" data-dismiss="modal" type="button" class="btn btn-outline-danger">Change Cover Picture</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Picture Modal -->
    <div class="modal fade" id="editProfilePic" tabindex="-1" role="dialog" aria-labelledby="editProfilePicLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfilePicLabel">Edit Profile Picture</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="/core/handlers/profile/uploadProfileImage.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="input-group mb-3">
                            <div class="custom-file">
                                <input type="file" name="image">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="removePic" class="btn btn-secondary">Remove</button>
                        <button type="submit" class="btn btn-secondary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Cover Picture Modal -->
    <div class="modal fade" id="editCoverPic" tabindex="-1" role="dialog" aria-labelledby="editCoverPicLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCoverPicLabel">Edit Cover Picture</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="/core/handlers/profile/uploadCoverImage.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="input-group mb-3">
                            <div class="custom-file">
                                <input type="file" name="image">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="removePic" class="btn btn-secondary">Remove</button>
                        <button type="submit" class="btn btn-secondary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Bio Modal -->
    <div class="modal fade" id="editBioModal" tabindex="-1" role="dialog" aria-labelledby="editBioModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="/core/handlers/profile/editProfile.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editBioModalLabel">Edit your Bio</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="profileBio">Your Bio</label>
                            <textarea name="bioSubmit" class="form-control" id="profileBio" rows="3">'.$userData->Bio.'</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    ';
}

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
    case "viewPost":
        if(isset($_GET['postID'])) {
            $functions->viewPostModal($profileID, $_GET['postID']);
        }
        break;
}

$template->content();
?>