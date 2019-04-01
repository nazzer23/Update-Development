<?php
class Functions {
    public $global;
    public $database;

    public function __construct($global) {
        $this->global = $global;
        $this->database = $global->db;
    }

    public function encryptPassword($x, $y) {
        $user = strtolower($x);
        $pass = $y;

        $data = $pass . $user;
        $data = hash("sha512", $data);
        $data = md5($data);
        $data = hash("sha512", base64_encode($data));
        $data = strrev($data);
        $data = strtoupper($data);
        $data = substr($data, strlen($user), 26);
        $data = strrev($data);

        return $data;
    }

    public function checkSession() {
        $userID = $_SESSION['userID'];
        $sessionString = $_SESSION['sessionString'];
        if($this->database->getNumberOfRows("SELECT * FROM users_sessions WHERE SessionString='{$sessionString}' AND UserID='{$userID}' AND Valid=1") < 0) {
            session_destroy();
            header('Location: /');
        }
    }

    public function generateAlert($msg, $type) {
        $alert = "";
        switch($type) {
            case 0:
                $alert = "danger";
                break;
            case 1:
                $alert = "success";
                break;
            default:
                $alert = "dark";
                break;
        }
        return '<div class="alert alert-'.$alert.'" role="alert">'.$msg.'</div>';
    }

    public function getDateFormat($date) {
        $databaseTime = strtotime($date);
        return date("l jS F Y g:ia", $databaseTime);
    }

