<?php
session_start();
require "config.php";
require "handlers/Template.php";
require "handlers/Database.php";
require "handlers/Functions.php";

class GlobalHandler {
    public $db;
    public $functions;
    public $template;
    public $userData;

    // Pages that can be browsed
    public $userLoggedOutPages;

    public function __construct($useTemplate=true) {
        $this->db = new Database();
        $this->functions = new Functions($this);
        if($useTemplate) {
            $this->template = new Template(Configuration::siteTheme);
            $this->template->vars['{siteName}'] = Configuration::siteName;
            $this->template->vars['{modals}'] = "";
            $this->template->vars['{scripts}'] = "";
        }

        // Escape $_POST Strings
        if(isset($_POST)) { $_POST = $this->db->escapeArray($_POST); }
        if(isset($_GET)) { $_GET = $this->db->escapeArray($_GET); }
        

        // Perform logged out checks
        $this->initializePageBlackList();
        $this->isUserLoggedIn();
    }

    public function initializePageBlackList() {
        $this->userLoggedOutPages = array("login", "register");
    }
    
    public function isUserLoggedIn() {
        $this->template->vars['{navbar}'] = "";

        if(!isset($_SESSION['loggedIn']) || !isset($_SESSION)) {
            $userData = null;
            if(!in_array(basename($_SERVER['PHP_SELF'], '.php'), $this->userLoggedOutPages)) {
                header('Location: /login.php');
            }
        } else {
            if(in_array(basename($_SERVER['PHP_SELF'], '.php'), $this->userLoggedOutPages)) {
                header('Location: /index.php');
            }

            $this->functions->checkSession();

            $userData = $this->db->executeQuery("SELECT * FROM users WHERE UserID='{$_SESSION['userID']}'")->fetch_object();

            // Set Navbar to logged in template
            $this->template->vars['{navbar}'] = '
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                <div class="container">
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample07" aria-controls="navbarsExample07" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
        
                    <div class="collapse navbar-collapse" id="navbarsExample07">
                        <ul class="navbar-nav mx-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="/">Home</a>
                            </li>
                        </ul>
                        <ul class="nav navbar-nav mx-auto">
                            <form class="form-inline mr-2 mr-md-0 mb-0" method="GET" action="/search.php">
                                <input class="form-control mr-sm-2" type="text" name="searchData" placeholder="Search..." aria-label="Search..." required>
                                <button class="btn btn-outline-danger my-2 my-sm-0" type="submit">Search</button>
                            </form>
                        </ul>
                        <ul class="nav navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="/notifications.php?friends">
                                    <i class="fas fa-user"></i> <span class="badge badge-light" id="requestCount"></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <i class="fas fa-envelope"></i> <span class="badge badge-light" id="messageCount"></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/notifications.php">
                                    <i class="fas fa-globe"></i> <span class="badge badge-light" id="notifCount"></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/profile.php?id='.$userData->UserID.'"><img class="rounded-circle img-fluid mr-1" style="max-width: 25px; max-height:25px; width:25px; height:25px;" src="'.$this->functions->getProfilePicture($_SESSION['userID']).' "/>'.$userData->FirstName.'</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="" id="dropdown03" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-user-cog"></i></a>
                                <div class="dropdown-menu" aria-labelledby="dropdown03">
                                    <a class="dropdown-item" href="#">Account Settings</a>
                                    <a class="dropdown-item" href="/logout.php">Logout</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
          </nav>';
        }
    }

}
?>