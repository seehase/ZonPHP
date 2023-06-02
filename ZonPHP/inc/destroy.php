<?php
include_once "../parameters.php";
include_once "../inc/sessionstart.php";

$_SESSION['passok'] = "nothing";
unset($_SESSION['passok']);
unset($_SESSION['lastupdate']);

session_unset();
session_destroy();

header('location:../index.php')

?>