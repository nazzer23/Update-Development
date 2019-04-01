<?php
require "core/global.inc.php";
$global = new GlobalHandler();
$template = $global->template;
$database = $global->db;
$functions = $global->functions;

// Global Template Variable Initialization
$template->vars['{pageName}'] = "Register";
$template->vars['{content}'] = $template->loadTemplate("register");

$template->vars['{scripts}'] = '<script src="/core/templates/atheme/js/register.js"></script>';

$template->content();
?>