    public function buildPost($postData) { 
        global $database; 
        $post = "";

        // Desktop View   
        $post .='<div class="d-none d-md-block">
        <div class="card mb-4">
        <div class="card-header border-0"><h5 class="mb-0">';
        // Post Settings
        if($_SESSION['userID'] == $postData->UserID || $_SESSION['userID'] == $postData->ProfileID) {
            $dropdown = '
            <div class="dropdown">
                <button class="btn btn-secondary float-right dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-cogs"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item" id="editPost" href="?mode=editPost&postID='.$postData->PostID.'">Edit Post</a>
                    <a class="dropdown-item deletePost" href="?mode=deletePost&postID='.$postData->PostID.'">Delete Post</a>
                </div>
            </div>
            ';
            $post .=$dropdown;
        }
        $post .= '<img class="card-img-top flex-left rounded img-fluid" style="min-width:100px; max-width: 150px; min-height:100px; max-height:150px; width:50px; height:50px;" src="'.$this->getProfilePicture($postData->UserID).'" data-holder-rendered="true">
        
        
        <a class="text-dark" href="/profile.php?id='.$postData->UserID.'">' . $postData->FirstName . ' ' . $postData->LastName . '</a>';

        if($postData->ProfileID > 0) {
            $getUserData = $database->executeQuery("SELECT * FROM users WHERE UserID='{$postData->ProfileID}' LIMIT 1")->fetch_object();
            $post .= ' <i class="fas fa-angle-double-right"></i> <a class="text-dark" href="/profile.php?id='.$getUserData->UserID.'">'.$getUserData->FirstName.' ' . $getUserData->LastName . '</a>';
        }
        
        $post .='</h5>
        </div>
        <div class="card-body">';

        $post .= '<div class="mb-1 text-muted"><p class="card-text mb-auto">'.nl2br($postData->Content).'</p></div>';
        $post .= '</div>
        <div class="card-footer">
            <small>
            '.$this->database->getNumberOfRows("SELECT * FROM users_posts_likes WHERE PostID='{$postData->PostID}' AND CommentID=0").' likes
            </small>
            <small>
            '.$this->database->getNumberOfRows("SELECT * FROM users_posts_comments WHERE PostID='{$postData->PostID}'").' comments
            </small>
            <small class="float-right">Posted on '.$this->getDateFormat($postData->Date).'</small>
        </div>';

        $commentQuery = $this->database->executeQuery("SELECT * FROM users_posts_comments WHERE PostID='{$postData->PostID}'");
        while($rows = $commentQuery->fetch_object()) {
            $getUserData = $this->database->fetchObject("SELECT * FROM users WHERE UserID='{$rows->UserID}'");
            $post .= '
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <img class="card-img-top flex-left rounded img-fluid mr-1" style="min-width:50px; max-width: 50px; min-height:50px; max-height:50px; width:50px; height:50px;" src="'.$this->getProfilePicture($getUserData->UserID).'" data-holder-rendered="true">
                    <a class="flex-left mr-2 text-danger" href="/profile.php?id='.$rows->UserID.'">' . $getUserData->FirstName . ' ' . $getUserData->LastName . '</a>
                    '.$rows->Text.'
                    <small class="ml-auto">'.$this->getDateFormat($rows->Date).'</small>
                </div>
            </div>';
        }

        $post .='<div class="card-footer"><form method="post" action="core/handlers/posts/commentPost.php"><div class="form-row align-items-center">';
        
        $likeStatus = "";
        if($this->database->getNumberOfRows("SELECT * FROM users_posts_likes WHERE PostID='{$postData->PostID}' AND UserID='{$_SESSION['userID']}'") > 0){
            $likeStatus = "<button id='likePost' type='button' class='btn btn-danger flex-left' postID='".$postData->PostID."'>Unlike</button>";
        } else {
            $likeStatus = "<button id='likePost' type='button' class='btn btn-danger flex-left' postID='".$postData->PostID."'>Like</button>";
        }
        $post .= $likeStatus;

        $comment = '
            <div class="col my-1">
                <input type="hidden" name="postID" value="'.$postData->PostID.'">
                <textarea class="form-control" name="commentText" placeholder="Enter your comment here." id="exampleFormControlTextarea1" rows="1"></textarea>
            </div>
            <div class="col-auto my-1">
                <button type="submit" class="btn btn-danger">Submit</button>
            </div>
        </div>
        ';
        $post .= $comment;

        $post .= '</div></form>
        </div></div></div></div>';

        // Mobile View   
        $post .='<div class="d-md-none">
        <div class="card flex-md-row mb-4 box-shadow">
        <div class="card-header border-0"><h5 class="mb-0">';

        // Post Settings
        if($_SESSION['userID'] == $postData->UserID || $_SESSION['userID'] == $postData->ProfileID) {
            $dropdown = '
            <div class="dropdown">
                <button class="btn btn-secondary float-right dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-cogs"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item" id="editPost" href="?mode=editPost&postID='.$postData->PostID.'">Edit Post</a>
                    <a class="dropdown-item deletePost" href="?mode=deletePost&postID='.$postData->PostID.'">Delete Post</a>
                </div>
            </div>
            ';
            $post .=$dropdown;
        }
        $post .='<img class="card-img-left flex-left rounded img-fluid" style="max-width: 50px; max-height:50px; width:50px; height:50px;" src="'.$this->getProfilePicture($postData->UserID).'" data-holder-rendered="true">
        <a class="text-dark" href="/profile.php?id='.$postData->UserID.'">'.$postData->FirstName.' ' . $postData->LastName . '</a>';
        if($postData->ProfileID > 0) {
            $getUserData = $database->executeQuery("SELECT * FROM users WHERE UserID='{$postData->ProfileID}' LIMIT 1")->fetch_object();
            $post .= ' <i class="fas fa-angle-double-right"></i> <a class="text-dark" href="/profile.php?id='.$getUserData->UserID.'">'.$getUserData->FirstName.' ' . $getUserData->LastName . '</a>';
        }
        $post .=' </h5><small>Posted on '.$this->getDateFormat($postData->Date).'</small>';
        $post .= '</div>
        <div class="card-body">'.nl2br($postData->Content).'</div>
        <div class="card-footer">
            <small>
            '.$this->database->getNumberOfRows("SELECT * FROM users_posts_likes WHERE PostID='{$postData->PostID}' AND CommentID=0").' likes
            </small>
            <small>
            '.$this->database->getNumberOfRows("SELECT * FROM users_posts_comments WHERE PostID='{$postData->PostID}'").' comments
            </small>
        </div>

        ';
        
        $commentQuery = $this->database->executeQuery("SELECT * FROM users_posts_comments WHERE PostID='{$postData->PostID}'");
        while($rows = $commentQuery->fetch_object()) {
            $getUserData = $this->database->fetchObject("SELECT * FROM users WHERE UserID='{$rows->UserID}'");
            $post .= '
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <img class="card-img-top flex-left rounded img-fluid mr-1" style="min-width:50px; max-width: 50px; min-height:50px; max-height:50px; width:50px; height:50px;" src="'.$this->getProfilePicture($getUserData->UserID).'" data-holder-rendered="true">
                    <a class="flex-left mr-2 text-danger" href="/profile.php?id='.$rows->UserID.'">' . $getUserData->FirstName . ' ' . $getUserData->LastName . '</a>
                    '.$rows->Text.'
                </div>
                <div class="card-footer">
                <small class="ml-auto">'.$this->getDateFormat($rows->Date).'</small>
                </div>
            </div>';
        }
        

        $post .='<div class="card-footer"><form method="post" action="core/handlers/posts/commentPost.php"><div class="form-row align-items-center">';
        
        $likeStatus = "";
        if($this->database->getNumberOfRows("SELECT * FROM users_posts_likes WHERE PostID='{$postData->PostID}' AND UserID='{$_SESSION['userID']}'") > 0){
            $likeStatus = "<button id='likePost' type='button' class='btn btn-danger flex-left' postID='".$postData->PostID."'>Unlike</button>";
        } else {
            $likeStatus = "<button id='likePost' type='button' class='btn btn-danger flex-left' postID='".$postData->PostID."'>Like</button>";
        }
        $post .= $likeStatus;

        $comment = '
            <div class="col my-1">
                <input type="hidden" name="postID" value="'.$postData->PostID.'">
                <textarea class="form-control" name="commentText" placeholder="Enter your comment here." id="exampleFormControlTextarea1" rows="1"></textarea>
            </div>
            <div class="col-auto my-1">
                <button type="submit" class="btn btn-danger">Submit</button>
            </div>
        </div>
        ';
        $post .= $comment;

        $post .= '</div></form>
        </div></div></div></div>';
        return $post;
    }

