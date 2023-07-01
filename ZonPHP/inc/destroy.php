<?php



include_once "../inc/init.php";
include_once "../inc/sessionstart.php";

unset($_SESSION['lastupdate']);
unset($_SESSION['txt']);

session_unset();
session_destroy();

unset($_SESSION['lastupdate']);
unset($_SESSION['txt']);

header('location:../index.php')

?>