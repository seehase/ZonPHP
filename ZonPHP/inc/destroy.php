<?php
global $zonPHPSessionID;
include_once "version_info.php";

session_name($zonPHPSessionID);
session_start();
unset($_SESSION['params']);
unset($_SESSION['txt']);
unset($_SESSION['colors']);
session_destroy();
header('location:../index.php');
