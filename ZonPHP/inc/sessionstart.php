<?php

// var_dump( "sessionstart", getcwd());

if (!isset($zonPHPSessionID)) $zonPHPSessionID = "SESZONPHP";

session_name($zonPHPSessionID);
session_start();

include "load_language.php";

?>