    public function checkIfFriendOrSelf($userID) {
        global $database;
        $isFriend = $database->getNumberOfRows("SELECT * FROM users_friends WHERE users_friends.UserID='{$_SESSION['userID']}' AND users_friends.FriendID='{$userID}'") > 0 ? true : false;
        if(!$isFriend) {
            if($userID == $_SESSION['userID']) {
                return true;
            }
            return false;
        }
        return true;
    }

    public function getProfilePicture($userID) {
        $noPicture = "http://cdn.updatesocial.co.uk/no-avatar.png";
        $userData = $this->database->executeQuery("SELECT * FROM users WHERE UserID='{$userID}' LIMIT 1")->fetch_object();
        if($userData->ProfilePictureID == 0) {
            return $noPicture;
        } else {
            $pictureQuery = $this->database->executeQuery("SELECT Picture FROM users_pictures WHERE PhotoID='{$userData->ProfilePictureID}' AND UserID='{$userID}' LIMIT 1");
            if($pictureQuery->num_rows <= 0) {
                return $noPicture;
            } else {
                $pictureData = $pictureQuery->fetch_object();
                return $pictureData->Picture;
            }
        }
    }

    public function getProfileCover($userID) {
        $noPicture = "http://cdn.updatesocial.co.uk/no-avatar.png";
        $userData = $this->database->executeQuery("SELECT * FROM users WHERE UserID='{$userID}' LIMIT 1")->fetch_object();
        if($userData->CoverPhotoID == 0) {
            return "none";
        } else {
            $pictureQuery = $this->database->executeQuery("SELECT Picture FROM users_pictures WHERE PhotoID='{$userData->CoverPhotoID}' AND UserID='{$userID}' LIMIT 1");
            if($pictureQuery->num_rows <= 0) {
                return "none";
            } else {
                $pictureData = $pictureQuery->fetch_object();
                return $pictureData->Picture;
            }
        }
    }

