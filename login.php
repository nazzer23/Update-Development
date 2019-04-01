<?php
require "core/global.inc.php";
$global = new GlobalHandler();
$template = $global->template;
$database = $global->db;
$functions = $global->functions;

// Global Template Variable Initialization
$template->vars['{pageName}'] = "Login";
$template->vars['{content}'] = $template->loadTemplate("login");

// Local Template Variables
$template->vars['{scripts}'] = '<script src="/core/templates/atheme/js/login.js"></script>';

$template->content();
?>