    public function uploadImage($file) {
        if(isset($_FILES['image'])){
            $error="";
            $file_name = $_FILES['image']['name'];
            $file_size =$_FILES['image']['size'];
            $file_tmp =$_FILES['image']['tmp_name'];
            $file_type=$_FILES['image']['type'];
            $file_parts =explode('.',$file_name);
            $file_ext=strtolower(end($file_parts));
            
            $extensions= array("jpeg","jpg","png", "gif", "bmp");
            
            if(in_array($file_ext,$extensions)=== false){
                return "error";
            }
            
            // Check if file was uploaded ok
            if( ! is_uploaded_file($_FILES['image']['tmp_name']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK)
            {
                return "error";
            }
        
            
            if($error == ""){
                /* FTP Account (Remote Server) */
                $ftp_host = 'babilon.ga'; /* host */
                $ftp_user_name = 'updateftp'; /* username */
                $ftp_user_pass = 'FZzB$xYn5x5}k(daLw;]jv\?Cx{SLEUc}mj2X4zJ5$WKh'; /* password */
                

                $remote_file = md5($file_tmp.$file_name.rand(2,49999912)) .".".$file_ext;
                $movedFile = "../../../core/temp/".$remote_file;

                if(move_uploaded_file($file_tmp, $movedFile)){
                        /* Connect using basic FTP */
                    $connect_it = ftp_connect( $ftp_host );
                    
                    /* Login to FTP */
                    $login_result = ftp_login( $connect_it, $ftp_user_name, $ftp_user_pass );
                    ftp_pasv($connect_it, true) or die("Cannot switch to passive mode"); 
                    
                    /* Send $local_file to FTP */
                    if ( ftp_put( $connect_it, $remote_file, $movedFile, FTP_BINARY ) ) {
                        
                    }
                    else {
                        return "error";
                    }
                    
                    /* Close the connection */
                    ftp_close( $connect_it );
                }

                if(strlen($error) > 0) {
                    return "error";
                } else {
                    return "http://cdn.updatesocial.co.uk/".$remote_file;
                }
            }
        }
    }

    public function popupModal($header, $content) {
        global $template;
        $template->vars['{modals}'] .= '
        <div class="modal fade" id="popupModal" tabindex="-1" role="dialog" aria-labelledby="popupModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="popupModalLabel">'.$header.'</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>'.$content.'</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <script>$("#popupModal").modal("show")</script>
            ';
    }

    public function editPostModal($postID) {
        global $template;  
        $postQuery = $this->database->executeQuery("SELECT * FROM users_posts WHERE UserID='{$_SESSION['userID']}' AND PostID='{$postID}'");
        if($postQuery->num_rows > 0) {
            $postData = $postQuery->fetch_object();
            $template->vars['{modals}'] .= '
            <!-- Edit Post Modal -->
            <div class="modal fade" id="editPostModal" tabindex="-1" role="dialog" aria-labelledby="popupModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <form method="post" action="/core/handlers/posts/editPost.php">
                            <div class="modal-header">
                                <h5 class="modal-title" id="popupModalLabel">Editing Post</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <textarea class="form-control" rows="4" name="content">'.$postData->Content.'</textarea>
                            </div>
                            <div class="modal-footer">
                                <input type="hidden" name="postID" value="'.$postID.'">
                                <button type="submit" class="btn btn-danger">Edit Post</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <script>$("#editPostModal").modal("show")</script>';
        }
    }

    public function deletePostModal($postID) {
        global $template;  
        $postQuery = $this->database->executeQuery("SELECT * FROM users_posts WHERE UserID='{$_SESSION['userID']}' AND PostID='{$postID}'");
        if($postQuery->num_rows > 0) {
            $postData = $postQuery->fetch_object();
            $template->vars['{modals}'] .= '
            <!-- Delete Post Modal -->
            <div class="modal fade" id="editPostModal" tabindex="-1" role="dialog" aria-labelledby="popupModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <form method="post" action="/core/handlers/posts/deletePost.php">
                            <div class="modal-header">
                                <h5 class="modal-title" id="popupModalLabel">Delete Post</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete the following post:</p><br>
                                <p>'.nl2br($postData->Content).'</p>
                            </div>
                            <div class="modal-footer">
                                <input type="hidden" name="postID" value="'.$postID.'">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <script>$("#editPostModal").modal("show")</script>';
        }
    }

    public function viewPostModal($userID, $postID) {
        global $template;
        $postQuery = $this->database->executeQuery("SELECT * FROM users_posts WHERE (UserID='{$userID}' or ProfileID='{$userID}') AND PostID='{$postID}'");
        if($postQuery->num_rows > 0) {
            $postData = $postQuery->fetch_object();

            if($this->database->getNumberOfRows("SELECT * FROM users_notifications WHERE UserID='{$_SESSION['userID']}' AND PostID='{$postID}' AND ReadNotif=0") > 0) {
                $this->database->executeQuery("UPDATE users_notifications SET ReadNotif=1 WHERE UserID='{$_SESSION['userID']}' AND PostID='{$postID}'");
            }

            if($postData->ProfileID == 0) {
                $userPosterData = $this->database->fetchObject("SELECT * FROM users WHERE UserID='{$userID}'");
            } else {
                $userPosterData = $this->database->fetchObject("SELECT * FROM users WHERE UserID='{$postData->UserID}'");
            }

            $template->vars['{modals}'] .= '
            <!-- View Post Modal -->
            <div class="modal fade" id="viewPostModal" tabindex="-1" role="dialog" aria-labelledby="popupModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="popupModalLabel"><img class="rounded-circle img-fluid mr-1" style="max-width: 50px; max-height:50px; width:50px; height:50px;" src="'.$this->getProfilePicture($userPosterData->UserID).' "/>'.$userPosterData->FirstName.' '.$userPosterData->LastName.'</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>'.nl2br($postData->Content).'</p>
                        </div>
                        <div class="modal-footer">
                            <small>
                                '.$this->database->getNumberOfRows("SELECT * FROM users_posts_likes WHERE PostID='{$postData->PostID}' AND CommentID=0").' likes
                            </small>
                            <small class="mr-auto">
                                '.$this->database->getNumberOfRows("SELECT * FROM users_posts_comments WHERE PostID='{$postData->PostID}'").' comments
                            </small>
                            <small>Posted on '.$this->getDateFormat($postData->Date).'</small>
                        </div>
                        <div class="modal-body">
                            ';
                        $commentQuery = $this->database->executeQuery("SELECT * FROM users_posts_comments WHERE PostID='{$postData->PostID}'");
                        while($rows = $commentQuery->fetch_object()) {
                            $getUserData = $this->database->fetchObject("SELECT * FROM users WHERE UserID='{$rows->UserID}'");
                            $template->vars['{modals}'] .= '
                                <div class="card">
                                    <div class="card-body d-flex align-items-center">
                                        <img class="card-img-top flex-left rounded img-fluid mr-1" style="min-width:50px; max-width: 50px; min-height:50px; max-height:50px; width:50px; height:50px;" src="'.$this->getProfilePicture($getUserData->UserID).'" data-holder-rendered="true">
                                        <a class="flex-left mr-2 text-danger" href="/profile.php?id='.$rows->UserID.'">' . $getUserData->FirstName . ' ' . $getUserData->LastName . '</a>
                                        '.$rows->Text.'
                                        <small class="  ml-auto">'.$this->getDateFormat($postData->Date).'</small>
                                    </div>
                                </div>';
                        }
                        $template->vars['{modals}'] .='
                        </div>
                    </div>
                </div>
            </div>
            <script>$("#viewPostModal").modal("show")</script>';
        }
    }
}
?>